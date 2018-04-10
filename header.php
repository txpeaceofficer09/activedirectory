<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$ldap_host = 'dc.example.com';
$ldap_user = 'domain\username';
$ldap_pass = 'password';

$ds = ldap_connect($ldap_host);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
$bd = ldap_bind($ds, $ldap_user, $ldap_pass);
$dn = isset($_GET['dn']) ? urldecode($_GET['dn']) : 'DC=example,DC=com';

require_once('func.php');

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Active Directory</title>
		<link rel="stylesheet" type="text/css" href="stylesheet.css" />
		<script src="jquery-3.3.1.min.js"></script>
		<script src="javascript.js"></script>
	</head>

	<body>