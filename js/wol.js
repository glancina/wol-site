function createRequest () {
  try {
    var request = new XMLHttpRequest ();
  }
  catch (TryMS) {
    try {
      request = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (OtherMS) {
      try {
	request = new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (failed) {
	request = null;
      }
    }
  }
  return request;
}
window.onload = initWolPage;

document.getElementById("btnWakeall").onclick = initWolPage;

function initWolPage() {
  //minutes to wait for host to come up (take into account 30 second delay):
  //wol_wait = 2.5;
  //seconds to wait before getting host status after wol packet is sent
  //wol_delay = 30;
  //seconds to delay before checking host status
  //status_delay = 10;
  //initialize a global associative array of timer variables for each host
  //timervar = {};
  //define a global associative array of status for each host
  host_status = {};

  document.getElementById("btnWakeall").disabled = true;

  // wol_url string variable is passed to us from wol.php
  //alert("wol_url: " + wol_url);
  // ethers array is passed to us from wol.php
  for(property in ethers) {
    host = property;
    host_status.host = null;
    getStatus(host); 
  }
}

/*
 * Sets up and sends Ajax request to get status of particular host;
 *   changes display to reflect returned status
 * Called by: initWolPage and processWol
 * Input: hostname to query
 * Return: nothing
*/
function getStatus(host_copy) {
  document.getElementById(host_copy + "-status").innerHTML = "<img src=\"/images/indicator.gif\"></img>";
  document.getElementById(host_copy + "-ip").innerHTML = "Resolving...";
  request = createRequest();
  if (request == null) {
    alert("Unable to create request");
    return;
  }
  var url = wol_url + "/wol-ajax.php";
  var params = "function=host_status";
  params += "&host=" + escape(host_copy);
  //asynchronous request
  request.open("GET",url+"?"+params,true);
  request.setRequestHeader("Cache-control", "no-cache, no-store, must-revalidate");
  request.setRequestHeader("Connection", "close");
  request.onreadystatechange = (function(request_copy) {
    return function() {
      if (request_copy.readyState == 4 && request_copy.status == 200) {
        var response = request_copy.responseText;
        response = response.trim();
        var response_array = response.split(" ");
        //set host status in global array
        host_status[response_array[0]] = response_array[1];
        fillStatusRow(response_array);
      }
    }
  }(request));
  request.send(null);
}

/*
 * Fills out row information for each host and enables/disables buttons
 * Called by: Ajax request callback function within getStatus()
 * Input: 3 element array(host,status,ip)
 * Return: nothing
*/
function fillStatusRow(array_copy) {
  var host = array_copy[0];
  var stat = array_copy[1];
  var ip = array_copy[2];
  var wakebtn = document.getElementById("btn" + host + "-wake");
  document.getElementById(host + "-status").innerHTML = stat;
  document.getElementById(host + "-ip").innerHTML = ip;
  if ( stat == "up" ) {
    wakebtn.disabled = true;
  } else {
    if (! wakebtn.onclick) {
      wakebtn.onclick = function() { sendwol(host) };
      wakebtn.disabled = false;
      document.getElementById('btnWakeall').disabled = false;
    }
  }
}

/*
 * Sets up and sends Ajax request to send wol packet to host;
 * Called by: onclick handler for wake button
 * Calls: processWol
 * Input: hostname to query
 * Return: nothing
*/
function sendwol(host_copy) {
  request = createRequest();
  if (request == null) {
    alert("Unable to create request");
    return;
  }
  var url = wol_url + "/wol-ajax.php";
  var params = "function=wol";
  params += "&host=" + escape(host_copy);
  request.open("GET",url+"?"+params,false);
  request.setRequestHeader("Cache-control", "no-cache, no-store, must-revalidate");
  request.setRequestHeader("Connection", "close");
  request.onreadystatechange = (function(request_copy) {
    return function() {
      if (request_copy.readyState == 4 && request_copy.status == 200) {
	var response = request_copy.responseText;
	response = response.trim();
	if ( response == "sent" ) {
	  processWol(host_copy);
	} else {
	  alert("wol command error");
	}
      }
    }
  }(request));
  request.send(null);
}

/*
 * After wol packet is sent successfully,
 * set up UI to allow user to check host status until host is up
 * Called by: sendwol
 * Input: hostname
 *   updates status field to "sent"
 *   changes wake button click handler to getStatus()
 *   changes wake button text to "chk" (check)
 *   enables wake button
*/
function processWol(host_copy) {
  document.getElementById(host_copy + "-status").innerHTML = "sent";
  var wakebtn = document.getElementById("btn" + host_copy + "-wake");
  wakebtn.onclick = function (){getStatus(host_copy);};
  wakebtn.textContent = "chk";
  wakebtn.disabled = false;
}

