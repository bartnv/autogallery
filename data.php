<?

$images = ".";
$cache = ".imgcache";

$dims = array(800, 1280, 1440, 1920, 2560);

function error($text) {
  header('Content-type: text/plain');
  print $text;
  exit;
}

if (isset($_GET['img']) && !empty($_GET['img'])) {
  if (strpos($_GET['img'], '/') != FALSE) error('Invalid character "/" in image filename');
  if (isset($_GET['size']) && (is_numeric($_GET['size']) || ($_GET['size'] == "thumb") || ($_GET['size'] == "original"))) {
    if ($_GET['size'] == "original") {
      header('Content-type: image/jpeg');
      header('Content-length: ' . filesize($images . '/' . $_GET['img']));
      header('Last-modified: ' . gmdate("D, d M Y H:i:s", filemtime($filename) . ' GMT'));
      header('Content-disposition: attachment; filename="' . str_replace('"', '\"', $_GET['img']) . '"');
      readfile($images . '/' . $_GET['img']);
      exit;
    }
    elseif ($_GET['size'] == "thumb") {
      $dim = 95;
      $filename = $cache . '/thumb-' . $_GET['img'];
    }
    else {
      for ($i = 0; $dims[$i]; $i++) {
        if ($dims[$i] >= $_GET['size']) {
          $dim = $dims[$i];
          break;
        }
      }
      if (!$dim) $dim = end($dims);
      $filename = $cache . '/' . $dim . '-' . $_GET['img'];
    }
    if (!file_exists($filename) || (filemtime($filename) < filemtime($images . '/' . $_GET['img']))) {
      if (!list($w, $h) = getimagesize($images . '/' . $_GET['img'])) error('Unsupported image type');
      if ($dim > max($w, $h)) { // Requested size is larger than original; just send original
        $filename = $images . '/' . $_GET['img'];
      }
      else {
        $img = imagecreatefromjpeg($images . '/' . $_GET['img']);
        if ($_GET['size'] == "thumb") {
          $neww = $dim;
          $newh = $dim;
          $ratio = max($dim/$w, $dim/$h);
          $x = ($w - $dim / $ratio) / 2;
          $y = ($h - $dim / $ratio) / 2;
          $h = $dim / $ratio;
          $w = $h;
        }
        elseif ($w > $h) {
          $neww = $dim;
          $newh = $dim*($h/$w);
          $x = 0;
          $y = 0;
        }
        else {
          $neww = $dim*($w/$h);
          $newh = $dim;
          $x = 0;
          $y = 0;
        }
        $new = imagecreatetruecolor($neww, $newh);
        imagecopyresampled($new, $img, 0, 0, $x, $y, $neww, $newh, $w, $h);
        imagejpeg($new, $filename);
      }
    }
    header('Content-type: image/jpeg');
    header('Content-length: ' . filesize($filename));
    header('Last-modified: ' . gmdate("D, d M Y H:i:s", filemtime($filename) . ' GMT'));
    readfile($filename);
  }
}
elseif (isset($_GET['zip']) && !empty($_GET['zip'])) {
  $dirname = basename(getcwd());
  if (empty($dirname)) $dirname = "full-gallery";
  chdir($images);

  header('Content-type: application/octet-stream');
  header('Content-disposition: attachment; filename=' . $dirname . '.zip');
  $fp = popen('zip -r - *', 'r');
  $buf = '';
  while (!feof($fp)) {
    $buf = fread($fp, 8192);
    print $buf;
//    flush();
  }
  pclose($fp);
}
elseif (isset($_GET['clean']) && !empty($_GET['clean'])) {
  header('Content-type: text/plain');
  header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");

  if (filemtime($images) > filemtime($cache)) {
    print "Image directory has been changed more recently than cache directory; checking for orphaned resizes...\n\n";
    $dir = opendir($cache);
    while ($dentry = readdir($dir)) {
      if (preg_match('/^(thumb|[0-9]+)-(.*)$/', $dentry, $matches)) {
        if (!file_exists($images . '/' . $matches[2])) {
          print "Removing $dentry...\n";
          unlink($cache . '/' . $dentry);
        }
      }
    }
  }
  if (filemtime(__FILE__) > filemtime($cache)) {
    print basename(__FILE__) . " has been changed more recently than cache directory; checking for deprecated resizes...\n\n";
    $dir = opendir($cache);
    while ($dentry = readdir($dir)) {
      if (preg_match('/^([0-9]+)-/', $dentry, $matches)) {
        if (!in_array($matches[1], $dims)) {
          print "Removing $dentry...\n";
          unlink($cache . '/' . $dentry);
        }
      }
    }
  }
  print "\nClean run done.\n";
}
else {
  header("Content-Type: application/json");
  $mtime = filemtime($images);
  header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mtime)." GMT");

  $dir = opendir($images);
  print "{ \"files\" : [ ";
  $first = 1;
  while ($dentry = readdir($dir)) {
    if (preg_match('/\.jpe?g$/i', $dentry)) {
      if (!$first) print ", \"$dentry\"";
      else {
        print "\"$dentry\"";
        $first = 0;
      }
    }
  }
  print " ] }";
}
