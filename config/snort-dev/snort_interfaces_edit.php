<?php
/* $Id$ */
/*
	firewall_nat_edit.php
	part of m0n0wall (http://m0n0.ch/wall)

	Copyright (C) 2003-2004 Manuel Kasper <mk@neon1.net>.
	Copyright (C) 2003-2004 Robert Zelaya
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

if (!is_array($config['installedpackages']['snortglobal']['rule'])) {
	$config['installedpackages']['snortglobal']['rule'] = array();
}
//nat_rules_sort();
$a_nat = &$config['installedpackages']['snortglobal']['rule'];

$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

if (isset($_GET['dup'])) {
        $id = $_GET['dup'];
        $after = $_GET['dup'];
}

if (isset($id) && $a_nat[$id]) {
	$pconfig['proto'] = $a_nat[$id]['protocol'];
	list($pconfig['beginport'],$pconfig['endport']) = explode("-", $a_nat[$id]['external-port']);
	$pconfig['localip'] = $a_nat[$id]['target'];
	$pconfig['localbeginport'] = $a_nat[$id]['local-port'];
	$pconfig['descr'] = $a_nat[$id]['descr'];
	$pconfig['interface'] = $a_nat[$id]['interface'];
	$pconfig['block'] = isset($a_nat[$id]['block']);
	$pconfig['inline'] = isset($a_nat[$id]['inline']);
	if (!$pconfig['interface'])
		$pconfig['interface'] = "wan";
} else {
	$pconfig['interface'] = "wan";
}

if (isset($_GET['dup']))
	unset($id);

if ($_POST) {

	if ($_POST['beginport_cust'] && !$_POST['beginport'])
		$_POST['beginport'] = $_POST['beginport_cust'];
	if ($_POST['endport_cust'] && !$_POST['endport'])
		$_POST['endport'] = $_POST['endport_cust'];
	if ($_POST['localbeginport_cust'] && !$_POST['localbeginport'])
		$_POST['localbeginport'] = $_POST['localbeginport_cust'];

	if (!$_POST['endport'])
		$_POST['endport'] = $_POST['beginport'];
        /* Make beginning port end port if not defined and endport is */
        if (!$_POST['beginport'] && $_POST['endport'])
                $_POST['beginport'] = $_POST['endport'];

	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	if(strtoupper($_POST['proto']) == "TCP" or strtoupper($_POST['proto']) == "UDP" or strtoupper($_POST['proto']) == "TCP/UDP") {
		$reqdfields = explode(" ", "interface proto beginport endport localip localbeginport");
		$reqdfieldsn = explode(",", "Interface,Protocol,External port from,External port to,NAT IP,Local port");
	} else {
		$reqdfields = explode(" ", "interface proto localip");
		$reqdfieldsn = explode(",", "Interface,Protocol,NAT IP");
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

//	if (($_POST['localip'] && !is_ipaddroralias($_POST['localip']))) {
//		$input_errors[] = "\"{$_POST['localip']}\" is not valid subnet address.";
//	}

	/* only validate the ports if the protocol is TCP, UDP or TCP/UDP */
	if(strtoupper($_POST['proto']) == "TCP" or strtoupper($_POST['proto']) == "UDP" or strtoupper($_POST['proto']) == "TCP/UDP") {

		if (($_POST['beginport'] && !is_ipaddroralias($_POST['beginport']) && !is_port($_POST['beginport']))) {
			$input_errors[] = "The start port must be an integer between 1 and 65535.";
		}

		if (($_POST['endport'] && !is_ipaddroralias($_POST['endport']) && !is_port($_POST['endport']))) {
			$input_errors[] = "The end port must be an integer between 1 and 65535.";
		}

		if (($_POST['localbeginport'] && !is_ipaddroralias($_POST['localbeginport']) && !is_port($_POST['localbeginport']))) {
			$input_errors[] = "The local port must be an integer between 1 and 65535.";
		}

		if ($_POST['beginport'] > $_POST['endport']) {
			/* swap */
			$tmp = $_POST['endport'];
			$_POST['endport'] = $_POST['beginport'];
			$_POST['beginport'] = $tmp;
		}

		if (!$input_errors) {
			if (($_POST['endport'] - $_POST['beginport'] + $_POST['localbeginport']) > 65535)
				$input_errors[] = "The target port range must be an integer between 1 and 65535.";
		}

	}

	/* check for overlaps */
	foreach ($a_nat as $natent) {
		if (isset($id) && ($a_nat[$id]) && ($a_nat[$id] === $natent))
			continue;
		if ($natent['interface'] != $_POST['interface'])
			continue;
		if ($natent['external-address'] != $_POST['extaddr'])
			continue;
		if (($natent['proto'] != $_POST['proto']) && ($natent['proto'] != "tcp/udp") && ($_POST['proto'] != "tcp/udp"))
			continue;

		list($begp,$endp) = explode("-", $natent['external-port']);
		if (!$endp)
			$endp = $begp;

		if (!(   (($_POST['beginport'] < $begp) && ($_POST['endport'] < $begp))
		      || (($_POST['beginport'] > $endp) && ($_POST['endport'] > $endp)))) {

			$input_errors[] = "The external port range overlaps with an existing entry.";
			break;
		}
	}

	if (!$input_errors) {
		$natent = array();
		if ($_POST['extaddr'])
			$natent['external-address'] = $_POST['extaddr'];
		$natent['protocol'] = $_POST['proto'];

		if ($_POST['beginport'] == $_POST['endport'])
			$natent['external-port'] = $_POST['beginport'];
		else
			$natent['external-port'] = $_POST['beginport'] . "-" . $_POST['endport'];

		$natent['target'] = $_POST['localip'];
		$natent['local-port'] = $_POST['localbeginport'];
		$natent['interface'] = $_POST['interface'];
		$natent['descr'] = $_POST['descr'];

		if($_POST['block'] == "yes")
			$natent['block'] = true;
		else
			unset($natent['block']);
			
		if($_POST['inline'] == "yes")
			$natent['inline'] = true;
		else
			unset($natent['inline']);

		if (isset($id) && $a_nat[$id])
			$a_nat[$id] = $natent;
		else {
			if (is_numeric($after))
				array_splice($a_nat, $after+1, 0, array($natent));
			else
				$a_nat[] = $natent;
		}		

		write_config();

		header("Location: snort_interfaces.php");
		exit;
	}
}

