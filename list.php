<?php

require_once('header.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	switch($_POST['action']) {
		case 'delete':
			// Delete the entries with a checked box.
		break;
	}
}

$users = array();
$computers = array();
$groups = array();

$result = ldap_list($ds, $dn, "samaccountname=*", array('objectclass', 'cn', 'operatingsystem', 'operatingsystemversion', 'sn', 'givenname', 'displayname', 'memberof', 'name', 'homedirectory', 'homedrive', 'samaccountname', 'accountexpires', 'pwdlastset', 'useraccountcontrol', 'lastlogoff', 'lastlogon', 'whencreated', 'whenchanged'));
$entries = ldap_get_entries($ds, $result);
for ($i=0; $i < $entries["count"]; $i++) {
	if (isComputer($entries[$i]['objectclass'])) {
		$computers[] = array('cn'=>$entries[$i]["cn"][0], 'os'=>$entries[$i]["operatingsystem"][0], 'version'=>$entries[$i]["operatingsystemversion"][0], 'lastlogon'=>$entries[$i]["lastlogon"][0], 'lastlogoff'=>$entries[$i]["lastlogoff"][0], 'whencreated'=>$entries[$i]["whencreated"][0], 'whenchanged'=>$entries[$i]["whenchanged"][0], 'useraccountcontrol'=>$entries[$i]["useraccountcontrol"][0]);
	} elseif (isUser($entries[$i]['objectclass'])) {
		$users[] = array('cn'=>$entries[$i]["cn"][0], 'sn'=>$entries[$i]["sn"][0], 'givenname'=>$entries[$i]["givenname"][0], 'displayname'=>$entries[$i]["displayname"][0], 'memberof'=>$entries[$i]["memberof"], 'name'=>$entries[$i]["name"], 'homedirectory'=>$entries[$i]["homedirectory"][0], 'homedrive'=>$entries[$i]["homedrive"][0], 'username'=>$entries[$i]["samaccountname"][0], 'accountexpires'=>$entries[$i]["accountexpires"][0], 'pwdlastset'=>$entries[$i]["pwdlastset"][0], 'useraccountcontrol'=>$entries[$i]["useraccountcontrol"][0], 'whencreated'=>$entries[$i]["whencreated"][0], 'whenchanged'=>$entries[$i]["whenchanged"][0]);
	} elseif (isGroup($entries[$i]['objectclass'])) {
		$groups[] = array('cn'=>$entries[$i]["cn"][0], 'whencreated'=>$entries[$i]["whencreated"][0], 'whenchanged'=>$entries[$i]["whenchanged"][0]);
	}
}

sort($users);
sort($computers);
sort($groups);

echo "<div class=\"dn\">".dnToPath($dn)."</div>";

// Use jQuery to submit the form inside .content with the POST method.
echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">";
echo "<input type=\"hidden\" name=\"dn\" value=\"".$dn."\" />";

if (count($computers) > 0) {
	echo "<table><tr><th></th><th></th><th><img src=\"lock.png\" /></th><th>Hostname</th><th>IP Address</th><th>Operating System</th><th>Operating System Version</th><th>Last Logon</th><th>Last Logoff</th><th class=\"hidden\">Created</th><th class=\"hidden\">Last Modified</th></tr>\r\n";
	foreach ($computers AS $id=>$computer) {
		echo "<tr><td><input type=\"checkbox\" /></td><td><img src=\"computer.png\" /></td><td><img src=\"".(isUserActive($computer['useraccountcontrol']) ? 'unlock' : 'lock').".png\" /></td><td class=\"nowrap\"><a href=\"show.php?dn=".urlencode($dn)."&id=".$computer['cn']."\" target=\"right\">".$computer['cn']."</a></td><td class=\"nowrap\">".(gethostbyname($computer['cn']) == $computer['cn'] ? '' : gethostbyname($computer['cn']))."</td><td class=\"nowrap\">".$computer['os']."</td><td class=\"nowrap\">".$computer['version']."</td><td class=\"nowrap\">".convertTime($computer['lastlogon'])."</td><td class=\"nowrap\">".convertTime($computer['lastlogoff'])."</td><td class=\"hidden nowrap\">".convertTime($computer['whencreated'])."</td><td class=\"hidden nowrap\">".convertTime($computer['whenchanged'])."</td></tr>\r\n";
	}
	echo "</table>\r\n";
}

if (count($users) > 0) {
	echo "<table><tr><th></th><th></th><th><img src=\"lock.png\" /></th><th>Name</th><th>Username</th><th>Member Of</th><th>Home Directory</th><th>Password Last Set</th><th class=\"hidden\">Created</th><th class=\"hidden\">Last Modified</th></tr>\r\n";
	foreach ($users AS $id=>$user) {
		echo "<tr><td><input type=\"checkbox\" /></td><td><img src=\"user.png\" /></td><td><img src=\"".(isUserActive($user['useraccountcontrol']) ? 'unlock' : 'lock').".png\" /></td><td class=\"nowrap\"><a href=\"show.php?dn=".urlencode($dn)."&id=".$user['cn']."\">".$user['cn']."</a></td><td class=\"nowrap\">".$user['username']."</td><td>".memberof($user['memberof'])."</td><td class=\"nowrap\">".$user['homedirectory']."</td><td class=\"nowrap\">".convertTime($user['pwdlastset'])."</td><td class=\"hidden nowrap\">".convertTime($user['whencreated'])."</td><td class=\"hidden nowrap\">".convertTime($user['whenchanged'])."</td></tr>\r\n";
	}
	echo "</table>\r\n";
}

if (count($groups) > 0) {
	echo "<table><tr><th></th><th></th><th>Name</th><th class=\"hidden\">Created</th><th class=\"hidden\">Last Modified</th></tr>\r\n";
	foreach ($groups AS $id=>$group) {
		echo "<tr><td><input type=\"checkbox\" /></td><td><img src=\"group.png\" /></td><td class=\"nowrap\"><a href=\"show.php?dn=".urlencode($dn)."&id=".$group['cn']."\">".$group['cn']."</a></td><td class=\"nowrap hidden\">".convertTime($group['whencreated'])."</td><td class=\"nowrap hidden\">".convertTime($group['whenchanged'])."</td></tr>\r\n";
	}
	echo "</table>\r\n";
}

if ((count($computers) == 0) && (count($users) == 0) && (count($groups) == 0)) {
	echo "<center><h3>Nothing in this OU.</h3></center>";
}

echo "</form>";

require_once('footer.php');

?>