<?php
/*
	postfix_search.php
	part of pfSense (http://www.pfsense.com/)
	Copyright (C) 2011 Marcello Coutinho <marcellocoutinho@gmail.com>
	based on varnish_view_config.
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

$pfSversion = str_replace("\n", "", file_get_contents("/etc/version"));
if(strstr($pfSversion, "1.2"))
	$one_two = true;

$pgtitle = "Diagnostics: Search Mail";
include("head.inc");

?>
<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
<?php include("fbegin.inc"); ?>

<?php if($one_two): ?>
<p class="pgtitle"><?=$pgtitle?></font></p>
<?php endif; ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>

<!-- <form action="postfix_view_config.php" method="post"> -->
	
<div id="mainlevel">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr><td>
		<?php
	$tab_array = array();
	$tab_array[] = array(gettext("General"), false, "/pkg_edit.php?xml=postfix.xml&id=0");
	$tab_array[] = array(gettext("ACLs / Filter Maps"), false, "/pkg_edit.php?xml=postfix_acl.xml&id=0");
	$tab_array[] = array(gettext("Valid Recipients"), false, "/pkg_edit.php?xml=postfix_recipients.xml&id=0");
	$tab_array[] = array(gettext("Antispam"), false, "/pkg_edit.php?xml=postfix_antispam.xml&id=0");
	$tab_array[] = array(gettext("XMLRPC Sync"), false, "/pkg_edit.php?xml=postfix_sync.xml&id=0");
	$tab_array[] = array(gettext("View config files"), false, "/postfix_view_config.php");
	$tab_array[] = array(gettext("Search Email"), true, "/postfix_search.php");
	display_top_tabs($tab_array);
?>
		</td></tr>
 		<tr>
 		
    		<td>
				<div id="mainarea">
					<table class="tabcont" width="100%" border="0" cellpadding="8" cellspacing="0">
					<tr><td></td></tr>
						<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gettext("Search options"); ?></td>
						</tr>
						<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("From: ");?></td>
                        <td width="78%" class="vtable"><textarea id="from" rows="2" cols="50%"></textarea>
                          <br><?=gettext("One email per line.");?></td>
                        </tr>
						<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("To: ");?></td>
                        <td width="78%" class="vtable"><textarea id="to" rows="2" cols="50%"></textarea>
                          <br><?=gettext("One email per line.");?></td>
					</tr>
					<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("SID: ");?></td>
                        <td width="78%" class="vtable"><textarea id="sid" rows="2" cols="20%"></textarea>
                          <br><?=gettext("Postfix queue file unique id. One per line.");?></td>
					</tr>
					<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Subject: ");?></td>
                        <td width="78%" class="vtable"><input type="text" class="formfld unknown" id="subject" size="65%">
                          <br><?=gettext("");?></td>
					</tr>
					<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Message_id: ");?></td>
                        <td width="78%" class="vtable"><input type="text" class="formfld unknown" id="msgid" size="65%">
                          <br><?=gettext("Message unique id.");?></td>
				</tr>
				<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Message Status: ");?></td>
                        <td width="78%" class="vtable">
                        <select name="drop3" id="status">
                        	<option value="" selected="selected">any</option>
                        	<option value="sent">sent</option>
							<option value="bounced">bounced</option>
							<option value="reject">reject</option>
							<option value="warning">warning</option>
						</select><br><?=gettext("Max log messages to fetch per Sqlite file.");?></td>
					</tr>
				<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Log type: ");?></td>
                        <td width="78%" class="vtable">
                        <select name="drop2" id="queuetype">
                        	<option value="NOQUEUE" selected="selected">NOQUEUE</option>
							<option value="QUEUE">QUEUE</option>
						</select><br><?=gettext("NOQUEUE logs means messages that where rejected in smtp negotiation.");?></td>
					</tr>
				<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Query Limit: ");?></td>
                        <td width="78%" class="vtable">
                        <select name="drop3" id="queuemax">
                        	<option value="50" selected="selected">50</option>
							<option value="150">150</option>
							<option value="250">250</option>
							<option value="250">500</option>
							<option value="250">1000</option>
							<option value="250">Unlimited</option>
						</select><br><?=gettext("Max log messages to fetch per Sqlite file.");?></td>
					</tr>
						<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Sqlite files: ");?></td>
                        <td width="78%" class="vtable">
                        	
                        	<?php if ($handle = opendir('/var/db/postfix')) {
                        		$total_files=0;
                        				while (false !== ($file = readdir($handle)))
                        				 if (preg_match("/(\d+-\d+-\d+).db$/",$file,$matches)){
                        				 		$total_files++;
												$select_output= '<option value="'.$file.'">'.$matches[1]."</option>\n" . $select_output;
											}
                        			closedir($handle);
                        			echo '<select name="drop1" id="Select1" size="'.($total_files>8?8:$total_files+2).'" multiple="multiple">';
                        			echo $select_output;
                        			echo '</select><br>'.gettext("Select what database files you want to use in your search.").'</td></td>';
                        	                        			}?>	
							</tr>
					<tr>
                        <td width="22%" valign="top" class="vncell"><?=gettext("Message Fields: ");?></td>
                        <td width="78%" class="vtable">
                        <select name="drop3" id="fields" size="13" multiple="multiple">
                        	<option value="date"   selected="selected">Date</option>
                        	<option value="from"   selected="selected">From</option>
                        	<option value="to" 	   selected="selected">To</option>
                        	<option value="delay" selected="selected">Delay</option>
                        	<option value="status" selected="selected">Status</option>
                        	<option value="status_info">Status Info</option>
                        	<option value="subject">Subject</option>
							<option value="size">Size</option>
							<option value="sid">SID</option>
							<option value="msgid">msgid</option>
							<option value="bounce">bounce</option>
							<option value="relay">Relay</option>
							<option value="helo">Helo</option>
						</select><br><?=gettext("Max log messages to fetch per Sqlite file.");?></td>
					</tr>
					
							<tr>
							<td width="22%" valign="top"></td>
                        <td width="78%"><input name="Submit" type="submit" class="formbtn" value="<?=gettext("Search");?>" onclick="getsearch_results(true)">
						</table>
						
				</div>
			</td>
		</tr>
	

	</table>
	<br>
	<div id="search_results"></div>
</div>
<script type="text/javascript">
function loopSelected(id)
{
  var selectedArray = new Array();
  var selObj = document.getElementById(id);
  var i;
  var count = 0;
  for (i=0; i<selObj.options.length; i++) {
    if (selObj.options[i].selected) {
      selectedArray[count] = selObj.options[i].value;
      count++;
    }
  }
  return(selectedArray);
}

function getsearch_results() {
		scroll(0,0);
		var $new_from=$('from').value.replace("\n", "','");
		var $new_to=$('to').value.replace("\n", "','");
		var $new_sid=$('sid').value.replace("\n", "','");
		var $files=loopSelected('Select1');
		var $fields=loopSelected('fields');
		var $queuetype=$('queuetype').options[$('queuetype').selectedIndex].text;
		var $queuemax=$('queuemax').options[$('queuemax').selectedIndex].text;
		var $pars="from="+$new_from+"&to="+$new_to+"&sid="+$new_sid+"&limit="+$queuemax+"&fields="+$fields+"&status="+$('status').value;
		var $pars= $pars+"&subject="+$('subject').value+"&msgid="+$('msgid').value+"&files="+$files+"&queue="+$queuetype;
		//alert($pars);
		var url = "/postfix.php";
		var myAjax = new Ajax.Request(
			url,
			{
				method: 'post',
				parameters: $pars,
				onComplete: activitycallback_postfix_search
			});
		}
	function activitycallback_postfix_search(transport) {
		$('search_results').innerHTML = transport.responseText;
	}
</script>
<!-- </form> -->
<?php include("fend.inc"); ?>
</body>
</html>