$pgtitle = "Services: Snort Interfaces";
include("head.inc");

?>

<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
<?php
include("fbegin.inc"); ?>
<p class="pgtitle"><?=$pgtitle?></p>
<?php if ($input_errors) print_input_errors($input_errors); ?>
            <form action="snort_interfaces_edit.php" method="post" name="iform" id="iform">
 <tr><td>
<?php
	if($id != "") {
	
	 /* get the interface name */
		$first = 0;
        $snortInterfaces = array(); /* -gtm  */

        $if_list = $config['installedpackages']['snortglobal']['rule'][$id]['interface'];
        $if_array = split(',', $if_list);
        //print_r($if_array);
        if($if_array) {
                foreach($if_array as $iface2) {
                        $if2 = convert_friendly_interface_to_real_interface_name($iface2);

                        if($config['interfaces'][$iface2]['ipaddr'] == "pppoe") {
                                $if2 = "ng0";
                        }

                        /* build a list of user specified interfaces -gtm */
                        if($if2){
                          array_push($snortInterfaces, $if2);
                          $first = 1;
                        }
                }

                if (count($snortInterfaces) < 1) {
                        log_error("Snort will not start.  You must select an interface for it to listen on.");
                        return;
                }
        }
		foreach($snortInterfaces as $snortIf)

	$tab_array = array();
	$tab_array[] = array("Interfaces", false, "snort_interfaces.php");
	$tab_array[] = array("Settings", false, "/pkg_edit.php?xml=snort/snort_{$snortIf}/snort_{$snortIf}.xml&id=0");
	$tab_array[] = array("Categories", false, "snort/snort_{$snortIf}/snort_rulesets_{$snortIf}.php");
	$tab_array[] = array("Rules", false, "snort/snort_{$snortIf}/snort_rules_{$snortIf}.php");
	$tab_array[] = array("Servers", false, "/pkg_edit.php?xml=snort/snort_{$snortIf}/snort_define_servers_{$snortIf}.xml&amp;id=0");
	$tab_array[] = array("Threshold", false, "/pkg.php?xml=snort/snort_{$snortIf}/snort_threshold_{$snortIf}.xml");
	$tab_array[] = array("Barnyard2", false, "/pkg_edit.php?xml=snort/snort_{$snortIf}/snort_barnyard2_{$snortIf}.xml&id=0");
	display_top_tabs($tab_array);

	}
