package cis

import (
	"bytes"
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"image"
	"image/color"
	_ "image/jpeg"
	"image/png"
	"io"
	"log"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"sync"
	"sync/atomic"
	"time"

	"golang.org/x/image/draw"

	"github.com/yugavto/apps/internal/orientation"
	"github.com/yugavto/apps/pkg/autocrm"
)

const (
	imgFullWidth     = 634
	imgFullHeight    = 500
	imgPreviewWidth  = 307
	imgPreviewHeight = 236
	imgWebPQuality   = 80
	imagesBatchSize  = 5000
	imagesWorkers    = 10
)

func (s *Service) ProcessImages() error {
	prodTable := s.apiTable()

	var extIDs []int
	err := s.db.Select(&extIDs, fmt.Sprintf(`
		SELECT v.ext_id FROM %s v
		LEFT JOIN yapps_app_cis_images i ON i.ext_id = v.ext_id
			AND i.preview LIKE ? AND i.preview LIKE '%%.webp%%'
		WHERE (v.update_images = 1 OR v.use_internal_images = 0
			OR NOT EXISTS (SELECT 1 FROM yapps_app_cis_images w
				WHERE w.ext_id = v.ext_id AND w.preview LIKE '%%.webp%%'))
			AND v.raw IS NOT NULL AND v.raw != ''
		GROUP BY v.ext_id
		HAVING COUNT(i.id) = 0 OR MAX(v.update_images) = 1
		LIMIT ?
	`, prodTable), s.imageBaseURL+"%", imagesBatchSize)
	if err != nil {
		return fmt.Errorf("query vehicles needing images: %w", err)
	}

	if len(extIDs) == 0 {
		return nil
	}

	log.Printf("processing images for %d vehicles with %d workers", len(extIDs), imagesWorkers)

	client := &http.Client{
		Timeout: 30 * time.Second,
	}

	extIDCh := make(chan int, len(extIDs))
	for _, id := range extIDs {
		extIDCh <- id
	}
	close(extIDCh)

	var wg sync.WaitGroup
	var processed atomic.Int32
	var errCount atomic.Int32

	for range imagesWorkers {
		wg.Add(1)
		go func() {
			defer wg.Done()
			for extID := range extIDCh {
				if err := s.processVehicleImages(client, extID, prodTable); err != nil {
					log.Printf("images: vehicle %d: %v", extID, err)
					errCount.Add(1)
				} else {
					processed.Add(1)
				}
			}
		}()
	}

	go func() {
		ticker := time.NewTicker(30 * time.Second)
		defer ticker.Stop()
		for range ticker.C {
			p := processed.Load()
			log.Printf("images: %d/%d processed (%d errors)", p, len(extIDs), errCount.Load())
			if p >= int32(len(extIDs)) {
				return
			}
		}
	}()

	wg.Wait()
	log.Printf("images: done %d/%d (%d errors)", processed.Load(), len(extIDs), errCount.Load())
	return nil
}

type imageDownload struct {
	K          int
	FullPath   string
	SmPath     string
	FullURL    string
	PreviewURL string
	DetailMD5  string
	PreviewMD5 string
}

