#!/bin/bash
# Convert all .jpg images to .webp on disk and update DB URLs
# Usage: bash convert_jpg_to_webp.sh

set -e

UPLOAD_DIR="/var/www/admin/data/www/apps.avatr-yugavto.ru/upload/Cis/vehicles"
DB_USER="admin_apps_usr"
DB_PASS="n3oCTvk9NL"
DB_NAME="admin_apps"
QUALITY=80
WORKERS=10

echo "=== JPG → WebP bulk conversion ==="
echo "Finding .jpg files..."
JPG_COUNT=$(find "$UPLOAD_DIR" -name '*.jpg' | wc -l)
echo "Found $JPG_COUNT .jpg files to convert"

if [ "$JPG_COUNT" -eq 0 ]; then
    echo "No .jpg files found, nothing to do"
    exit 0
fi

# Convert files in parallel using xargs
echo "Converting with $WORKERS workers, quality=$QUALITY..."
find "$UPLOAD_DIR" -name '*.jpg' | xargs -P "$WORKERS" -I {} bash -c '
    src="$1"
    dst="${src%.jpg}.webp"
    if cwebp -quiet -q '"$QUALITY"' "$src" -o "$dst" 2>/dev/null; then
        rm "$src"
    else
        echo "FAILED: $src" >&2
    fi
' _ {}

REMAINING=$(find "$UPLOAD_DIR" -name '*.jpg' | wc -l)
CONVERTED=$((JPG_COUNT - REMAINING))
echo "Converted: $CONVERTED, Failed: $REMAINING"

# Update DB: replace .jpg with .webp in URLs and recalculate hash
echo "Updating database URLs..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
    UPDATE yapps_app_cis_images
    SET detail = CONCAT(
            SUBSTRING_INDEX(REPLACE(detail, '.jpg', '.webp'), '?', 1),
            '?',
            MD5(SUBSTRING_INDEX(REPLACE(detail, '.jpg', '.webp'), '?', 1))
        ),
        preview = CONCAT(
            SUBSTRING_INDEX(REPLACE(preview, '.jpg', '.webp'), '?', 1),
            '?',
            MD5(SUBSTRING_INDEX(REPLACE(preview, '.jpg', '.webp'), '?', 1))
        )
    WHERE preview LIKE '%.jpg%' OR detail LIKE '%.jpg%';
"

STILL_JPG=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM yapps_app_cis_images WHERE preview LIKE '%.jpg%';")
echo "DB records still with .jpg: $STILL_JPG"
echo "=== Done ==="
