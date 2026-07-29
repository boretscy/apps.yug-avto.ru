package orientation

import (
	"image"
	"image/color"
	"image/jpeg"
	"os"
	"testing"
)

func TestDetectRotation(t *testing.T) {
	modelPath := os.Getenv("ONNX_MODEL_PATH")
	if modelPath == "" {
		t.Skip("ONNX_MODEL_PATH not set")
	}

	f, err := os.Open("../../test/1693786_d99ee6916c.jpg")
	if err != nil {
		t.Fatal(err)
	}
	defer f.Close()

	img, err := jpeg.Decode(f)
	if err != nil {
		t.Fatal(err)
	}

	det, err := NewDetector(modelPath)
	if err != nil {
		t.Fatal(err)
	}
	defer det.Destroy()

	deg, conf, err := det.DetectRotation(img)
	if err != nil {
		t.Fatal(err)
	}
	if deg != 90 {
		t.Errorf("expected 90°, got %d° (conf=%.2f)", deg, conf)
	}
	if conf < 0.1 {
		t.Errorf("confidence too low: %.2f", conf)
	}
}

func TestCorrectRotation(t *testing.T) {
	modelPath := os.Getenv("ONNX_MODEL_PATH")
	if modelPath == "" {
		t.Skip("ONNX_MODEL_PATH not set")
	}

	f, err := os.Open("../../test/1693786_d99ee6916c.jpg")
	if err != nil {
		t.Fatal(err)
	}
	defer f.Close()

	img, err := jpeg.Decode(f)
	if err != nil {
		t.Fatal(err)
	}

	det, err := NewDetector(modelPath)
	if err != nil {
		t.Fatal(err)
	}
	defer det.Destroy()

	corrected, deg, err := det.CorrectRotation(img)
	if err != nil {
		t.Fatal(err)
	}
	if deg != 90 {
		t.Errorf("expected 90° correction, got %d°", deg)
	}
	if corrected.Bounds().Dx() != img.Bounds().Dy() || corrected.Bounds().Dy() != img.Bounds().Dx() {
		t.Error("dimensions not swapped after 90° correction")
	}
}

func TestRotate90(t *testing.T) {
	img := image.NewRGBA(image.Rect(0, 0, 10, 20))
	rot := rotate90(img)
	b := rot.Bounds()
	if b.Dx() != 20 || b.Dy() != 10 {
		t.Fatalf("90°: expected 20x10, got %dx%d", b.Dx(), b.Dy())
	}
}

func TestRotate180(t *testing.T) {
	img := image.NewRGBA(image.Rect(0, 0, 10, 20))
	rot := rotate180(img)
	b := rot.Bounds()
	if b.Dx() != 10 || b.Dy() != 20 {
		t.Fatalf("180°: expected 10x20, got %dx%d", b.Dx(), b.Dy())
	}
}

func TestRotate270(t *testing.T) {
	img := image.NewRGBA(image.Rect(0, 0, 10, 20))
	rot := rotate270(img)
	b := rot.Bounds()
	if b.Dx() != 20 || b.Dy() != 10 {
		t.Fatalf("270°: expected 20x10, got %dx%d", b.Dx(), b.Dy())
	}
}

func TestRotateIdempotent(t *testing.T) {
	img := image.NewRGBA(image.Rect(0, 0, 10, 20))
	img.Set(0, 0, color.RGBA{255, 0, 0, 255})

	var r image.Image = img
	for i := 0; i < 4; i++ {
		r = rotate90(r)
	}
	if r.At(0, 0) != img.At(0, 0) {
		t.Error("4×90° rotation changed pixel")
	}
}

func TestRotate90Then270(t *testing.T) {
	img := image.NewRGBA(image.Rect(0, 0, 10, 20))
	img.Set(0, 0, color.RGBA{255, 0, 0, 255})

	r2 := rotate270(rotate90(img))
	if r2.At(0, 0) != img.At(0, 0) {
		t.Error("90°+270° changed pixel")
	}
}
