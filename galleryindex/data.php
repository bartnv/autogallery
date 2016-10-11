<?

function error($text) {
  header('Content-type: text/plain');
  print $text;
  exit;
}

if (empty($_GET['req'])) exit;

if ($_GET['req'] == "dirs") {
  header("Content-Type: application/json");

  $dirs = array();
  $maxmtime = 0;

  $dir = opendir(".");
  while ($dentry = readdir($dir)) {
    if (($dentry == ".") || ($dentry == "..")) continue;
    if (is_dir($dentry) && is_file($dentry . "/data.php")) {
      $dirs[] = $dentry;
      $mtime = filemtime($images);
      if ($mtime > $maxmtime) $maxmtime = $mtime;
    }
  }

  header("Last-Modified: ".gmdate("D, d M Y H:i:s", $maxmtime)." GMT");

  $res = array('dirs' => $dirs);
  print json_encode($res);
}
elseif ($_GET['req'] == "list") {
  header("Content-Type: application/json");

  print "{ \"files\" : [ ";
  $first = 1;

  $dir = opendir(".");
  while ($dentry = readdir($dir)) {
    if (($dentry == ".") || ($dentry == "..")) continue;
    if (is_dir($dentry) && is_file($dentry . "/data.php")) {
      $mtime = filemtime($dentry);
      if ($mtime > $maxmtime) $maxmtime = $mtime;
      $subdir = opendir($dentry);
      while ($file = readdir($subdir)) {
        if (preg_match('/\.jpe?g$/i', $file)) {
          if (!$first) print ", \"$dentry/$file\"";
          else {
            print "\"$dentry/$file\"";
            $first = 0;
          }
        }
      }
    }
  }
  print " ] }";
}
