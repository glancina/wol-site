<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <meta http-equiv = "Content-Type" content = "text/html; charset = UTF-8" />
    <title>Wake-on-LAN</title>
    <link rel="stylesheet" type="text/css" href="css/wol.css" />
    <script src="js/wol.js" type="text/javascript"></script>
  </head>
  <body>
  <?php
    #
    # pull in configuration data
    if (! defined('WOL-CONFIG')) {
      require 'wol-config.php';
    }
    #
    # pull in required functions
    if (! defined('WOL-FUNCTIONS')) {
      require 'wol-functions.php';
    }
    #echo json_encode($ethers);
  ?>

  <script type="text/javascript">
    // pass $ethers array to javascript
    var ethers = <?php echo json_encode($ethers); ?>;
    // pass url to wol*.php to javascript
    var wol_url = <?php echo json_encode($wol_url); ?>;
  </script>

  <div class="body-text-mono">
  <table id="tblWOLHost">
  <thead>
  <tr>
    <th>host</th>
    <th>status</th>
    <th id="ip">ip</th>
    <th id="Wakeall"><button type="button" id="btnWakeall">WOL</button></th>
  </tr>
  </thead>
  <tbody>
<?php
    $i = 0;
    foreach(array_keys($ethers) as $host) {
      if ($i % 2) {
	echo "  <tr class=\"alt\">\n";
      } else {
	echo "  <tr>\n";
      }
      $responding = "";
      $ip = "";
      echo "<td id=\"$host-host\">$host</td>";
      echo "<td id=\"$host-status\">$responding</td>";
      echo "<td id=\"$host-ip\">$ip</td>\n";
      echo "<td id=\"$host-wake\"><button type=\"button\" id=\"btn$host-wake\">wol</button></td>";
      echo "  </tr>\n";
      $i++;
    }
  ?>
  </tbody>
  </table>
  </div>

  <!--
    <p>
      <a href="http://validator.w3.org/check?uri=referer">
	<img src="http://www.w3.org/Icons/valid-xhtml10"
	  alt="Valid XHTML 1.0 Strict" />
      </a>
    </p>

    <p>
      <a href="http://jigsaw.w3.org/css-validator/check/referer">
	<img src="http://jigsaw.w3.org/css-validator/images/vcss"
	  alt="Valid CSS" />
      </a>
    </p>
  -->

  </body>
</html>

