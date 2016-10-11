# MMVI AutoGallery
Lightweight photogallery with automatic thumbnails and resizes

## Requirements
  * PHP GD module (usually available by default)
  * For ZIP-download: a commandline 'zip'-utility

## Getting started
  * Put index.html and data.php in a directory with JPEG-images
  * Create a directory ".imgcache" that PHP has write permissions on

## Tweaking
The paths to the images and the cache directory can be configured at the top of
the data.php file. Additionally, the dimensions of the generated resizes are
set there in the $dims array.

## Technical details
AutoGallery sends clients resized versions of the images based on the client's
window dimensions. The first available size that is equal or larger than the
client's window is chosen. If no resize is sufficient, the original image will
be sent.

Thumbnails and resizes are automatically generated the first time they are
needed and will be updated if the original file's last-modified timestamp
changes.

The data.php script has a cleaning mode to clean up the cache directory. This
is useful if you've changed the images or the $dims array. To do this, call
data.php?clean=1 from your browser.
