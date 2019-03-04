<?php
  define('WOL-CONFIG', true);
  # 
  # pull in required functions
  if (! defined('WOL-FUNCTIONS')) {
    require 'wol-functions.php';
  }
  #
  # Edit me START----------
  # initialize resolver order and types
    # order from:
    # dns, wins, broadcast
  $resolver_order = array('dns','wins','broadcast');
  #
  # broadcast address
  $bcast_address = array('192.168.2.255');
  #
  # WINS host
  $wins_host = '192.168.2.9';
  #
  # url path to wol.*
  $wol_url = '/wol';
  #
  # initialize list of hosts and MAC addresses:
  # from:
  # /etc/ethers
  # optional list of hosts to read from /etc/ethers
  # set $host_list = false to read entire file
  #$host_list = true;
  if ($host_list) {
    $ethers = array(
    "bucephalus"=>array("",$bcast_address[0],""),
    "castor"=>array("",$bcast_address[0],""),
    "pollux"=>array("",$bcast_address[0],""),
    "lanscape"=>array("",$bcast_address[0],""),
    "wubba"=>array("",$bcast_address[0],""),
    "skeeter"=>array("",$bcast_address[0],""),
    "hera"=>array("",$bcast_address[0],"")
    );
  }
  #
  # commment this out if you want to use a hard-coded array
  parse_ethers();
  #
  # example of hard-coded array
  if (! isset($ethers)) {
    $ethers = array(
    "bucephalus"=>array("f4:6d:04:4e:a7:bf",$bcast_address[0],""),
    "castor"=>array("c8:0a:a9:c8:82:3",$bcast_address[0],""),
    "pollux"=>array("c8:0a:a9:c8:38:46",$bcast_address[0],""),
    "lanscape"=>array("e0:3f:49:11:ed:aa",$bcast_address[0],""),
    "hera"=>array("00:21:70:36:8e:58",$bcast_address[0],"")
    );
  }
  /*
  echo "DEBUG: host:ether:bcast<br />";
  foreach($ethers as $host => $subarray) {
    echo "$host:$subarray[0]:$subarray[1]<br />";
  }
  */
  # Edit me END----------
?>
