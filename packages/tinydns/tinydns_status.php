<?php
/* $Id$ */
/*
	tinydns_status.php
	part of pfSense (http://www.pfsense.com/)

	Copyright (C) 2006 Scott Ullrich <sullrich@gmail.com>
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

require("guiconfig.inc");

/* Defaults to this page but if no settings are present, redirect to setup page */
if(!$config['installedpackages']['miniupnpd']['config'][0]['iface_array'])
	Header("Location: /pkg_edit.php?xml=tinydns.xml&id=0");

$pgtitle = "TinyDNS: Status";
include("head.inc");

?>
<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
<?php include("fbegin.inc"); ?>
<p class="pgtitle"><?=$pgtitle?></font></p>
<?php if ($savemsg) print_info_box($savemsg); ?>

<div id="mainlevel">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<?php
	$tab_array = array();
	$tab_array[] = array(gettext("Settings"), false, "/pkg_edit.php?xml=tinydns.xml&id=0");
	$tab_array[] = array(gettext("Domains"), false, "/tinydns_filter.php");
	$tab_array[] = array(gettext("Status"), true, "/tinydns_status.php");
	display_top_tabs($tab_array);
?>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
   <tr>
     <td class="tabcont" >
      <form action="tinydns_status.php" method="post">
    </form>
    </td>
   </tr>
   <tr>
    <td class="tabcont" >
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
          <td width="80%" class="listhdrr">IP</td>
          <td width="10%" class="listhdrr">Status</td>
          <td width="10%" class="listhdrr">In Service</td>
          <td width="10%" class="listhdrr">Response time</td>
		</tr>

<?php
$pingdir = return_dir_as_array("/var/db/pingstatus");
if(file_exists("/service/tinydns/root/data"))
	$tinydns_data = file_get_contents("/service/tinydns/root/data");
else
	$tinydns_data = "";
foreach($config['installedpackages']['tinydnsdomains']['config'] as $ping) {
	if($ping['recordtype'] == "SOA")
		continue;
	if(!$ping['row'])
		continue;
	$ipaddress = $ping['ipaddress'];
	$hostname  = $ping['hostname'];
	$status = file_get_contents("/var/db/pingstatus/$ipaddress");
	if(stristr($tinydns_data, "+{$hostname}:{$ipaddress}"))
		$inservice = "<FONT COLOR='GREEN'>YES</FONT>";
	else
		$inservice = "<FONT COLOR='BLUE'>NO</FONT>";
	echo "<tr>";
	echo "<td class=\"listlr\">";
	echo "$hostname<br>&nbsp;&nbsp;&nbsp;|->&nbsp;$ipaddress";
	echo "</td>";
	echo "<td class=\"listlr\">";
	if(stristr($status,"DOWN"))
		echo "<FONT COLOR='red'>DOWN</FONT>";
	else
		echo $status;
	echo "</td>";

	echo "<td class=\"listlr\">";
	echo $inservice;
	echo "</td>";

	echo "<td class=\"listlr\">";
	if(file_exists("/var/db/pingmsstatus/$ipaddress"))
		$msstatus = file_get_contents("/var/db/pingmsstatus/$ipaddress");
	else
		$msstatus = "N/A";
	echo $msstatus;
	echo "</td>";
	echo "</tr>";

	foreach($ping['row'] as $row) {
		$ipaddress = $row['failoverip'];
		$status = file_get_contents("/var/db/pingstatus/$ipaddress");
		echo "<tr>";
		echo "<td class=\"listlr\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|->&nbsp;&nbsp;";
		echo $ipaddress;
		if($row['loadbalance'])
			echo " (LB)";
		if(stristr($tinydns_data, "+{$hostname}:{$row['failoverip']}"))
			$inservice = "<FONT COLOR='GREEN'>YES</FONT>";
		else
			$inservice = "<FONT COLOR='BLUE'>NO</FONT>";
		echo "</td>";
		echo "<td class=\"listlr\">";
		if(stristr($status,"DOWN"))
			echo "<FONT COLOR='red'>DOWN</FONT>";
		else
			echo $status;
		echo "</td>";

		echo "<td class=\"listlr\">";
		echo $inservice;
		echo "</td>";

		echo "<td class=\"listlr\">";
		if(file_exists("/var/db/pingmsstatus/$ipaddress"))
			$msstatus = file_get_contents("/var/db/pingmsstatus/$ipaddress");
		else
			$msstatus = "N/A";
		echo $msstatus;
		echo "</td>";
		echo "</tr>";

	}
	echo "<tr><td>&nbsp;</td></tr>";
}
?>
      </table>
     </td>
    </tr>
</table>
</div>
<?php include("fend.inc"); ?>
<meta http-equiv="refresh" content="60;url=<?php print $_SERVER['SCRIPT_NAME']; ?>">
</body>
</html>
