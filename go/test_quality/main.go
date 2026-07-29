package main

import (
	"bytes"
	"crypto/tls"
	"fmt"
	"image"
	"image/jpeg"
	"image/png"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"time"

	"golang.org/x/image/draw"
)

const (
	fullW    = 634
	fullH    = 500
	previewW = 307
	previewH = 236
)

var urls = map[string]string{
	"full":  "https://65c09323-9edc-4678-9b9e-398a18d0f841.selstorage.ru/vehicle/1700689_ccc14a54fc.jpeg",
	"pl":    "https://65c09323-9edc-4678-9b9e-398a18d0f841.selstorage.ru/vehicle/1700689_ccc14a54fc_l.jpeg",
}

var client = &http.Client{
	Timeout: 30 * time.Second,
	Transport: &http.Transport{
		TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
	},
}

func download(url string) (image.Image, error) {
	resp, err := client.Get(url)
	if err != nil {
		return nil, fmt.Errorf("download: %w", err)
	}
	defer resp.Body.Close()
	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("status %d", resp.StatusCode)
	}
	img, _, err := image.Decode(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("decode: %w", err)
	}
	return img, nil
}

func calcCrop(srcW, srcH, maxW, maxH int) (intW, intH, cropX, cropY int) {
	if srcW*maxH >= srcH*maxW {
		intH = maxH
		intW = srcW * maxH / srcH
		cropX = (intW - maxW) / 2
		cropY = 0
	} else {
		intW = maxW
		intH = srcH * maxW / srcW
		cropX = 0
		cropY = (intH - maxH) / 2
	}
	return
}

func resizeCrop(src image.Image, maxW, maxH int, scaler draw.Scaler) image.Image {
	b := src.Bounds()
	srcW := b.Dx()
	srcH := b.Dy()

	intW, intH, cropX, cropY := calcCrop(srcW, srcH, maxW, maxH)
	intermediate := image.NewRGBA(image.Rect(0, 0, intW, intH))
	scaler.Scale(intermediate, intermediate.Bounds(), src, b, draw.Over, nil)
	cropped := intermediate.SubImage(image.Rect(cropX, cropY, cropX+maxW, cropY+maxH))
	return cropped
}

func saveJPEG(img image.Image, path string, quality int) error {
	f, err := os.Create(path)
	if err != nil {
		return err
	}
	defer f.Close()
	return jpeg.Encode(f, img, &jpeg.Options{Quality: quality})
}

func savePNG(img image.Image, path string) error {
	f, err := os.Create(path)
	if err != nil {
		return err
	}
	defer f.Close()
	return png.Encode(f, img)
}

func saveRaw(url, path string) error {
	resp, err := client.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	f, err := os.Create(path)
	if err != nil {
		return err
	}
	defer f.Close()
	_, err = io.Copy(f, resp.Body)
	return err
}

func savePNGTemp(img image.Image) (string, error) {
	f, err := os.CreateTemp("", "img_*.png")
	if err != nil {
		return "", err
	}
	defer f.Close()
	if err := png.Encode(f, img); err != nil {
		return "", err
	}
	return f.Name(), nil
}

func writePPM(img image.Image, w io.Writer) {
	b := img.Bounds()
	fmt.Fprintf(w, "P6\n%d %d\n255\n", b.Dx(), b.Dy())
	for y := b.Min.Y; y < b.Max.Y; y++ {
		for x := b.Min.X; x < b.Max.X; x++ {
			r, g, b, _ := img.At(x, y).RGBA()
			w.Write([]byte{byte(r >> 8), byte(g >> 8), byte(b >> 8)})
		}
	}
}

func savePPMTemp(img image.Image) (string, error) {
	f, err := os.CreateTemp("", "img_*.ppm")
	if err != nil {
		return "", err
	}
	defer f.Close()
	writePPM(img, f)
	return f.Name(), nil
}

func cjpegEncode(img image.Image, path string, quality int) error {
	ppmPath, err := savePPMTemp(img)
	if err != nil {
		return err
	}
	defer os.Remove(ppmPath)

	cmd := exec.Command("cjpeg", "-quality", fmt.Sprintf("%d", quality), "-outfile", path, ppmPath)
	var stderr bytes.Buffer
	cmd.Stderr = &stderr
	if err := cmd.Run(); err != nil {
		return fmt.Errorf("cjpeg: %w (%s)", err, stderr.String())
	}
	return nil
}

