<?php
  $egm = $_GET['egm'];
  $res = shell_exec("sudo /var/www/curregm.sh $egm");
  $tres = trim($res);
  if ("" === "$tres") {
  echo "9999";
  } else {
  echo $tres;
  }
?>