?>
 </td></tr>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
	  	<tr>
                  <td width="22%" valign="top" class="vncellreq">Interface</td>
                  <td width="78%" class="vtable">
					<select name="interface" class="formfld">
						<?php
						$interfaces = array('wan' => 'WAN', 'lan' => 'LAN');
						for ($i = 1; isset($config['interfaces']['opt' . $i]); $i++) {
							$interfaces['opt' . $i] = $config['interfaces']['opt' . $i]['descr'];
						}
						foreach ($interfaces as $iface => $ifacename): ?>
						<option value="<?=$iface;?>" <?php if ($iface == $pconfig['interface']) echo "selected"; ?>>
						<?=htmlspecialchars($ifacename);?>
						</option>
						<?php endforeach; ?>
					</select><br>
                     <span class="vexpl">Choose which interface this rule applies to.<br>
                     Hint: in most cases, you'll want to use WAN here.</span></td>
                </tr>
				<tr>
					<td width="22%" valign="top" class="vncellreq">Block all offenders</td>
					<td width="78%" class="vtable">
						<input type="checkbox" value="yes" name="block"<?php if($pconfig['block']) echo " CHECKED"; ?>><br>
						HINT: Block all offenders that trigger an alert on the selected interface.
					</td>
				</tr>
				<tr>
					<td width="22%" valign="top" class="vncellreq">Enable Inline Mode</td>
					<td width="78%" class="vtable">
						<input type="checkbox" value="yes" name="inline"<?php if($pconfig['inline']) echo " CHECKED"; ?>><br>
						HINT: This will enable Snort Inline mode on the selected interafce.
					</td>
				</tr>
                <tr>
                  <td width="22%" valign="top" class="vncellreq">Inline listening port </td>
                  <td width="78%" class="vtable">
                    <select name="localbeginport" class="formfld" onChange="ext_change();check_for_aliases();">
                      <option value="">(other)</option>
                      <?php $bfound = 0; foreach ($wkports as $wkport => $wkportdesc): ?>
                      <?php endforeach; ?>
                    </select> <input onChange="check_for_aliases();" autocomplete='off' class="formfldalias" name="localbeginport_cust" id="localbeginport_cust" type="text" size="5" value="<?php if (!$bfound) echo $pconfig['localbeginport']; ?>">
                    <br>
                    <span class="vexpl">Specify the port Snort Inline should lissten on.<br>
                    Hint: Never enter a port that is already being used by the system.</span></td>
                </tr>
                <tr>
                  <td width="22%" valign="top" class="vncellreq">Inline Divert Protocol</td>
                  <td width="78%" class="vtable">
                    <select name="proto" class="formfld" onChange="proto_change(); check_for_aliases();">
                      <?php $protocols = explode(" ", "TCP UDP TCP/UDP GRE ESP All"); foreach ($protocols as $proto): ?>
                      <option value="<?=strtolower($proto);?>" <?php if (strtolower($proto) == $pconfig['proto']) echo "selected"; ?>><?=htmlspecialchars($proto);?></option>
                      <?php endforeach; ?>
                    </select> <br> <span class="vexpl">Choose which IP protocol Snort Inline should divert.<br>
                    Hint: in most cases, you should specify <em>All</em> &nbsp;here.</span></td>
                </tr>
                <tr>
                  <td width="22%" valign="top" class="vncellreq">Inline Divert External port range </td>
                  <td width="78%" class="vtable">
                    <table border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td>from:&nbsp;&nbsp;</td>
                        <td><select name="beginport" class="formfld" onChange="ext_rep_change(); ext_change(); check_for_aliases();">
                            <option value="">(other)</option>
                            <?php $bfound = 0; foreach ($wkports as $wkport => $wkportdesc): ?>
                            <option value="<?=$wkport;?>" <?php if ($wkport == $pconfig['beginport']) {
								echo "selected";
								$bfound = 1;
							}?>>
							<?=htmlspecialchars($wkportdesc);?>
							</option>
                            <?php endforeach; ?>
                          </select> <input onChange="check_for_aliases();" autocomplete='off' class="formfldalias" name="beginport_cust" id="beginport_cust" type="text" size="5" value="<?php if (!$bfound) echo $pconfig['beginport']; ?>"></td>
                      </tr>
                      <tr>
                        <td>to:</td>
                        <td><select name="endport" class="formfld" onChange="ext_change(); check_for_aliases();">
                            <option value="">(other)</option>
                            <?php $bfound = 0; foreach ($wkports as $wkport => $wkportdesc): ?>
                            <option value="<?=$wkport;?>" <?php if ($wkport == $pconfig['endport']) {
								echo "selected";
								$bfound = 1;
							}?>>
							<?=htmlspecialchars($wkportdesc);?>
							</option>
							<?php endforeach; ?>
                          </select> <input onChange="check_for_aliases();" class="formfldalias" autocomplete='off' name="endport_cust" id="endport_cust" type="text" size="5" value="<?php if (!$bfound) echo $pconfig['endport']; ?>"></td>
                      </tr>
                    </table>
                    <br> <span class="vexpl">Specify the port or port range Snort Inline should divert on the firewall's external address.<br>
                    Hint: you can leave the <em>'to'</em> field empty if you only want to divert a single port<br>
					Hint: you can leave from and to empty to divert all ports.</span></td>
                </tr>
                <tr>
                  <td width="22%" valign="top" class="vncellreq">Inline IP Subnet</td>
                  <td width="78%" class="vtable">
                    <input autocomplete='off' name="localip" type="text" class="formfldalias" id="localip" size="20" value="<?=htmlspecialchars($pconfig['localip']);?>">
                    <br> <span class="vexpl">Enter the internal IP subnet address you wish to sniff. Leave blank for all.<br>
                    e.g. <em>192.168.1.0/24</em></span></td>
                </tr>
                <tr>
                  <td width="22%" valign="top" class="vncell">Description</td>
                  <td width="78%" class="vtable">
                    <input name="descr" type="text" class="formfld" id="descr" size="40" value="<?=htmlspecialchars($pconfig['descr']);?>">
                    <br> <span class="vexpl">You may enter a description here
                    for your reference (not parsed).</span></td>
                </tr>
                <?php if ((!(isset($id) && $a_nat[$id])) || (isset($_GET['dup']))): ?>
				<?php endif; ?>
                <tr>
                  <td width="22%" valign="top">&nbsp;</td>
                  <td width="78%">
                    <input name="Submit" type="submit" class="formbtn" value="Save"> <input type="button" class="formbtn" value="Cancel" onclick="history.back()">
                    <?php if (isset($id) && $a_nat[$id]): ?>
                    <input name="id" type="hidden" value="<?=$id;?>">
                    <?php endif; ?>
                  </td>
                </tr>
              </table>
</form>
<script language="JavaScript">
<!--
	ext_change();
//-->
</script>
<?php
$isfirst = 0;
$aliases = "";
$addrisfirst = 0;
$aliasesaddr = "";
if($config['aliases']['alias'] <> "")
	foreach($config['aliases']['alias'] as $alias_name) {
		if(!stristr($alias_name['address'], ".")) {
			if($isfirst == 1) $aliases .= ",";
			$aliases .= "'" . $alias_name['name'] . "'";
			$isfirst = 1;
		} else {
			if($addrisfirst == 1) $aliasesaddr .= ",";
			$aliasesaddr .= "'" . $alias_name['name'] . "'";
			$addrisfirst = 1;
		}
	}
?>
<script language="JavaScript">
<!--
	var addressarray=new Array(<?php echo $aliasesaddr; ?>);
	var customarray=new Array(<?php echo $aliases; ?>);
//-->
</script>
<?php include("fend.inc"); ?>
</body>
</html>
