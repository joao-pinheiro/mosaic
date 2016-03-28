#!/bin/sh
php ../cli/slice.php -f=source_1.jpg -w=10 -h=20 -o=mosaic1
php ../cli/slice.php -f=source_2.jpg -w=8 -h=12 -o=mosaic2
php ../cli/stitch.php -m=mosaic1/map.json -o=mosaic_1.jpg -x=20 -y=20
php ../cli/stitch.php -m=mosaic2/map.json -o=mosaic_2.jpg -x=20 -y=20

