<?php
/* $Id$ */
/*
 snort_interfaces.php
 part of m0n0wall (http://m0n0.ch/wall)

 Copyright (C) 2003-2004 Manuel Kasper <mk@neon1.net>.
 Copyright (C) 2008-2009 Robert Zelaya.
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

require_once("guiconfig.inc");
require_once("/usr/local/pkg/snort/snort_new.inc");
require_once("/usr/local/pkg/snort/snort_gui.inc");

// set page vars

if (isset($_GET['uuid']) && isset($_GET['rdbuuid'])) {
	echo 'Error: more than one uuid';
	exit(0);
}

// set page vars
if (isset($_GET['uuid'])) {
	$uuid = $_GET['uuid'];
}

if (isset($_GET['rdbuuid'])) {
	$rdbuuid = $_GET['rdbuuid'];
}else{
	$ruledbname_pre1 = snortSql_fetchAllSettings('snortDB', 'SnortIfaces', 'uuid', $uuid);
	$rdbuuid = $ruledbname_pre1['ruledbname'];
}

if (empty($rdbuuid)) {
	echo 'ERROR: Missing RDBUUID';
	exit;
}

if (isset($_GET['rulefilename'])) {
	$rulefilename = $_GET['rulefilename'];
}else{
	echo 'ERROR: Missing rulefilename';
	exit;
}

	$pgtitle = "Services: Snort: Ruleset Ips:";
	include("/usr/local/pkg/snort/snort_head.inc");

?>

<body link="#0000CC" vlink="#0000CC" alink="#0000CC">

<div id="loadingWaiting">
  <p class="loadingWaitingMessage"><img src="./images/loading.gif" /> <br>Please Wait...</p>
</div>

<?php include("fbegin.inc"); ?>
<!-- hack to fix the hardcoed fbegin link in header -->
<div id="header-left2">
<a href="../index.php" id="status-link2">
<img src="./images/transparent.gif" border="0"></img>
</a>
</div>

<div class="body2"><!-- hack to fix the hardcoed fbegin link in header -->
<div id="header-left2"><a href="../index.php" id="status-link2"><img src="./images/transparent.gif" border="0"></img></a></div>

<form id="iform" >

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php
	if (!empty($uuid)) { 
		echo '
		<tr>
			<td>
			<div class="newtabmenu" style="margin: 1px 0px; width: 775px;"><!-- Tabbed bar code-->
			<ul class="newtabmenu">
					<li><a href="/snort/snort_interfaces.php"><span>Snort Interfaces</span></a></li>
					<li><a href="/snort/snort_interfaces_edit.php?uuid=' . $uuid . '"><span>If Settings</span></a></li>
					<li><a href="/snort/snort_rulesets.php?uuid=' . $uuid . '"><span>Categories</span></a></li>
					<li><a href="/snort/snort_rules.php?uuid=' . $uuid . '"><span>Rules</span></a></li>
					<li class="newtabmenu_active"><a href="/snort/snort_rulesets_ips.php?uuid=' . $uuid . '"><span>Ruleset Ips</span></a></li>
					<li><a href="/snort/snort_define_servers.php?uuid=' . $uuid . '"><span>Servers</span></a></li>
					<li><a href="/snort/snort_preprocessors.php?uuid=' . $uuid . '"><span>Preprocessors</span></a></li>
					<li><a href="/snort/snort_barnyard.php?uuid=' . $uuid . '"><span>Barnyard2</span></a></li>			
			</ul>
			</div>
			</td>
		</tr>
		';
	}else{
		echo ' 
		<tr>
			<td>
			<div class="newtabmenu" style="margin: 1px 0px; width: 775px;"><!-- Tabbed bar code-->
			<ul class="newtabmenu">
				<li><a href="/snort/snort_interfaces.php"><span>Snort Interfaces</span></a></li>
				<li><a href="/snort/snort_interfaces_global.php"><span>Global Settings</span></a></li>
				<li><a href="/snort/snort_download_updates.php"><span>Updates</span></a></li>
				<li class="newtabmenu_active"><a href="/snort/snort_interfaces_rules.php"><span>RulesDB</span></a></li>
				<li><a href="/snort/snort_alerts.php"><span>Alerts</span></a></li>
				<li><a href="/snort/snort_blocked.php"><span>Blocked</span></a></li>
				<li><a href="/snort/snort_interfaces_whitelist.php"><span>Whitelists</span></a></li>
				<li><a href="/snort/snort_interfaces_suppress.php"><span>Suppress</span></a></li>
				<li><a href="/snort/snort_help_info.php"><span>Help</span></a></li>
			</ul>
			</div>
			</td>
		</tr>
		<tr>
			<td>
			<div class="newtabmenu" style="margin: 1px 0px; width: 775px;"><!-- Tabbed bar code-->
			<ul class="newtabmenu">
			<li><a href="/snort/snort_interfaces_rules_edit.php?rdbuuid=' . $rdbuuid . '"><span>Rules DB Edit</span></a></li>
			<li><a href="/snort/snort_rulesets.php?rdbuuid=' . $rdbuuid . '"><span>Categories</span></a></li>
			<li><a href="/snort/snort_rules.php?rdbuuid=' . $rdbuuid . '"><span>Rules</span></a></li>
			<li class="newtabmenu_active"><a href="/snort/snort_rulesets_ips.php?rdbuuid=' . $rdbuuid . '"><span>Ruleset Ips</span></a></li>
			</ul>
			</div>
			</td>
		</tr>	
			';
	}
	?>
	<tr>
		<td id="tdbggrey">		
		<table width="100%" border="0" cellpadding="10px" cellspacing="0">
		<tr>
		<td class="tabnavtbl">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<!-- START MAIN AREA -->

<table width="100%" border="0" cellpadding="10px" cellspacing="0">	
		<input type="hidden" name="snortSamSaveSettings" value="1" /> <!-- what to do, save -->
		<input type="hidden" name="dbName" value="snortDBrules" /> <!-- what db-->
		<input type="hidden" name="dbTable" value="SnortruleSigsIps" /> <!-- what db table-->
		<input type="hidden" name="ifaceTab" value="snort_ruleset_ips" /> <!-- what interface tab -->

	<tr id="frheader" >
		<td width="1%" class="listhdrr2">&nbsp;&nbsp;&nbsp;On</td>
		<td width="1%" class="listhdrr2">&nbsp;&nbsp;&nbsp;Sid</td>
		<td width="1%" class="listhdrr2">&nbsp;&nbsp;&nbsp;Source</td>
		<td width="1%" class="listhdrr2">&nbsp;&nbsp;&nbsp;Amount</td>
		<td width="1%" class="listhdrr2">&nbsp;&nbsp;&nbsp;Duration</td>
		<td width="20%" class="listhdrr2">Message</td>												
	</tr>
	
	<tbody class="rulesetloopblock">
	
	</tbody>
	
</table>
<br>
<table>
<tr>
	<td>
		<input name="Submit" type="submit" class="formbtn" value="Save">
		<input id="cancel" type="button" class="formbtn" value="Cancel">
	</td>	
</tr>
</table>

		</form >
		<!-- STOP MAIN AREA -->
		</table>
		</td>
		</tr>			
		</table>
	</td>
	</tr>
</table>
</div>

<script type="text/javascript">

//prepare the form when the DOM is ready 
jQuery(document).ready(function() {


	
<?php 

	/*
	 * Builds Json long string from a snort rules file
	 * Options: $rdbuuid, $rulefilename
	 * Used in Ips Tab
	 */
	function createSidTmpBlockSpit($rdbuuid, $rulefilename) 
	{
	
		function getCurrentIpsRuleArray($output)
		{
			
			foreach (array_unique($output) as $line)
			{
				$newOutput = explode(' # ', $line);
				$newLine[] = $newOutput;
			}
			
			return $newLine;	
		}
		
		function getSidBlockJsonArray($getEnableSid)
		{
				
			if (!empty($getEnableSid)) {
					
					$i = 0;	
												
							$countSigList = count($getEnableSid);
							foreach ($getEnableSid as $val3)
							{
								
								$i++;
		
								if ($i == 1) {
									$main .= '[';
								}
								
								if ( $i == $countSigList ) {		 
									$main .= '{"sid":"' . escapeJsonString($val3['0']) . '","enable":"' . 'on' . '","who":"' . 'src' . '","timeamount":"' . '15' . '","timetype":"' . 'minutes' . '","msg":"' . escapeJsonString($val3['1']) . '"}'; 
								}else{
									$main .= '{"sid":"' . escapeJsonString($val3['0']) . '","enable":"' . 'on' . '","who":"' . 'src' . '","timeamount":"' . '15' . '","timetype":"' . 'minutes' . '","msg":"' . escapeJsonString($val3['1']) . '"},';  
								}
								
								if ($i == $countSigList) {
									$main .= ']';
								}
								
							} // END foreach
		
				return $main;		
							
			} // END of jSON build
		
			return false;
			
		}
			if (!file_exists('/usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/dbBlockSplit')) {
				exec('mkdir /usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/dbBlockSplit');
			}
			
			exec('rm /usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/dbBlockSplit/*.rules');
			exec('cp /usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/rules/' . $rulefilename . ' ' . '/usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/dbBlockSplit/' . $rulefilename);
		
			//$getEnableSidArray = '';
			exec('perl /usr/local/bin/make_snortsam_map.pl /usr/local/etc/snort/snortDBrules/DB/' . $rdbuuid . '/dbBlockSplit/', $getEnableSidArray);
			
			return getSidBlockJsonArray(getCurrentIpsRuleArray($getEnableSidArray));
		
	} // END of build json rule func
	
	// Build table from json, anf $_GET
	$getJsonRulefile = createSidTmpBlockSpit($rdbuuid, $rulefilename);
	
	if (!empty($getJsonRulefile)) {
		echo 'var getLogJsonRuleFile = ' . $getJsonRulefile . ';';
	}