func (s *Service) processVehicleImages(client *http.Client, extID int, table string) error {
	var rawJSON string
	if err := s.db.Get(&rawJSON, fmt.Sprintf("SELECT raw FROM %s WHERE ext_id = ?", table), extID); err != nil {
		return fmt.Errorf("get raw: %w", err)
	}

	var raw autocrm.VehicleRaw
	if err := json.Unmarshal([]byte(rawJSON), &raw); err != nil {
		return fmt.Errorf("unmarshal raw: %w", err)
	}

	if len(raw.Images) == 0 {
		detail, err := s.crm.GetVehicleDetail(extID)
		if err == nil && len(detail.Images) > 0 {
			log.Printf("images: vehicle %d: list had no images, fetched %d from detail", extID, len(detail.Images))
			raw = *detail
		}
	}

	vehDir := filepath.Join(s.uploadDir, "vehicles", strconv.Itoa(extID))
	smDir := filepath.Join(vehDir, "sm")

	if err := os.MkdirAll(smDir, 0755); err != nil {
		return fmt.Errorf("mkdir: %w", err)
	}

	// Stage 1: download all images to disk before touching the DB
	var downloaded []imageDownload
	for k, img := range raw.Images {
		filename := fmt.Sprintf("%02d.webp", k)

		fullURL := img.Full
		if fullURL == "" {
			fullURL = img.PreviewLarge
		}
		if fullURL == "" {
			continue
		}

		useOrientation := s.orient

		previewURL := img.Full
		if previewURL == "" {
			previewURL = img.PreviewLarge
		}
		if previewURL == "" {
			previewURL = img.PreviewSmall
		}
		if previewURL == "" {
			continue
		}

		fullPath := filepath.Join(vehDir, filename)
		smPath := filepath.Join(smDir, filename)

		d := imageDownload{K: k, FullPath: fullPath, SmPath: smPath, FullURL: fullURL, PreviewURL: previewURL}

		if err := downloadResizeSave(client, fullURL, fullPath, imgFullWidth, imgFullHeight, useOrientation); err != nil {
			log.Printf("full resize %d img %d: %v", extID, k, err)
		} else {
			d.DetailMD5 = fileMD5(fullPath)
		}

		orientPreview := useOrientation
		if err := downloadResizeSave(client, previewURL, smPath, imgPreviewWidth, imgPreviewHeight, orientPreview); err != nil {
			log.Printf("preview resize %d img %d: %v", extID, k, err)
		} else {
			d.PreviewMD5 = fileMD5(smPath)
		}

		if d.DetailMD5 != "" || d.PreviewMD5 != "" {
			downloaded = append(downloaded, d)
		}
	}

	// If no images downloaded successfully, keep existing ones and return
	if len(downloaded) == 0 {
		if len(raw.Images) == 0 {
			// Vehicle has no images at all — mark as done to avoid re-processing
			s.db.Exec(fmt.Sprintf("UPDATE %s SET use_internal_images = 1, update_images = 0 WHERE ext_id = ?", table), extID)
		}
		return fmt.Errorf("no images downloaded for %d, existing images preserved", extID)
	}

	// Stage 2: atomically swap — delete old DB rows, insert new ones
	s.db.Exec("DELETE FROM yapps_app_cis_images WHERE ext_id = ?", extID)
	for _, d := range downloaded {
		detailPath := fmt.Sprintf("/upload/Cis/vehicles/%d/%02d.webp?%s", extID, d.K, d.DetailMD5)
		previewPath := fmt.Sprintf("/upload/Cis/vehicles/%d/sm/%02d.webp?%s", extID, d.K, d.PreviewMD5)

		if _, err := s.db.Exec(
			"INSERT INTO yapps_app_cis_images (ext_id, detail, preview, number) VALUES (?, ?, ?, ?)",
			extID, detailPath, previewPath, d.K,
		); err != nil {
			log.Printf("insert image %d for %d: %v", d.K, extID, err)
		}
	}

	// Stage 3: mark as processed
	if _, err := s.db.Exec(
		fmt.Sprintf("UPDATE %s SET use_internal_images = 1, update_images = 0 WHERE ext_id = ?", table),
		extID,
	); err != nil {
		return fmt.Errorf("update flags: %w", err)
	}

	return nil
}

func isWhiteBackground(img image.Image) bool {
	bounds := img.Bounds()
	w := bounds.Dx()
	h := bounds.Dy()
	if w < 10 || h < 10 {
		return false
	}

	samples := []image.Point{
		// Углы
		{X: bounds.Min.X, Y: bounds.Min.Y},
		{X: bounds.Max.X - 1, Y: bounds.Min.Y},
		{X: bounds.Min.X, Y: bounds.Max.Y - 1},
		{X: bounds.Max.X - 1, Y: bounds.Max.Y - 1},
	}

	// Точки по границам
	for i := 1; i <= 5; i++ {
		x := bounds.Min.X + (w * i / 6)
		y := bounds.Min.Y + (h * i / 6)
		// Верхняя граница
		samples = append(samples, image.Point{X: x, Y: bounds.Min.Y})
		// Нижняя граница
		samples = append(samples, image.Point{X: x, Y: bounds.Max.Y - 1})
		// Левая граница
		samples = append(samples, image.Point{X: bounds.Min.X, Y: y})
		// Правая граница
		samples = append(samples, image.Point{X: bounds.Max.X - 1, Y: y})
	}

	whiteCount := 0
	for _, p := range samples {
		r, g, b, a := img.At(p.X, p.Y).RGBA()
		r8 := uint8(r >> 8)
		g8 := uint8(g >> 8)
		b8 := uint8(b >> 8)
		a8 := uint8(a >> 8)

		// Порог для белого/почти белого цвета ИЛИ прозрачного пикселя (альфа < 30)
		if a8 < 30 || (r8 >= 240 && g8 >= 240 && b8 >= 240) {
			whiteCount++
		}
	}

	// Если более 80% пикселей на границе белые/прозрачные — считаем фон белым (студийный рендер)
	ratio := float64(whiteCount) / float64(len(samples))
	return ratio >= 0.80
}

