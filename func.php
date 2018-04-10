<?php

// Determine if the AD object is a computer from the objectclass array of the object.
function isComputer($arr) {
	$retVal = false;

	if (in_array('computer', $arr)) $retVal = true;

	return $retVal;
}

// Determine if the AD object is a user from the objectclass array of the object.
function isUser($arr) {
	$retVal = false;

	if (!in_array('computer', $arr) && in_array('person', $arr)) $retVal = true;

	return $retVal;
}

// Determine if the AD object is a security group from the objectclass array of the object.
function isGroup($arr) {
	$retVal = false;
	
	if (in_array('group', $arr)) $retVal = true;

	return $retVal;
}

function isContainer($arr) {
	$retVal = false;
	
	if (in_array('container', $arr)) $retVal = true;

	return $retVal;	
}

function isOU($arr) {
		$retVal = false;
	
	if (in_array('organizationalUnit', $arr)) $retVal = true;

	return $retVal;
}

// Return comma delimited string with values from the memberof array for an AD object.
function memberof($arr) {
	for ($i=0; $i < $arr['count']; $i++) {
		$arr[$i] = substr($arr[$i], 3, stripos($arr[$i], ',')-3);
	}

	$retVal = join(', ', $arr);
	return substr($retVal, stripos($retVal, ',')+2);
}

// Convert and return the time value from AD as a more user-friendly version.
function convertTime($time) {
	$winSecs = (int)($time / 10000000);
	$unixTimestamp = ($winSecs - 11644473600);
	// return date(DateTime::RFC822, $unixTimestamp);
	return date('m-d-Y H:i:s', $unixTimestamp);
}

function isUserActive($val) {
	$bitFlags = array(
		"TRUSTED_TO_AUTH_FOR_DELEGATION"=>16777216,
		"PASSWORD_EXPIRED"=>8388608,
		"DONT_REQ_PREAUTH"=>4194304,
		"USE_DES_KEY_ONLY"=>2097152,
		"NOT_DELEGATED"=>1048576,
		"TRUSTED_FOR_DELEGATION"=>524288,
		"SMARTCARD_REQUIRED"=>262144,
		"MNS_LOGON_ACCOUNT"=>131072,
		"DONT_EXPIRE_PASSWORD"=>65536,
		"SERVER_TRUST_ACCOUNT"=>8192,
		"WORKSTATION_TRUST_ACCOUNT"=>4096,
		"INTERDOMAIN_TRUST_ACCOUNT"=>2048,
		"NORMAL_ACCOUNT"=>512,
		"TEMP_DUPLICATE_ACCOUNT"=>256,
		"ENCRYPTED_TEXT_PWD_ALLOWED"=>128,
		"PASSWD_CANT_CHANGE"=>64,
		"PASSWD_NOTREQD"=>32,
		"LOCKOUT"=>16,
		"HOMEDIR_REQUIRED"=>8,
		// "ACCOUNT_DISABLED"=>2,  // We don't need to remove this so we can evaluate it.
		// "SCRIPT"=>1
	);
	
	foreach ($bitFlags AS $k=>$v) {
		if ($val >= $v) {
			$val = $val - $v;
		}
	}
	
	// Take the bitmask field useraccountcontrol and start with the largest attribute PASSWORD_EXPIRED and loop through the values if the value is smaller than
	// useraccountcontrol then subtract it from useraccountcontrol and move on until useraccountcontrol is 2 or 0.  If it is 2 or 3 then it is a locked account.
	
	return ($val >= 2) ? false : true;	
}

function dnToPath($dn) {
	$arr = explode(',', $dn);
	$ou = [];
	$dc = [];
	
	foreach ($arr AS $i) {
		if (substr($i, 0, 2) == "DC") {
			$dc[] = substr($i, 3);
		} elseif (substr($i, 0, 2) == "OU") {
			$ou[] = substr($i, 3);
		} else {
			$cn = substr($i, 3);
		}
	}
	
	if (count($dc) > 0) {
		$retVal .= join('.', $dc);
	}
	if (count($ou) > 0) {
		krsort($ou);
		$retVal .= " &gt; ".join(" &gt; ", $ou);
	}

	if (isset($cn)) $retVal .= " &gt; ".$cn;

	return $retVal;
}

function key_sort($a, $b) {
	return strcasecmp($a, $b);
}

function getOUs($ds, $dn) {
	// $filter = array('BYOD', 'Administration', 'Groups', 'Servers', 'Users', 'Former Employees', 'PrinterGroups', 'Technology', 'trans', 'teacher', 'Boys Coachs', 'Girls Coachs', 'test', 'Teacher', 'Kitchen', 'Office', 'CEC');
	$filter = array('ForeignSecurityPrincipals', 'Managed Service Accounts', 'Program Data', 'System');

	$ou = array();

	$result = ldap_list($ds, $dn, "name=*", array("ou", "cn", "objectclass")) or die("LDAP Error: ".ldap_error($ds));
	$entries = ldap_get_entries($ds, $result);
	if ($entries["count"] > 0) {
		for ($i=0; $i < $entries["count"]; $i++) {
			//if (!isComputer($entries[$i]["objectclass"]) && !isUser($entries[$i]["objectclass"]) && !isGroup($entries[$i]["objectclass"])) {
			if ((isContainer($entries[$i]["objectclass"]) || isOU($entries[$i]["objectclass"])) && (!in_array($entries[$i]["cn"][0], $filter) && !in_array($entries[$i]["ou"][0], $filter))) {
				$ou[$entries[$i]["dn"]] = getOUs($ds, $entries[$i]["dn"]);
			}
		}
	}
	uksort($ou, 'key_sort');
	return $ou;
}

// $ous = getOUs($ds, $dn);

function getOU($dn) {
	return substr($dn, 3, stripos($dn, ',')-3);
}

function printTree($arr) {
	echo "<ul>";
	foreach ($arr AS $k=>$v) {
		echo "<li><a onClick=\"loadPage('list.php?dn=".urlencode($k)."');\">".getOU($k)."</a>";
		if (count($v) > 0) {
			printTree($v);
		}
		echo "</li>";
	}
	echo "</ul>";
}

?>