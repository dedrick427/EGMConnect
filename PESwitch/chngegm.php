<!DOCTYPE html>
<html>
<body>
<?php
  $egm = $_GET['egm'];
  $vlan = $_GET['vlan'];
  shell_exec("sudo /var/www/chngegm.sh $egm $vlan");
?>
</body>
</html>
