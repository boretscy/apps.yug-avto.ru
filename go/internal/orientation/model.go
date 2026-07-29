package orientation

import (
	"fmt"
	"image"
	"log"
	"math"
	"sort"

	ort "github.com/yalue/onnxruntime_go"
	"golang.org/x/image/draw"
)

const (
	letterboxSize  = 640
	confThreshold  = 0.4
	iouThreshold   = 0.45
	carClassID     = 2
	numClasses     = 80
	numPredictions = 8400
)

type Detector struct {
	session *ort.AdvancedSession
	input   *ort.Tensor[float32]
	output  *ort.Tensor[float32]
	data    []float32
}

func NewDetector(modelPath string) (*Detector, error) {
	if modelPath == "" {
		return nil, nil
	}

	libPath := "/usr/lib/x86_64-linux-gnu/libonnxruntime.so"
	ort.SetSharedLibraryPath(libPath)

	if err := ort.InitializeEnvironment(); err != nil {
		return nil, fmt.Errorf("init onnx env: %w", err)
	}

	data := make([]float32, 1*3*letterboxSize*letterboxSize)

	inputShape := ort.NewShape(1, 3, letterboxSize, letterboxSize)
	input, err := ort.NewTensor(inputShape, data)
	if err != nil {
		ort.DestroyEnvironment()
		return nil, fmt.Errorf("input tensor: %w", err)
	}

	outputShape := ort.NewShape(1, numClasses+4, numPredictions)
	output, err := ort.NewEmptyTensor[float32](outputShape)
	if err != nil {
		input.Destroy()
		ort.DestroyEnvironment()
		return nil, fmt.Errorf("output tensor: %w", err)
	}

	session, err := ort.NewAdvancedSession(modelPath,
		[]string{"images"},
		[]string{"output0"},
		[]ort.Value{input},
		[]ort.Value{output},
		nil,
	)
	if err != nil {
		input.Destroy()
		output.Destroy()
		ort.DestroyEnvironment()
		return nil, fmt.Errorf("new session: %w", err)
	}

	return &Detector{
		session: session,
		input:   input,
		output:  output,
		data:    data,
	}, nil
}

func (d *Detector) Destroy() {
	if d == nil {
		return
	}
	d.session.Destroy()
	d.input.Destroy()
	d.output.Destroy()
	ort.DestroyEnvironment()
}

type pred struct {
	cx, cy, w, h float32
	classID      int
	score        float32
}

func (d *Detector) detect(img image.Image) []pred {
	d.preprocess(img)

	if err := d.session.Run(); err != nil {
		log.Printf("onnx run: %v", err)
		return nil
	}

	out := d.output.GetData()
	if len(out) == 0 {
		return nil
	}

	raw := make([]pred, 0, numPredictions)
	for p := 0; p < numPredictions; p++ {
		cx := out[0*numPredictions+p]
		cy := out[1*numPredictions+p]
		w := out[2*numPredictions+p]
		h := out[3*numPredictions+p]

		if w <= 0 || h <= 0 {
			continue
		}

		bestClass := 0
		bestScore := float32(0)
		for c := 0; c < numClasses; c++ {
			s := out[(4+c)*numPredictions+p]
			if s > bestScore {
				bestScore = s
				bestClass = c
			}
		}

		if bestScore >= confThreshold {
			raw = append(raw, pred{
				cx: cx, cy: cy, w: w, h: h,
				classID: bestClass,
				score:   bestScore,
			})
		}
	}

	return nms(raw)
}

func (d *Detector) preprocess(img image.Image) {
	src := toRGBA(img)
	srcW := src.Bounds().Dx()
	srcH := src.Bounds().Dy()

	scale := float64(letterboxSize) / float64(max(srcW, srcH))
	newW := max(1, int(float64(srcW)*scale))
	newH := max(1, int(float64(srcH)*scale))

	scaled := image.NewRGBA(image.Rect(0, 0, newW, newH))
	draw.ApproxBiLinear.Scale(scaled, scaled.Bounds(), src, src.Bounds(), draw.Over, nil)

	for y := 0; y < letterboxSize; y++ {
		for x := 0; x < letterboxSize; x++ {
			var r, g, b uint8
			offX := x - (letterboxSize-newW)/2
			offY := y - (letterboxSize-newH)/2
			if offX >= 0 && offX < newW && offY >= 0 && offY < newH {
				pos := offY*scaled.Stride + offX*4
				r = scaled.Pix[pos+0]
				g = scaled.Pix[pos+1]
				b = scaled.Pix[pos+2]
			}
			idx := y*letterboxSize + x
			d.data[0*letterboxSize*letterboxSize+idx] = float32(r) / 255.0
			d.data[1*letterboxSize*letterboxSize+idx] = float32(g) / 255.0
			d.data[2*letterboxSize*letterboxSize+idx] = float32(b) / 255.0
		}
	}
}

func nms(preds []pred) []pred {
	if len(preds) == 0 {
		return nil
	}
	sort.Slice(preds, func(i, j int) bool {
		return preds[i].score > preds[j].score
	})

	keep := make([]bool, len(preds))
	var result []pred

	for i := range preds {
		if keep[i] {
			continue
		}
		result = append(result, preds[i])
		for j := i + 1; j < len(preds); j++ {
			if !keep[j] && iou(preds[i], preds[j]) > iouThreshold {
				keep[j] = true
			}
		}
	}

	return result
}

func iou(a, b pred) float32 {
	ax1 := a.cx - a.w/2
	ay1 := a.cy - a.h/2
	ax2 := a.cx + a.w/2
	ay2 := a.cy + a.h/2
	bx1 := b.cx - b.w/2
	by1 := b.cy - b.h/2
	bx2 := b.cx + b.w/2
	by2 := b.cy + b.h/2

	ix1 := float32(math.Max(float64(ax1), float64(bx1)))
	iy1 := float32(math.Max(float64(ay1), float64(by1)))
	ix2 := float32(math.Min(float64(ax2), float64(bx2)))
	iy2 := float32(math.Min(float64(ay2), float64(by2)))

	inter := float32(0)
	if ix2 > ix1 && iy2 > iy1 {
		inter = (ix2 - ix1) * (iy2 - iy1)
	}

	aArea := a.w * a.h
	bArea := b.w * b.h
	union := aArea + bArea - inter
	if union <= 0 {
		return 0
	}
	return inter / union
}

func toRGBA(img image.Image) *image.RGBA {
	switch v := img.(type) {
	case *image.RGBA:
		return v
	default:
		b := img.Bounds()
		dst := image.NewRGBA(b)
		for y := b.Min.Y; y < b.Max.Y; y++ {
			for x := b.Min.X; x < b.Max.X; x++ {
				dst.Set(x, y, img.At(x, y))
			}
		}
		return dst
	}
}
