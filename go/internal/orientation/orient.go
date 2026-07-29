package orientation

import (
	"image"
)

func (d *Detector) DetectRotation(img image.Image) (degrees int, confidence float32, err error) {
	if d == nil {
		return 0, 0, nil
	}

	rotations := []struct {
		img     image.Image
		degrees int
	}{
		{img, 0},
		{rotate90(img), 90},
		{rotate180(img), 180},
		{rotate270(img), 270},
	}

	type candidate struct {
		degrees   int
		count     int
		totalConf float32
	}

	var candidates [4]candidate
	for i, r := range rotations {
		preds := d.detect(r.img)
		for _, p := range preds {
			if p.classID != carClassID {
				continue
			}
			if p.w <= p.h {
				continue
			}
			candidates[i].count++
			candidates[i].totalConf += p.score
		}
		candidates[i].degrees = r.degrees
	}

	best := candidates[0]
	for i := 1; i < 4; i++ {
		c := candidates[i]
		if c.count >= 2 && (c.count > best.count || (c.count == best.count && c.totalConf > best.totalConf)) {
			best = c
		}
	}

	return best.degrees, best.totalConf, nil
}

func (d *Detector) CorrectRotation(img image.Image) (image.Image, int, error) {
	deg, conf, err := d.DetectRotation(img)
	if err != nil || deg == 0 {
		return img, 0, err
	}
	_ = conf

	var rotated image.Image
	switch deg {
	case 90:
		rotated = rotate90(img)
	case 180:
		rotated = rotate180(img)
	case 270:
		rotated = rotate270(img)
	default:
		return img, 0, nil
	}
	return rotated, deg, nil
}

func rotate90(src image.Image) image.Image {
	b := src.Bounds()
	dst := image.NewRGBA(image.Rect(0, 0, b.Dy(), b.Dx()))
	for y := b.Min.Y; y < b.Max.Y; y++ {
		for x := b.Min.X; x < b.Max.X; x++ {
			dst.Set(b.Max.Y-1-y, x, src.At(x, y))
		}
	}
	return dst
}

func rotate180(src image.Image) image.Image {
	b := src.Bounds()
	w := b.Dx()
	h := b.Dy()
	dst := image.NewRGBA(image.Rect(0, 0, w, h))
	for y := 0; y < h; y++ {
		for x := 0; x < w; x++ {
			dst.Set(w-1-x, h-1-y, src.At(x, y))
		}
	}
	return dst
}

func rotate270(src image.Image) image.Image {
	b := src.Bounds()
	dst := image.NewRGBA(image.Rect(0, 0, b.Dy(), b.Dx()))
	for y := b.Min.Y; y < b.Max.Y; y++ {
		for x := b.Min.X; x < b.Max.X; x++ {
			dst.Set(y, b.Max.X-1-x, src.At(x, y))
		}
	}
	return dst
}
