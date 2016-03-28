Mosaic
======
Mosaic is collection of PHP CLI scripts to generate image mosaics, under BSD License.
It includes separate tools to slice and stitch images.

Get Started
-----------

#### Requirements
Prerequisite packages are:

* PHP 5.5.x/5.6.x
* PHP-Imagick module
* Composer

### Installation
```bash
git clone git://github.com/joao-pinheiro/mosaic.git
cd mosaic
composer update
```

Usage
-----

# Slice
Performs slicing of an image in w * h tiles, and generates a json map file for stitching purposes.
Available options:
         -f, --file     The input image to be sliced
         -w, --width    The number of horizontal tiles/blocks
         -h, --height   The number of vertical tiles/blocks
         -m, --mask     Optional output mask for files
         -o, --output   Output directory for both the tiles and the map

### Example
Slicing the image teste.jpg into a mosaic of 10 x 15 tiles, and write the result files into the existing directory result
```bash
php slice.php -f=teste.jpg -w=10 -h=15 -o=result
`
``
# Map
Generates stitching maps for arbitrary image file listings. This can be used to create evenly distributed contact sheets.

Available options:
        -f, --mask      The file list mask
        -w, --width     The number of horizontal tiles/blocks
        -h, --height    The number of horizontal tiles/blocks
        -o, --output    The resulting mapfile (it must be on the same directory as the images)

### Example
Generating a stitching map with 10 x 15  tiles for all jpg files in the directory photos
```bash
php map.php -f=photos/*.jpg -w=10 -h=15 -o=photos/map.json
```

# Stitch
Stitches a set of tiles in a single image, using a map file.

Available options:
        -m, --map       The map file to use (must be in the same directory as the images)
        -o, --output    The output file to generate
        -x, --spacex    Horizontal space between tiles, in pixels
        -y, --spacey    Vertical space between tiles, in pixels
        -b, --bgcolor   (Optional) Background color (HTML hex code)
        -c, --bordercolor   (Optional) border color (HTML hex code)
        -w, --borderwidth   (Optional) border width, in pixels
        -s, --fitstrategy   (Optional) how to place images in the tile (not used yet)

### Example
Stitching a mosaic with a vertical and horizontal interleave of 40px, a custom background color and a grey border 10px wide on each tile,
from a mapfile in the photos directory
```bash
php stitch.php -o=mosaic.jpg -x=40 -y=40 -b=#336699 -c=#222222 -w=10 --m=photos/map.json
```