func downloadResizeSave(client *http.Client, url, savePath string, maxW, maxH int, orient *orientation.Detector) error {
	resp, err := client.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("status: %s", resp.Status)
	}

	src, _, err := image.Decode(resp.Body)
	if err != nil {
		return fmt.Errorf("decode: %w", err)
	}

	if orient != nil {
		corrected, deg, err := orient.CorrectRotation(src)
		if err == nil && deg != 0 {
			src = corrected
			log.Printf("orientation: corrected %d° for %s", deg, savePath)
		} else if err != nil {
			log.Printf("orientation: skip %s (%v)", savePath, err)
		}
	}

	srcBounds := src.Bounds()
	srcW := srcBounds.Dx()
	srcH := srcBounds.Dy()

	var fitW, fitH int
	var posX, posY int

	hasWhiteBg := isWhiteBackground(src)

	if hasWhiteBg {
		// Для студийных рендеров на белом фоне применяем Letterboxing (белые поля)
		if srcW*maxH > srcH*maxW {
			fitW = maxW
			fitH = srcH * maxW / srcW
			posX = 0
			posY = (maxH - fitH) / 2
		} else {
			fitH = maxH
			fitW = srcW * maxH / srcH
			posX = (maxW - fitW) / 2
			posY = 0
		}
	} else {
		// Для реальных фото растягиваем с обрезкой краев (Crop), чтобы не было белых полей
		if srcW*maxH > srcH*maxW {
			// Исходник шире -> вписываем по высоте, обрезаем бока
			fitH = maxH
			fitW = srcW * maxH / srcH
			posX = (maxW - fitW) / 2
			posY = 0
		} else {
			// Исходник выше -> вписываем по ширине, обрезаем верх/низ
			fitW = maxW
			fitH = srcH * maxW / srcW
			posX = 0
			posY = (maxH - fitH) / 2
		}
	}

	// Создаем новое изображение целевого размера
	dst := image.NewRGBA(image.Rect(0, 0, maxW, maxH))

	if hasWhiteBg {
		// Заливаем белым только для Letterbox
		whiteColor := color.RGBA{R: 255, G: 255, B: 255, A: 255}
		draw.Draw(dst, dst.Bounds(), &image.Uniform{whiteColor}, image.Point{}, draw.Src)
	}

	// Масштабируем Catmull-Rom
	rect := image.Rect(posX, posY, posX+fitW, posY+fitH)
	draw.CatmullRom.Scale(dst, rect, src, srcBounds, draw.Over, nil)

	if err := saveWebP(dst, savePath, imgWebPQuality); err != nil {
		return fmt.Errorf("encode webp: %w", err)
	}

	return nil
}

func saveWebP(img image.Image, path string, quality int) error {
	tmpFile, err := os.CreateTemp(filepath.Dir(path), "cwebp_*.png")
	if err != nil {
		return fmt.Errorf("temp png: %w", err)
	}
	tmpPath := tmpFile.Name()
	if err := png.Encode(tmpFile, img); err != nil {
		tmpFile.Close()
		os.Remove(tmpPath)
		return fmt.Errorf("png encode: %w", err)
	}
	tmpFile.Close()
	defer os.Remove(tmpPath)

	cmd := exec.Command("cwebp", "-quiet", "-q", fmt.Sprintf("%d", quality), tmpPath, "-o", path)
	var stderr bytes.Buffer
	cmd.Stderr = &stderr
	if err := cmd.Run(); err != nil {
		return fmt.Errorf("cwebp: %w (%s)", err, stderr.String())
	}
	return nil
}

func fileMD5(path string) string {
	f, err := os.Open(path)
	if err != nil {
		return ""
	}
	defer f.Close()

	h := md5.New()
	io.Copy(h, f)
	return hex.EncodeToString(h.Sum(nil))
}
