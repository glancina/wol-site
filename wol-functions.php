  <?php
    define ('WOL-FUNCTIONS', true);
    #
    # pull in configuration data
    if (! defined('WOL-CONFIG')) {
      require 'wol-config.php';
    }

    #
    # Function to parse /etc/ethers
    # Assumed format of /etc/ethers:
    # e0:3f:49:11:ed:aa       lanscape
    # 00:21:70:36:8e:58       hera
    # $ethers looks like:
    # $ethers[host][0]: mac address
    # $ethers[host][1]: bcast address
    # $ethers[host][2]: ip address
    function parse_ethers() {
      global $ethers;
      global $bcast_address;
      $ethers_empty = false;
      if (! isset($ethers)) { $ethers_empty = true; };
      $etc_ethers = fopen('/etc/ethers','r');
      $currline = fgets($etc_ethers);
      while(! feof($etc_ethers)) {
	# skip over commented lines
	if ( substr( $currline,0,1) == '#' ) { break; };
        # split the current line and assign
        # $tmparray[0]: mac address
        # $tmparray[1]: hostname
	$tmparray = preg_split('/[\s]+/', $currline);
        # if the ethers array is empty, i.e. not pre-populated in wol-config.php,
	if ( $ethers_empty ) {
          # This needs work - array_key must be set as well
          # set mac address
	  $ethers[$tmparray[1]][0]=$tmparray[0];
          # if bcast address is not set, default to $bcast_address[0]
          if ( ! isset( $ethers[$tmparray[1]][1]) ) { $ethers[$tmparray[1]][1]=$bcast_address[0]; }
          #echo "DEBUG parse_ethers()-ethers_empty: \$ethers[\$tmparray[1]][0]: " . $ethers[$tmparray[1]][0] . "<br />";
          #echo "DEBUG parse_ethers()-ethers_empty: \$ethers[\$tmparray[1]][1]: " . $ethers[$tmparray[1]][1] . "<br />";
	} else {
	  if (array_key_exists($tmparray[1],$ethers)) {
	    $ethers[$tmparray[1]][0]=$tmparray[0];
            #echo "DEBUG parse_ethers(): \$ethers[\$tmparray[1]][0]: " . $ethers[$tmparray[1]][0] . "<br />";
            # if bcast address is not set, default to $bcast_address[0]
            if ( ! isset( $ethers[$tmparray[1]][1]) ) { $ethers[$tmparray[1]][1]=$bcast_address[0]; }
	  }
	}
	$currline = fgets($etc_ethers);
      }
      /*
      echo "DEBUG parse_ethers(): host:ether:bcast<br />";
      foreach($ethers as $host => $subarray) {
        echo "$host:$subarray[0]:$subarray[1]<br />";
      }
      */
    }

    # resolves host using one of dns, wins, or broadcast
    # returns true if resolved using requested method
    # adds resolved IP address on to $ethers array 
    # INPUT $method: string 'dns','wins',broadcast'
    #       $host: string host from $ethers array
    # RETURNS boolean true if successfully resolved
    function host_resolve($method,$host) {
      global $wins_host;
      global $bcast_address;
      global $ethers;
      switch ($method) {
	case 'dns':
	  $tmp_output = `host -W 1 $host`;
	  $tmp_array = parse_hostlookup($tmp_output);
	  if ($tmp_array[1] == $host) {
	    # add ip address onto $ethers record for this host
	    $ethers[$host][2] = chop($tmp_array[0]);
	    return true;
	  } else {
	    return false;
	  }
	  break;
	case 'wins':
	  #echo "nmblookup -U $wins_host -R $host | grep -v '^querying'";
	  $tmp_output = `nmblookup -U $wins_host -R $host | grep -v '^querying'`;
	  $tmp_array = parse_nmblookup($tmp_output);
	  if ($tmp_array[1] == $host) {
	    # add ip address onto $ethers record for this host
	    $ethers[$host][2] = $tmp_array[0];
	    return true;
	  } else {
	    return false;
	  }
	  break;
	case 'broadcast':
	  #echo "nmblookup $host<br \>";
	  $tmp_output = `nmblookup $host`;
	  $tmp_array = parse_nmblookup($tmp_output);
	  if ($tmp_array[1] == $host) {
	    # add ip address onto $ethers record for this host
	    $ethers[$host][2] = $tmp_array[0];
	    return true;
	  } else {
	    return false;
	  }
	  break;
      }
    };

    function host_ping($host) {
      global $ethers;
      $rc = 0;
      $tmp_output ="";
      #echo "host_ping: \$host as passed: $host<br />";
      $ip = $ethers[$host][2];
      #echo "host_ping: \$ip: $ip<br />";
      #echo "ping -c 1 -i 0.2 -w 0.2 -q $ip >/dev/null<br \>";
      $cmd_line = escapeshellcmd("ping -c 1 -i 0.2 -w 0.2 -q " . $ip);
      #echo "host_ping: \$cmd_line: $cmd_line<br />";
      exec($cmd_line,$tmp_output,$rc);
      #echo "host_ping: \$rc: $rc<br />";
      if ($rc == 0) {
	#echo "$host is up<br />";
	return true;
      } else {
	#echo "$host is not up<br />";
	return false;
      }
    }

    function host_status($host) {
      global $resolver_order;
      global $ethers;
      $isup = false;
      $resolved = false;
      #echo "host_status: \$host as passed: $host<br />";
      foreach($resolver_order as $method) {
	$resolved = host_resolve($method,$host);
	if ($resolved) {
	  #echo "host_status: $host is resolved; pinging ". $ethers[$host][2] . "<br />";
	  $isup = host_ping($host);
	  if ($isup) {
	    #echo "host_status: $host is resolved; pinged " . $ethers[$host][2] ."; $host is up<br />";
	    #echo "host_status: \$isup: " . print_r($isup) . "<br />";
	    break;
	  } else {
	    #echo "host_status: $host is resolved; pinged " . $ethers[$host][2] ."; $host is NOT up<br />";
	    #echo "host_status: \$isup: " . print_r($isup) . "<br />";
	    $isup = false;
	    break;
	  }
	}
      }
      return array($resolved,$isup);
    };


    function wol($host) {
      global $ethers;
      # wol -i 10.15.1.255 <name in /etc/ethers>
      $cmd_line = escapeshellcmd("/usr/bin/wol -i " . $ethers[$host][1] . " " . $host);
      #echo "DEBUG wol \$cmd_line: $cmd_line<br />";
      exec($cmd_line,$tmp_output,$rc);
      #echo "DEBUG wol \$rc: " . $rc. "<br />";
      if ($rc == 0) {
	return true;
      } else {
	return false;
      }
    };


    function parse_hostlookup($output) {
      # bucephalus.gbltech.net has address 192.168.1.1
      # Host wubba not found: 3(NXDOMAIN)
      $tmp_array = explode(" ",$output);
      #echo "DEBUG parse_hostlookup: " . htmlentities($tmp_array[1]) . " $tmp_array[0]<br />";
      if ($tmp_array[0] == "Host") {
	$tmp_array[1] = $tmp_array[2] . $tmp_array[3];
	return $tmp_array;
      }
      $tmp_array[1] = substr($tmp_array[0],0,strpos($tmp_array[0],"."));
      $tmp_array[0] = $tmp_array[3];
      $tmp_array = array_slice($tmp_array,0,2);
      #echo "DEBUG parse_hostlookup: " . htmlentities($tmp_array[1]) . " $tmp_array[0]<br />";
      return $tmp_array;
    }

    function parse_nmblookup($output) {
      # 192.168.1.103 hera<00>
      # name_query failed to find name bucephalus
      $tmp_array = explode(" ",$output);
      #echo "DEBUG parse_nmblookup: " . htmlentities($tmp_array[1]) . " $tmp_array[0]<br />";
      if ($tmp_array[1] == "failed") {
	return $tmp_array;
      }
      $tmp_array[1] = substr($tmp_array[1],0,strpos($tmp_array[1],"<"));
      #echo "DEBUG parse_nmblookup: " . htmlentities($tmp_array[1]) . " $tmp_array[0]<br />";
      return $tmp_array;
    }

  ?>
