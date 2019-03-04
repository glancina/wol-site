  <?php
    define ('WOL-AJAX', true);
    #
    # pull in configuration data
    if (! defined('WOL-CONFIG')) {
      require 'wol-config.php';
    }
    #
    # pull in functions
    if (! defined('WOL-FUNCTIONS')) {
      require 'wol-functions.php';
    }
    #echo "function: " . $_REQUEST['function'] . "<br />";
    #echo "host: " . $_REQUEST['host'] . "<br />";
    $fx = $_REQUEST['function'];
    $host = $_REQUEST['host'];

    switch($fx) {
    case 'host_status':
      $status = host_status($host);
      if ($status[0]) {
        $ip = $ethers[$host][2];
      } else {
        $ip = "unknown";
      }
      if ($status[1]) {
        $responding = "up";
      } else {
        $responding = "down";
      }
      #$status = json_encode(array($responding,$ip));
      #header("Content-Type: text/json");
      header("Cache-control: no-cache, no-store, must-revalidate");
      header("Pragma: no-cache");
      header("Expires: -1");
      header("Connection: close");
      $status = $host . " " . $responding . " " . $ip;
      #$status = trim($status);
      echo $status;
      break;
    case 'wol':
      $wolstatus = wol($host);
      #echo "DEBUG wol-ajax:wol \$status: " . $wolstatus . "<br />";
      if ($wolstatus) {
	echo "sent";
      } else {
	echo "error";
      }
      break;
    case 'host_ping':
      $status = host_ping($host);
      #$status = trim($status);
      echo $status;
      break;
    }
  ?>