?>

// create option list through js
function createDropdownOptionList(list, opselected) {

	var strOut = '';
	var selectedOptionON = '';
	for (var key in list) {
		
		if (opselected.toUpperCase() == list[key]) {
			selectedOptionON = 'selected="selected"';
		}
		
		strOut = strOut + '<option value="' + list[key] + '" ' + selectedOptionON + '>' + list[key] + '</option>' + "\n";
		selectedOptionON = '';
	}
	return strOut;
}

function makeLargeSidTables(snortObjlist) {

	//disable Row Append if row count is less than 0
	var countRowAppend = snortObjlist.length;
	
	var timeValuePerfList = {"src":"SRC", "dst":"DST", "both":"BOTH"};
	var timeTypePerfList = {"minutes":"MINUTES", "seconds":"SECONDS", "hours":"HOURS", "days":"DAYS", "weeks":"WEEKS", "months":"MONTHS", "ALWAYS":"ALWAYS"};
	
	// if rowcount is not empty do this
	if (countRowAppend > 0){
	
		// Break up append row adds by chunks of 300
		// NOTE: ie9 is still giving me issues on deleted.rules 6000 sigs. I should break up the json code above into smaller parts.
		incrementallyProcess(function (i){
	
			if (isEven(i) === true){
				var rowIsEvenOdd = 'odd_ruleset2';
			}else{ 
				var rowIsEvenOdd = 'even_ruleset2';
			}
			
			if (snortObjlist[i].enable === 'on'){
				var rulesetChecked = 'checked'; 
			}else{
				var rulesetChecked = '';
			}
		
			jQuery('.rulesetloopblock').append(	
					"\n" + '<tr class="hidemetr" id="ipstable_' + snortObjlist[i].sid + '" valign="top">' + "\n" +
					'<td class="' + rowIsEvenOdd + '">' + "\n" +
						'<input class="domecheck" id="checkbox_' + snortObjlist[i].sid + '" name="snortsam[db][' + snortObjlist[i].sid + '][enable]" value="' + snortObjlist[i].enable + '" checked="' + rulesetChecked + '" type="checkbox">' + "\n" +
					'</td>' + "\n" +
					'<td class="' + rowIsEvenOdd + '" id="sid_' + snortObjlist[i].sid + '" >' + snortObjlist[i].sid + '</td>' + "\n" +
					'<td class="' + rowIsEvenOdd + '">' + "\n" +
						'<select class="formfld2" id="who_' + snortObjlist[i].sid + '" name="snortsam[db][' + snortObjlist[i].sid + '][who]">' + "\n" +
						createDropdownOptionList(timeValuePerfList, snortObjlist[i].who) +						
						'</select>' + "\n" +
					'</td>' + "\n" +
					'<td class="' + rowIsEvenOdd + '">' + "\n" +
						'<input class="formfld2" id="timeamount_' + snortObjlist[i].sid + '" name="snortsam[db][' + snortObjlist[i].sid + '][timeamount]" type="text" size="7" value="' + snortObjlist[i].timeamount + '">' + "\n" +
					'</td>' + "\n" +
						'<td class="' + rowIsEvenOdd + '">' + "\n" +
						'<select class="formfld2" id="timetype_' + snortObjlist[i].sid + '" name="snortsam[db][' + snortObjlist[i].sid + '][timetype]" >' + "\n" +
						createDropdownOptionList(timeTypePerfList, snortObjlist[i].timetype) +
						'</select>' + "\n" +
					'</td>' + "\n" +
					'<td class="listbg" id="msg_' + snortObjlist[i].sid + '"><font color="white">' + snortObjlist[i].msg + '</font></td>' + "\n" +
				'</tr>' + "\n"					
			);
		  
		}, 
		snortObjlist,  // Object to work with the case Json object
		300, // chunk size
		25, // how many secs to wait
		function (){
		
			hideLoading('#loadingWaiting');
		
		}); // end incrament
	} // end of if stopRowAppend
	
}; // END make table func

	
			// Build table call
			function startTableBuild() {

				showLoading('#loadingWaiting');	
				lastTableBuild();
			}
			function lastTableBuild() {
			
				makeLargeSidTables(getLogJsonRuleFile);
				
			}

			<?php
			if (!empty($getJsonRulefile)) {
				echo 'startTableBuild();';
			}
			?>
			
}); // end of document ready




</script>


<!-- footer do not touch below -->
<?php 
include("fend.inc"); 
echo $snort_custom_rnd_box;
?>


</body>
</html>