func jpegtranOptimize(path string) error {
	tmpPath := path + ".tmp"
	cmd := exec.Command("jpegtran", "-optimize", "-outfile", tmpPath, path)
	var stderr bytes.Buffer
	cmd.Stderr = &stderr
	if err := cmd.Run(); err != nil {
		return fmt.Errorf("jpegtran: %w (%s)", err, stderr.String())
	}
	return os.Rename(tmpPath, path)
}

func pngquantEncode(img image.Image, path string) error {
	tmpPath := path + ".tmp"
	f, err := os.Create(tmpPath)
	if err != nil {
		return err
	}
	if err := png.Encode(f, img); err != nil {
		f.Close()
		os.Remove(tmpPath)
		return err
	}
	f.Close()
	defer os.Remove(tmpPath)

	cmd := exec.Command("pngquant", "--quality=65-80", "--speed=1", "--output", path, tmpPath)
	var stderr bytes.Buffer
	cmd.Stderr = &stderr
	if err := cmd.Run(); err != nil {
		return fmt.Errorf("pngquant: %w (%s)", err, stderr.String())
	}
	return nil
}

type result struct {
	File string
	Desc string
}

func main() {
	testDir := filepath.Join("..", "test")
	os.MkdirAll(testDir, 0755)

	baseURL := "https://apps.avatr-yugavto.ru/test"
	var results []result

	fmt.Println("=== Загрузка оригиналов ===")

	fullSrc, err := download(urls["full"])
	if err != nil {
		fmt.Fprintf(os.Stderr, "ERROR: full download: %v\n", err)
		os.Exit(1)
	}
	b := fullSrc.Bounds()
	fmt.Printf("  full loaded: %dx%d\n", b.Dx(), b.Dy())

	plSrc, err := download(urls["pl"])
	if err != nil {
		fmt.Fprintf(os.Stderr, "ERROR: preview_large download: %v\n", err)
		os.Exit(1)
	}
	b = plSrc.Bounds()
	fmt.Printf("  preview_large loaded: %dx%d\n", b.Dx(), b.Dy())

	fmt.Println("\n=== Сохранение оригиналов ===")
	saveRaw(urls["full"], filepath.Join(testDir, "00_full_original_1280x960.jpg"))
	results = append(results, result{"00_full_original_1280x960.jpg", "оригинал full (1280×960)"})
	fmt.Println("  [OK] 00_full_original_1280x960.jpg")

	saveRaw(urls["pl"], filepath.Join(testDir, "00_sm_original_800x600.jpg"))
	results = append(results, result{"00_sm_original_800x600.jpg", "оригинал preview_large (800×600)"})
	fmt.Println("  [OK] 00_sm_original_800x600.jpg")

	fmt.Println("\n=== Полный размер 634×500 (из full 1280×960) ===")

	// baseline: ApproxBiLinear + JPEG Q95 (как сейчас в images.go)
	img := resizeCrop(fullSrc, fullW, fullH, draw.ApproxBiLinear)
	saveJPEG(img, filepath.Join(testDir, "00_full_baseline_634x500.jpg"), 95)
	results = append(results, result{"00_full_baseline_634x500.jpg", "как сейчас — ApproxBiLinear, JPEG Q95 (из 1280×960)"})
	fmt.Println("  [OK] 00_full_baseline_634x500.jpg — ApproxBiLinear + Q95")

	// BiLinear + JPEG Q100
	img = resizeCrop(fullSrc, fullW, fullH, draw.BiLinear)
	saveJPEG(img, filepath.Join(testDir, "00_full_bilinear_634x500.jpg"), 100)
	results = append(results, result{"00_full_bilinear_634x500.jpg", "BiLinear, JPEG Q100 (из 1280×960)"})
	fmt.Println("  [OK] 00_full_bilinear_634x500.jpg — BiLinear + JPEG Q100")

	// CatmullRom + JPEG Q100
	img = resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	saveJPEG(img, filepath.Join(testDir, "00_full_catrom_634x500.jpg"), 100)
	results = append(results, result{"00_full_catrom_634x500.jpg", "CatmullRom, JPEG Q100 (из 1280×960)"})
	fmt.Println("  [OK] 00_full_catrom_634x500.jpg — CatmullRom + JPEG Q100")

	// CatmullRom + PNG lossless
	img = resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	savePNG(img, filepath.Join(testDir, "00_full_catrom_634x500.png"))
	results = append(results, result{"00_full_catrom_634x500.png", "CatmullRom, PNG lossless (из 1280×960)"})
	fmt.Println("  [OK] 00_full_catrom_634x500.png — CatmullRom + PNG")

	// CatmullRom + cjpeg (libjpeg-turbo) Q100
	img = resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	if err := cjpegEncode(img, filepath.Join(testDir, "00_full_cjpeg_634x500.jpg"), 100); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: cjpeg: %v\n", err)
	} else {
		results = append(results, result{"00_full_cjpeg_634x500.jpg", "CatmullRom + cjpeg (libjpeg-turbo) Q100 (из 1280×960)"})
		fmt.Println("  [OK] 00_full_cjpeg_634x500.jpg — CatmullRom + cjpeg Q100")
	}

	// CatmullRom + JPEG Q100 + jpegtran -optimize
	img = resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	jpegPath := filepath.Join(testDir, "00_full_jpegtran_634x500.jpg")
	if err := saveJPEG(img, jpegPath, 100); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: save jpeg: %v\n", err)
	} else if err := jpegtranOptimize(jpegPath); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: jpegtran: %v\n", err)
	} else {
		results = append(results, result{"00_full_jpegtran_634x500.jpg", "CatmullRom, Go JPEG Q100 + jpegtran -optimize (из 1280×960)"})
		fmt.Println("  [OK] 00_full_jpegtran_634x500.jpg — CatmullRom + Go JPEG Q100 + jpegtran")
	}

	// WebP variants (from CatmullRom PNG temp)
	webpImg := resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	pngTmp, err := savePNGTemp(webpImg)
	if err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: savePNGTemp for webp: %v\n", err)
	} else {
		defer os.Remove(pngTmp)
		for _, w := range []struct{ args []string; suffix, desc string }{
			{[]string{"-q", "100"}, "q100", "CatmullRom + WebP Q100 (из 1280×960)"},
			{[]string{"-q", "90"}, "q90", "CatmullRom + WebP Q90 (из 1280×960)"},
			{[]string{"-q", "80"}, "q80", "CatmullRom + WebP Q80 (из 1280×960)"},
			{[]string{"-lossless"}, "lossless", "CatmullRom + WebP lossless (из 1280×960)"},
		} {
			outPath := filepath.Join(testDir, fmt.Sprintf("00_full_webp_%s_634x500.webp", w.suffix))
			args := append(w.args, pngTmp, "-o", outPath)
			cmd := exec.Command("cwebp", args...)
			var stderr bytes.Buffer
			cmd.Stderr = &stderr
			if err := cmd.Run(); err != nil {
				fmt.Fprintf(os.Stderr, "  ERROR: cwebp %s: %v (%s)\n", w.suffix, err, stderr.String())
			} else {
				results = append(results, result{fmt.Sprintf("00_full_webp_%s_634x500.webp", w.suffix), w.desc})
				fmt.Printf("  [OK] 00_full_webp_%s_634x500.webp — %s\n", w.suffix, w.desc)
			}
		}
	}

	// CatmullRom + PNG + pngquant
	img = resizeCrop(fullSrc, fullW, fullH, draw.CatmullRom)
	if err := pngquantEncode(img, filepath.Join(testDir, "00_full_pngquant_634x500.png")); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: pngquant: %v\n", err)
	} else {
		results = append(results, result{"00_full_pngquant_634x500.png", "CatmullRom + pngquant (из 1280×960)"})
		fmt.Println("  [OK] 00_full_pngquant_634x500.png — CatmullRom + pngquant")
	}

	fmt.Println("\n=== Полный размер 634×500 (из preview_large 800×600 — как сейчас) ===")

	// baseline из preview_large (как сейчас делает images.go)
	img = resizeCrop(plSrc, fullW, fullH, draw.ApproxBiLinear)
	saveJPEG(img, filepath.Join(testDir, "00_full_baseline_pl_634x500.jpg"), 95)
	results = append(results, result{"00_full_baseline_pl_634x500.jpg", "как сейчас — ApproxBiLinear, JPEG Q95 (из 800×600)"})
	fmt.Println("  [OK] 00_full_baseline_pl_634x500.jpg — ApproxBiLinear + Q95 (из PL)")

	// CatmullRom + JPEG Q100 из preview_large
	img = resizeCrop(plSrc, fullW, fullH, draw.CatmullRom)
	saveJPEG(img, filepath.Join(testDir, "00_full_catrom_pl_634x500.jpg"), 100)
	results = append(results, result{"00_full_catrom_pl_634x500.jpg", "CatmullRom, JPEG Q100 (из 800×600)"})
	fmt.Println("  [OK] 00_full_catrom_pl_634x500.jpg — CatmullRom + JPEG Q100 (из PL)")

	// CatmullRom + PNG lossless из preview_large
	img = resizeCrop(plSrc, fullW, fullH, draw.CatmullRom)
	savePNG(img, filepath.Join(testDir, "00_full_catrom_pl_634x500.png"))
	results = append(results, result{"00_full_catrom_pl_634x500.png", "CatmullRom, PNG lossless (из 800×600)"})
	fmt.Println("  [OK] 00_full_catrom_pl_634x500.png — CatmullRom + PNG (из PL)")

	fmt.Println("\n=== Превью 307×236 (из full 1280×960) ===")

	img = resizeCrop(fullSrc, previewW, previewH, draw.ApproxBiLinear)
	saveJPEG(img, filepath.Join(testDir, "00_sm_baseline_307x236.jpg"), 95)
	results = append(results, result{"00_sm_baseline_307x236.jpg", "как сейчас — ApproxBiLinear, JPEG Q95 (из 1280×960)"})
	fmt.Println("  [OK] 00_sm_baseline_307x236.jpg — ApproxBiLinear + Q95")

	img = resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	saveJPEG(img, filepath.Join(testDir, "00_sm_catrom_307x236.jpg"), 100)
	results = append(results, result{"00_sm_catrom_307x236.jpg", "CatmullRom, JPEG Q100 (из 1280×960)"})
	fmt.Println("  [OK] 00_sm_catrom_307x236.jpg — CatmullRom + JPEG Q100")

	img = resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	savePNG(img, filepath.Join(testDir, "00_sm_catrom_307x236.png"))
	results = append(results, result{"00_sm_catrom_307x236.png", "CatmullRom, PNG lossless (из 1280×960)"})
	fmt.Println("  [OK] 00_sm_catrom_307x236.png — CatmullRom + PNG")

	// cjpeg
	img = resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	if err := cjpegEncode(img, filepath.Join(testDir, "00_sm_cjpeg_307x236.jpg"), 100); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: cjpeg: %v\n", err)
	} else {
		results = append(results, result{"00_sm_cjpeg_307x236.jpg", "CatmullRom + cjpeg Q100 (из 1280×960)"})
		fmt.Println("  [OK] 00_sm_cjpeg_307x236.jpg — CatmullRom + cjpeg Q100")
	}

	// jpegtran
	img = resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	smJpegPath := filepath.Join(testDir, "00_sm_jpegtran_307x236.jpg")
	if err := saveJPEG(img, smJpegPath, 100); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: save jpeg: %v\n", err)
	} else if err := jpegtranOptimize(smJpegPath); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: jpegtran: %v\n", err)
	} else {
		results = append(results, result{"00_sm_jpegtran_307x236.jpg", "CatmullRom, Go JPEG Q100 + jpegtran (из 1280×960)"})
		fmt.Println("  [OK] 00_sm_jpegtran_307x236.jpg — CatmullRom + Go JPEG Q100 + jpegtran")
	}

	// WebP preview
	webpSm := resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	pngSmTmp, err := savePNGTemp(webpSm)
	if err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: savePNGTemp for sm webp: %v\n", err)
	} else {
		defer os.Remove(pngSmTmp)
		for _, w := range []struct{ args []string; suffix, desc string }{
			{[]string{"-q", "100"}, "q100", "CatmullRom + WebP Q100 (из 1280×960)"},
			{[]string{"-q", "90"}, "q90", "CatmullRom + WebP Q90 (из 1280×960)"},
			{[]string{"-q", "80"}, "q80", "CatmullRom + WebP Q80 (из 1280×960)"},
			{[]string{"-lossless"}, "lossless", "CatmullRom + WebP lossless (из 1280×960)"},
		} {
			outPath := filepath.Join(testDir, fmt.Sprintf("00_sm_webp_%s_307x236.webp", w.suffix))
			args := append(w.args, pngSmTmp, "-o", outPath)
			cmd := exec.Command("cwebp", args...)
			var stderr bytes.Buffer
			cmd.Stderr = &stderr
			if err := cmd.Run(); err != nil {
				fmt.Fprintf(os.Stderr, "  ERROR: cwebp %s: %v (%s)\n", w.suffix, err, stderr.String())
			} else {
				results = append(results, result{fmt.Sprintf("00_sm_webp_%s_307x236.webp", w.suffix), w.desc})
				fmt.Printf("  [OK] %s — %s\n", fmt.Sprintf("00_sm_webp_%s_307x236.webp", w.suffix), w.desc)
			}
		}
	}

	// pngquant
	img = resizeCrop(fullSrc, previewW, previewH, draw.CatmullRom)
	if err := pngquantEncode(img, filepath.Join(testDir, "00_sm_pngquant_307x236.png")); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: pngquant: %v\n", err)
	} else {
		results = append(results, result{"00_sm_pngquant_307x236.png", "CatmullRom + pngquant (из 1280×960)"})
		fmt.Println("  [OK] 00_sm_pngquant_307x236.png — CatmullRom + pngquant")
	}

	fmt.Println("\n=== Превью 307×236 (из preview_large 800×600) ===")

	img = resizeCrop(plSrc, previewW, previewH, draw.ApproxBiLinear)
	saveJPEG(img, filepath.Join(testDir, "00_sm_baseline_pl_307x236.jpg"), 95)
	results = append(results, result{"00_sm_baseline_pl_307x236.jpg", "как сейчас — ApproxBiLinear, JPEG Q95 (из 800×600)"})
	fmt.Println("  [OK] 00_sm_baseline_pl_307x236.jpg — ApproxBiLinear + Q95 (из PL)")

	img = resizeCrop(plSrc, previewW, previewH, draw.CatmullRom)
	saveJPEG(img, filepath.Join(testDir, "00_sm_catrom_pl_307x236.jpg"), 100)
	results = append(results, result{"00_sm_catrom_pl_307x236.jpg", "CatmullRom, JPEG Q100 (из 800×600)"})
	fmt.Println("  [OK] 00_sm_catrom_pl_307x236.jpg — CatmullRom + JPEG Q100 (из PL)")

	img = resizeCrop(plSrc, previewW, previewH, draw.CatmullRom)
	savePNG(img, filepath.Join(testDir, "00_sm_catrom_pl_307x236.png"))
	results = append(results, result{"00_sm_catrom_pl_307x236.png", "CatmullRom, PNG lossless (из 800×600)"})
	fmt.Println("  [OK] 00_sm_catrom_pl_307x236.png — CatmullRom + PNG (из PL)")

	// cjpeg PL
	img = resizeCrop(plSrc, previewW, previewH, draw.CatmullRom)
	if err := cjpegEncode(img, filepath.Join(testDir, "00_sm_cjpeg_pl_307x236.jpg"), 100); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: cjpeg: %v\n", err)
	} else {
		results = append(results, result{"00_sm_cjpeg_pl_307x236.jpg", "CatmullRom + cjpeg Q100 (из 800×600)"})
		fmt.Println("  [OK] 00_sm_cjpeg_pl_307x236.jpg — CatmullRom + cjpeg Q100 (из PL)")
	}

	// pngquant PL
	img = resizeCrop(plSrc, previewW, previewH, draw.CatmullRom)
	if err := pngquantEncode(img, filepath.Join(testDir, "00_sm_pngquant_pl_307x236.png")); err != nil {
		fmt.Fprintf(os.Stderr, "  ERROR: pngquant: %v\n", err)
	} else {
		results = append(results, result{"00_sm_pngquant_pl_307x236.png", "CatmullRom + pngquant (из 800×600)"})
		fmt.Println("  [OK] 00_sm_pngquant_pl_307x236.png — CatmullRom + pngquant (из PL)")
	}

	fmt.Println("\n========================================")
	fmt.Println("=== РЕЗУЛЬТАТЫ: авто 1700689, фото 00 ===")
	fmt.Println("========================================")
	fmt.Println()

	// attach file sizes to descriptions
	for i := range results {
		fi, err := os.Stat(filepath.Join(testDir, results[i].File))
		if err == nil {
			results[i].Desc = fmt.Sprintf("%s [%d KB]", results[i].Desc, (fi.Size()+512)/1024)
		}
	}

	for _, r := range results {
		fmt.Printf("  %s/%s — %s\n", baseURL, r.File, r.Desc)
	}
}
