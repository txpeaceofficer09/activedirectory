<?php require_once('header.php'); ?>
		
		<div class="divTable">
			<div class="divTableRow">
				<div class="divTableCell" id="header">
					<h1>Active Directory</h1>
				</div>
			</div>
			<div class="divTableRow">
				<div class="divTableCell">
					<div class="divTable">
						<div class="divTableRow">
							<div class="divTableCell" id="nav" valign="top">
<?php

echo "<ul class=\"list\">";
echo "<li><a onClick=\"loadPage('list.php?dn=".urlencode($dn)."');\">".dnToPath($dn)."</a>";
printTree(getOUs($ds, $dn));
echo "</li></ul>";

?>
							</div>
							<div class="divTableCell" id="content" valign="top">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

<script> loadPage('list.php'); </script>
		
<?php require_once('footer'); ?>