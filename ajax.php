<?php
	//Author: Daniel Raquel (draquel@webjynx.com)
	include("dbobj.php");
	session_start();
	
	if(isset($_REQUEST['ari']) || $_REQUEST['ari'] != NULL || $_REQUEST['ari'] != ""){
		switch($_REQUEST['ari']){
			case 0: //DB Select/Update
				if(isset($_POST['db'])){$_SESSION['db']['Active'] = mysql_escape_string($_POST['db']);}
				$res_cont = mysql_query("SELECT * FROM `Contact_Data`",$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
				$_SESSION['contacts'] = new DLList();
				while($row = mysql_fetch_array($res_cont)){
					$c = new Contact_BG();
					$c->initMysql($row);
					$c->setCodes($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					$c->setCommittees($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					$_SESSION['contacts']->insertLast($c);
				}
				
				$res_dona = mysql_query("SELECT * FROM `Donations`",$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
				$_SESSION['donations'] = new DLList();
				while($row = mysql_fetch_array($res_dona)){ 
					$d = new Donation();
					$d->initMysql($row);
					$_SESSION['donations']->insertLast($d);
				}
				
				$res_sett = mysql_query("SELECT * FROM `Settings`",$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
				$_SESSION['settings'] = array();
				$_SESSION['settings']['List'] = new DLList();
				while($row = mysql_fetch_array($res_sett)){
					$s = new Setting();
					$s->initMysql($row);
					$_SESSION['settings']['List']->insertLast($s);
					$_SESSION['settings'][$row['Title']] = $row['Value'];
				}
				echo 1;
			break;
			case 1: //Check for Unique Contact Info
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$type = mysql_escape_string($_POST['type']);
					$sstr = mysql_escape_string($_POST['istr']);
					$id = mysql_escape_string($_POST['id']);
					
					switch($type){
						case "em":
							$sql = "SELECT * FROM Emails WHERE Address = \"".$sstr."\"";
							if($id != '0'){ $sql .= " AND ID != \"".$id."\""; }
							$res = mysql_query($sql,$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
							if(mysql_num_rows($res) == 0){ echo 1; }else{ echo 2; }
						break;
						case "ph":
						
						break;
						case "ad":
						
						break;
					}
				}
			break;
			case 2: //Contact_BG Edit Form
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$id = mysql_escape_string($_POST['i']);
					if($id != 0){
						$c = $_SESSION['contacts']->getFirstNode();
						while($c != NULL){
							$a = $c->readNode()->toArray();
							if($a['ID'] == $id){ $cont = $a; break;}
							$c = $c->getNext();
						}
				
						$out = "<input name=\"ID\" type=\"hidden\" value=\"".$cont['ID']."\" ><input name=\"Created\" type=\"hidden\" value=\"".$cont['Created']."\"><input name=\"Updated\" type=\"hidden\" value=\"".$cont['Updated']."\">
						<fieldset class=\"fs_contact\">
						<legend>Contact</legend>
						<table>
						<tr class=\"formField formSetHead\"><td>First</td><td>Last</td><td>Birthday</td></tr>
						<tr class=\"formField\"><td><input type=\"text\" name=\"First\" value=\"". $cont['First'] ."\" ><span id=\"first_err\" class=\"err_msg\"></span></td><td><input type=\"text\" name=\"Last\" value=\"". $cont['Last'] ."\" ><span id=\"last_err\" class=\"err_msg\"></span></td><td><input type=\"date\" name=\"BDay\" value=\"".$cont['Bday']."\" ></td></td></tr>
						<tr class=\"formField formSetHead\"><td>Company</td><td>Title</td></tr>
						<tr class=\"formField\"><td><input type=\"text\" name=\"Company\" value=\"". $cont['Company'] ."\" ><span id=\"comp_err\" class=\"err_msg\"></span></td><td><input type=\"text\" name=\"Title\" value=\"". $cont['Title'] ."\"     ></td></tr>
						<tr class=\"formField formSetHead\"><td>State Organization</td><td>Title</td></tr>
						<tr class=\"formField\"><td><input type=\"text\" name=\"State_Org\" value=\"". $cont['State_Org'] ."\" ><span id=\"storg_err\" class=\"err_msg\"></span></td><td><input type=\"text\" name=\"State_Title\" value=\"". $cont['State_Title'] ."\" ></td></tr>
						</table>
						</fieldset><fieldset id=\"fs_Relations\">
						<legend>Contact Groups:</legend>
						<table>
						<tr class=\"formField formSetHead\"><td>Codes</td><td>Committees</td></tr>
						<tr><td><select name=\"Codes\" multiple>";
						for($i = 0; $i < count($_SESSION['code_arr']); $i += 1){
							$hasCode = False;
							for($j = 0; $j < count($cont['Rels']['Codes']); $j += 1){ 
								if($cont['Rels']['Codes'][$j]['KID'] == $_SESSION['code_arr'][$i]['ID']){ $hasCode = True; break; }
							}
							if($hasCode){ $out .= "<option value=\"".$cont['Rels']['Codes'][$j]['ID']."|".$cont['Rels']['Codes'][$j]['Created']."|".$cont['Rels']['Codes'][$j]['Updated']."|". $_SESSION['code_arr'][$i]['ID']. "\" selected>"; }
							else{ $out .= "<option value=\"0|0|0|". $_SESSION['code_arr'][$i]['ID']. "\">";  }
							$out .= $_SESSION['code_arr'][$i]['Code']. " - ". $_SESSION['code_arr'][$i]['Definition']. "</option>";
						}
						$out .= "</select></td><td><select name=\"Committees\" multiple>";
						for($i = 0; $i < count($_SESSION['comm_arr']); $i += 1){
							$hasComm = False;
							for($j = 0; $j < count($cont['Rels']['Committees']); $j += 1){ 
								if($cont['Rels']['Committees'][$j]['KID'] == $_SESSION['comm_arr'][$i]['ID']){ $hasComm = True; break; }
							}
							if($hasComm){ $out .= "<option value=\"".$cont['Rels']['Committees'][$j]['ID']."|".$cont['Rels']['Committees'][$j]['Created']."|".$cont['Rels']['Committees'][$j]['Updated']."|". $_SESSION['comm_arr'][$i]['ID']. "\" selected>";  }
							else{ $out .= "<option value=\"0|0|0|". $_SESSION['comm_arr'][$i]['ID']. "\">";  }
							$out .= $_SESSION['comm_arr'][$i]['Code']. " - ". $_SESSION['comm_arr'][$i]['Definition']. "</option>";
						}
						$out .= "</select></td></tr>
						</table>
						</fieldset><fieldset class=\"fs_emails\">
						<legend>Emails:</legend>
						<table>
						<tr class=\"formField formSetHead\"><td></td><td>Name</td><td>Address</td><td>Primary</td><td>Remove</td></tr>";
						$e = $c->readNode()->getEmails()->getFirstNode();
						while($e != NULL){
							$em = $e->readNode()->toArray();
							$out .= "<tr class=\"formField\"><td><img class=\"err_icon\" title=\"\" src=\"img/error.png\" ></td><td><input name=\"ID\" type=\"hidden\" value=\"".$em['ID']."\" ><input name=\"Created\" type=\"hidden\" value=\"".$em['Created']."\" ><input name=\"Updated\" type=\"hidden\" value=\"".$em['Updated']."\" ><input type=\"text\" name=\"Name\" value=\"".$em['Name']."\" ></td><td><input type=\"text\" name=\"Address\" value=\"".$em['Address']."\" onBlur=\"uniqueCIC(this,'em','".$em['ID']."',this.value)\"></td><td><input class=\"Primary\" type=\"checkbox\" name=\"Primary\" onclick=\"checkPrimary(this)\"";
							if($em['Primary'] == 1){ $out .= " checked"; }
							$out .= "></td><td ><img class=\"rem_icon\" src=\"img/delete.png\" name=\"Delete\" onclick=\"deleteRow(this)\" ></td></tr>\n";
							$e = $e->getNext();
						}
						$out .= "</table>
						<span id=\"emails_err\"class=\"fs_err\"></span><div class=\"cc_add\" id=\"New_Email\" onclick=\"addEmail('editContact')\">+ Add Email</div>
						</fieldset><fieldset class=\"fs_phones\">
						<legend>Phones:</legend>
						<table>
						<tr class=\"formField formSetHead\"><td></td><td>Name</td><td>Region</td><td>Area</td><td>Number</td><td>Ext</td><td>Primary</td><td>Remove</td></tr>";
						$p = $c->readNode()->getPhones()->getFirstNode();
						while($p != NULL){
							$ph = $p->readNode()->toArray();
							$out .= "<tr class=\"formField\"><td><img class=\"err_icon\" title=\"\" src=\"img/error.png\" ></td><td><input name=\"ID\"  type=\"hidden\" value=\"".$ph['ID']."\" ><input name=\"Created\" type=\"hidden\" value=\"".$ph['Created']."\"><input name=\"Updated\" type=\"hidden\" value=\"".$ph['Updated']."\"><input type=\"text\" name=\"Name\" value=\"".$ph['Name']."\"></td><td><input type=\"text\" name=\"Region\" value=\"".$ph['Region']."\" maxlength=\"3\"></td><td><input type=\"text\" name=\"Area\" value=\"".$ph['Area']."\" maxlength=\"3\"></td><td><input type=\"text\" name=\"Number\" value=\"".$ph['Number']."\" maxlength=\"8\"></td><td><input type=\"text\" name=\"Ext\" value=\"".$ph['Ext']."\" ></td><td><input class=\"Primary\" type=\"checkbox\" name=\"Primary\" onclick=\"checkPrimary(this)\"";
							if($ph['Primary'] == 1){ $out .= " checked"; }
							$out .= "></td><td><img class=\"rem_icon\" src=\"img/delete.png\" name=\"Delete\" onclick=\"deleteRow(this)\" ></td></tr>\n";
							$p = $p->getNext();
						}
						$out .= "</table>
						<span id=\"phones_err\"class=\"fs_err\"></span><div class=\"cc_add\" id=\"New_Phone\" onclick=\"addPhone('editContact')\">+ Add Phone</div>
						</fieldset><fieldset class=\"fs_addresses\">
						<legend>Addresses:</legend>
						<table>
						<tr class=\"formField formSetHead\"><td></td><td>Name</td><td>Address</td><td>Address 2</td><td>City</td><td>State</td><td>Zip</td><td>Primary</td><td>Remove</td></tr>";
						$a = $c->readNode()->getAddresses()->getFirstNode();
						while($a != NULL){
							$ad = $a->readNode()->toArray();
							$out .= "<tr class=\"formField\"><td><img class=\"err_icon\" title=\"\" src=\"img/error.png\" ></td><td><input name=\"ID\"  type=\"hidden\" value=\"".$ad['ID']."\" ><input name=\"Created\" type=\"hidden\" value=\"".$ad['Created']."\" ><input name=\"Updated\" type=\"hidden\" value=\"".$ad['Updated']."\" ><input type=\"text\" name=\"Name\" value=\"".$ad['Name']."\" ></td><td><input type=\"text\" name=\"Address\" value=\"".$ad['Address']."\" ></td><td><input type=\"text\" name=\"Address2\" value=\"".$ad['Address2']."\" ></td><td><input type=\"text\" name=\"City\" value=\"".$ad['City']."\" ></td><td><input type=\"text\" name=\"State\" value=\"".$ad['State']."\" maxlength=\"2\"></td><td><input type=\"text\" name=\"Zip\" value=\"".$ad['Zip']."\" maxlength=\"5\"></td><td><input class=\"Primary\" type=\"checkbox\" name=\"Primary\" onclick=\"checkPrimary(this)\"";
							if($ad['Primary'] == 1){ $out .= " checked";}
							$out .= "></td><td><img class=\"rem_icon\" src=\"img/delete.png\" name=\"Delete\" onclick=\"deleteRow(this)\" ></td></tr>\n";
							$a = $a->getNext();
						}
						$out .= "</table>
						<span id=\"addresses_err\"class=\"fs_err\"></span><div class=\"cc_add\" id=\"New_Address\" onclick=\"addAddress('editContact')\">+ Add Address</div>
						</fieldset>
						<table><tr><td colspan=\"2\"><input class=\"\" type=\"button\" value=\"Apply Changes\" onClick=\"completeForm('editContact','ajax.php?ari=4','editContactRes',validateContactFormData,submitContactFormData)\"></td></tr></table>";
						echo $out;
					}else{ echo ""; }
				}
			break;
			case 3: //Submit Contact_BG Data
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$cid = (int)$_POST['id'];
					$created = (int)$_POST['created'];
					$updated = (int)$_POST['updated'];
					$first = mysql_escape_string($_POST['first']);
					$last = mysql_escape_string($_POST['last']);
					$bday = strtotime($_POST['bday']);
					$state_org = mysql_escape_string($_POST['state_org']);
					$state_title = mysql_escape_string($_POST['state_title']);
					$company = mysql_escape_string($_POST['company']);
					$title = mysql_escape_string($_POST['title']);					
					if($_POST['codes'] != ""){ $codes = explode(",",mysql_escape_string($_POST['codes'])); }else{ $codes = NULL; }
					if($_POST['committees'] != ""){ $committees = explode(",",mysql_escape_string($_POST['committees'])); }else{ $committees = NULL;  }
					
					$fcont = new Contact_BG();
					$fcont->init($cid,$created,$updated,$first,$last,$bday,$company,$title,$state_org,$state_title);
					
					//Initialize Form Content Object
					if($codes != NULL){
						for($i = 0;$i < count($codes);$i += 1){
							for($j = 0;$j < count($_SESSION['code_arr']); $j += 1){ 
								$code = explode("|",$codes[$i]);
								if($codes[$i] == $_SESSION['code_arr'][$j]['ID']){
									$r = new Relation();
									$r->init($code[0],$code[1],$code[2],$cid,$code[3],$_SESSION['code_arr'][$j]['Code'],$_SESSION['code_arr'][$j]['Definition']);
									$fcont->getCodes()->insertLast($r);
								}
							}
						}
					}
					if($committees != NULL){
						for($i = 0;$i < count($committees);$i += 1){
							for($j = 0;$j < count($_SESSION['comm_arr']); $j += 1){
							$committee = explode("|",$committees[$i]);
								if($committee[3] == $_SESSION['comm_arr'][$j]['ID']){
									$r = new Relation();
									$r->init($committee[0],$committee[1],$committee[2],$cid,$committee[3],$_SESSION['comm_arr'][$j]['Code'],$_SESSION['comm_arr'][$j]['Definition']);
									$fcont->getCommittees()->insertLast($r);
								}
							}
						}
					}
					for($i = 1;$i <= 5;$i += 1){
						if(!isset($_POST['em'.$i.'_id'])){ continue; }
						$e = new Email();
						$e->init($_POST['em'.$i.'_id'],$_POST['em'.$i.'_created'],$_POST['em'.$i.'_updated'],$_POST['em'.$i.'_name'],$cid,$_POST['em'.$i.'_primary'],$_POST['em'.$i.'_address']);
						$fcont->getEmails()->insertLast($e);
					}
					for($i = 1;$i <= 5;$i += 1){ 
						if(!isset($_POST['ph'.$i.'_id'])){ continue; }
						$p = new Phone();
						$p->init($_POST['ph'.$i.'_id'],$_POST['ph'.$i.'_created'],$_POST['ph'.$i.'_updated'],$_POST['ph'.$i.'_name'],$cid,$_POST['ph'.$i.'_primary'],$_POST['ph'.$i.'_region'],$_POST['ph'.$i.'_area'],$_POST['ph'.$i.'_number'],$_POST['ph'.$i.'_ext']);
						$fcont->getPhones()->insertLast($p);
					}
					for($i = 1;$i <= 5;$i += 1){
						if(!isset($_POST['ad'.$i.'_id'])){ continue; }
						$a = new Address();
						$a->init($_POST['ad'.$i.'_id'],$_POST['ad'.$i.'_created'],$_POST['ad'.$i.'_updated'],$_POST['ad'.$i.'_name'],$cid,$_POST['ad'.$i.'_primary'],$_POST['ad'.$i.'_address'],$_POST['ad'.$i.'_address2'],$_POST['ad'.$i.'_city'],$_POST['ad'.$i.'_state'],$_POST['ad'.$i.'_zip']);
						$fcont->getAddresses()->insertLast($a);
					}
					//Write to Database
					$fcont->dbWrite($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					echo "Contact Added!"; 
				}	
			break;
			case 4: //Submit Edit Content Data
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$cid = (int)$_POST['id'];
					$created = (int)$_POST['created'];
					$updated = (int)$_POST['updated'];
					$first = mysql_escape_string($_POST['first']);
					$last = mysql_escape_string($_POST['last']);
					$bday = strtotime($_POST['bday']);
					$state_org = mysql_escape_string($_POST['state_org']);
					$state_title = mysql_escape_string($_POST['state_title']);
					$company = mysql_escape_string($_POST['company']);
					$title = mysql_escape_string($_POST['title']);    
					if($_POST['codes'] != ""){ $codes = explode(",",mysql_escape_string($_POST['codes'])); }else{ $codes = NULL; } 
					if($_POST['committees'] != ""){ $committees = explode(",",mysql_escape_string($_POST['committees'])); }else{ $committees = NULL;  }
					
					//Initialize Form Content Object
					$fcont = new Contact_BG();
					$fcont->init($cid,$created,$updated,$first,$last,$bday,$company,$title,$state_org,$state_title);
					
					if($codes != NULL){
						for($i = 0;$i < count($codes);$i += 1){ 
							for($j = 0;$j < count($_SESSION['code_arr']); $j += 1){
								$code = explode("|",$codes[$i]);
								if($code[3] == $_SESSION['code_arr'][$j]['ID']){
									$r = new Relation();
									$r->init($code[0],$code[1],$code[2],$cid,$code[3],$_SESSION['code_arr'][$j]['Code'],$_SESSION['code_arr'][$j]['Definition']);
									$fcont->getCodes()->insertLast($r);
								}
							}
						}
					}
					if($committees != NUll){
						for($i = 0;$i < count($committees);$i += 1){ 
							for($j = 0;$j < count($_SESSION['comm_arr']); $j += 1){
								$committee = explode("|",$committees[$i]);
								if($committee[3] == $_SESSION['comm_arr'][$j]['ID']){
									$r = new Relation();
									$r->init($committee[0],$committee[1],$committee[2],$cid,$committee[3],$_SESSION['comm_arr'][$j]['Code'],$_SESSION['comm_arr'][$j]['Definition']);
									$fcont->getCommittees()->insertLast($r);
								}   
							}   
						}
					}
					
					for($i = 1;$i <= 5;$i += 1){ 
						if(!isset($_POST['em'.$i.'_id'])){ continue; }
						$e = new Email();
						$e->init($_POST['em'.$i.'_id'],$_POST['em'.$i.'_created'],$_POST['em'.$i.'_updated'],$_POST['em'.$i.'_name'],$cid,$_POST['em'.$i.'_primary'],$_POST['em'.$i.'_address']);
						$fcont->getEmails()->insertLast($e);
					}   
					for($i = 1;$i <= 5;$i += 1){ 
						if(!isset($_POST['ph'.$i.'_id'])){ continue; }
						$p = new Phone();
						$p->init($_POST['ph'.$i.'_id'],$_POST['ph'.$i.'_created'],$_POST['ph'.$i.'_updated'],$_POST['ph'.$i.'_name'],$cid,$_POST['ph'.$i.'_primary'],$_POST['ph'.$i.'_region'],$_POST['ph'.$i.'_area'],$_POST['ph'.$i.'_number'],$_POST['ph'.$i.'_ext']);
						$fcont->getPhones()->insertLast($p);
					}   
					for($i = 1;$i <= 5;$i += 1){ 
						if(!isset($_POST['ad'.$i.'_id'])){ continue; }
						$a = new Address();
						$a->init($_POST['ad'.$i.'_id'],$_POST['ad'.$i.'_created'],$_POST['ad'.$i.'_updated'],$_POST['ad'.$i.'_name'],$cid,$_POST['ad'.$i.'_primary'],$_POST['ad'.$i.'_address'],$_POST['ad'.$i.'_address2'],$_POST['ad'.$i.'_city'],$_POST['ad'.$i.'_state'],$_POST['ad'.$i.'_zip']);
						$fcont->getAddresses()->insertLast($a);
					}
					
					//Initialize Database Content Object
					$cont = $_SESSION['contacts']->getFirstNode();
					$index = 0;
					while($cont != NULL){
						$c = $cont->readNode();
						$ca = $c->toArray();
						if($ca['ID'] == $cid){ break;}
						$cont = $cont->getNext();
						$index += 1;
					}
					$cont = $c;
					
					//DB Write Form Object/Session Update
					$cont->dbRead($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					$fcont->dbWrite($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					$fcont->setDonations($_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					$_SESSION['contacts']->deleteNodeAt($index);
					$_SESSION['contacts']->insertLast($fcont);
					
					//CHECK FOR OBSOLETE RECORDS IN DATABASE
					//Checks for Relations
					$fcod = $fcont->getCodes()->getFirstNode();
					$cod = $cont->getCodes()->getFirstNode();
					while($cod != NULL){
						$d = $cod->readNode()->toArray();
						$hasCode = False;
						while($fcod != NULL){
							$fd = $fcod->readNode()->toArray();
							//error_log("COD CHECK: ".$d['ID']."::".$fd['ID']);
							if($d['ID'] == $fd['ID']){ $hasCode = True; break; }
							$fcod = $fcod->getNext();
						}
						if(!$hasCode){ $cod->readNode()->dbDelete($_SESSION['db']['Con']->con($_SESSION['db']['Active'])); }
						$cod = $cod->getNext();
					}
					$fcom = $fcont->getCommittees()->getFirstNode();
					$com = $cont->getCommittees()->getFirstNode();
					while($com != NULL){
						$m = $com->readNode()->toArray();
						$hasCode = False;
						while($fcom != NULL){
							$fm = $fcom->readNode()->toArray(); 
							//error_log("COM CHECK: ".$m['ID']."::".$fm['ID']);
							if($m['ID'] == $fm['ID']){ $hasCode = True; break; }
							$fcom = $fcom->getNext();
						}
						if(!$hasCode){ $com->readNode()->dbDelete($_SESSION['db']['Con']->con($_SESSION['db']['Active'])); }
						$com = $com->getNext();
					}
					
					//Checks for Contact Info
					$femails = $fcont->getEmails()->getFirstNode();
					$emails = $cont->getEmails()->getFirstNode();
					while($emails != NULL){
						$e = $emails->readNode()->toArray();
						$hasEmail = False;
						while($femails != NULL){
							$fe = $femails->readNode()->toArray();
							if($e['ID'] == $fe['ID']){ $hasEmail = True; break;}
							$femails = $femails->getNext();
						}
						if(!$hasEmail){ $emails->readNode()->dbDelete($_SESSION['db']['Con']->con($_SESSION['db']['Active'])); }
						$emails = $emails->getNext();
					}
					$fphones = $fcont->getPhones()->getFirstNode();
					$phones = $cont->getPhones()->getFirstNode();
					while($phones != NULL){
						$p = $phones->readNode()->toArray();
						$hasPhone = False;
						while($fphones != NULL){
							$fp = $fphones->readNode()->toArray();
							if($p['ID'] == $fp['ID']){ $hasPhone = True; break;}
							$fphones = $fphones->getNext();
						}   
						if(!$hasPhone){ $phones->readNode()->dbDelete($_SESSION['db']['Con']->con($_SESSION['db']['Active']));}
						$phones = $phones->getNext();
					}
					$faddresses = $fcont->getAddresses()->getFirstNode();
					$addresses = $cont->getAddresses()->getFirstNode();
					while($addresses != NULL){
						$a = $addresses->readNode()->toArray();
						$hasAddress = False;
						while($faddresses != NULL){
							$fa = $faddresses->readNode()->toArray();
							if($a['ID'] == $fa['ID']){ $hasAddress = True; break;}
							$faddresses = $faddresses->getNext();
						}   
						if(!$hasAddress){ $adresses->readNode()->dbDelete($_SESSION['db']['Con']->con($_SESSION['db']['Active']));}
						$addresses = $addresses->getNext();
					}
					echo"Contact Updated";
					//echo print_r($fcont->toArray()) . print_r($cont->toArray());
				}
			break;
			case 5: //Submit Review Contact Form
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$id = mysql_escape_string($_POST['i']);
					if($id != 0){
						$cont = $_SESSION['contacts']->getFirstNode();
						while($cont != NULL){
							$c = $cont->readNode()->toArray();
							if($c['ID'] == $id){break;}
							$cont = $cont->getNext();
						}
						$s = "<table id='contactReview'>
						<tr><th>Name:</th><td>".$c['Last'].", ".$c['First']."</td></tr>
						<tr><th>State Org:</th><td>".$c['State_Org']."</td></tr>
						<tr><th>State Title:</th><td>".$c['State_Title']."</td></tr>
						<tr><th>Company:</th><td>".$c['Company']."</td></tr>
						<tr><th>Company Title:</th><td>".$c['Title']."</td></tr>
						<tr><th>Emails:</th><td>";
						foreach($c['Emails'] as $em){ $s .= $em['Name']." : ".$em['Address']."<br>\n"; }
						$s .= "</td></tr>
						<tr><th>Phones:</th><td>";
						foreach($c['Phones'] as $ph){ $s .= $ph['Name']." : ".$ph['Region']."-(".$ph['Area'].")-".$ph['Number']."<br>\n"; }
						$s .= "</td></tr>
						<tr><th>Addresses:</td><td>";
						foreach($c['Addresses'] as $ad){ $s .= $ad['Name']." : ".$ad['Address'].", ".$ad['Address2'].", ".$ad['City'].", ".$ad['State'].", ".$ad['Zip']."<br>\n";  }
						$s .= "</td></tr>
						<tr><th>Codes:</th><td>";
						foreach($c['Rels']['Codes'] as $cod){ $s .= $cod['Code'] . " - " . $cod['Definition'] . "<br>\n"; }
						$s .= "</td></tr>
						<tr><th>Committees:</th><td>";
						foreach($c['Rels']['Committees'] as $com){ $s .= $com['Code'] . " - " . $com['Definition'] . "<br>\n"; }
						$s .= "</td></tr>
						<tr><th>Donations:</th><td>";
						foreach($c['Donations'] as $d){ $s .= $d['Date'] . " :: $" . $d['Amount'] . "<br>\n"; }
						$s.= "</td></tr>
						</table>";
						echo $s;
					}else{ echo ""; }
				}
			break;
			case 6: //Submit Enter Donation Form
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$id = mysql_escape_string($_POST['id']);
					$date = mysql_escape_string($_POST['date']);
					$amount = floatval(mysql_escape_string($_POST['amount']));
					
					$d = new Donation();
					$d->init(0,0,0,$id,$date,$amount);
					
					if($d->dbWrite($_SESSION['db']['Con']->con($_SESSION['db']['Active']))){ 
						$cont = $_SESSION['contacts']->getFirstNode();
						$index = 0;
						while($cont != NULL){
							$c = $cont->readNode()->toArray();
							if($c['ID'] == $id){
								$co = $cont->readNode();
								$co->getDonations()->insertLast($d);
								$_SESSION['contacts']->deleteNodeAt($index);
								$_SESSION['contacts']->insertLast($co);
								break;
							}
							$cont = $cont->getNext();
							$index += 1;
						}
						echo "Donation Added"; 
					}else{ echo "Error!"; }
				}
			break;
			case 7: //Reports 3.0
				if(!$_SESSION['db']['Con']->connect($_SESSION['db']['Active'])){
					echo "CONNECTION FAILURE <br >";
				}else{
					$code = mysql_escape_string($_GET['cd']);
					$comm = mysql_escape_string($_GET['cm']);
					$stat = $_GET['s'];
					
					$cont_qry = "SELECT * FROM Contact_Export";
					$title = $_SESSION['settings']['Org_Name']." - ";
					if($stat != "a"){ if($stat == "c"){ $title .= "Current "; }else{ $title .= "Past "; } } 
					$title .= "Contact Report";
					if($code != 0){
						for($i = 0; $i < count($_SESSION['code_arr']); $i += 1){
							if($_SESSION['code_arr'][$i]['ID'] == $code){ $abr = $_SESSION['code_arr'][$i]['Code']; break; }
						}
						$title .= " (Code: ".$abr.")";
					}
					if($comm != 0){
						for($i = 0; $i < count($_SESSION['comm_arr']); $i += 1){
							if($_SESSION['comm_arr'][$i]['ID'] == $comm){ $abr = $_SESSION['comm_arr'][$i]['Code']; break; }
						}
						$title .= "(Committee: ".$abr.")";
					}
					
					$result = mysql_query($cont_qry,$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					
					//Initialize XLSX
					require_once("_php/PHPExcel/PHPExcel.php");
					$objPHPExcel = new PHPExcel();
					$objPHPExcel->getProperties()->setCreator("")
					->setLastModifiedBy("")
					->setTitle("Office 2007 XLSX Test Document")
					->setSubject("Office 2007 XLSX Test Document")
					->setDescription("Contact sheet, complete contact listing from database.");
					//Write Header
					$report_title = new PHPExcel_RichText();
					$objPayable = $report_title->createTextRun($title);		
					$objPayable->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($report_title);
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', date("D, M j, Y g:ia"))
					->setCellValue('A4', 'ID')
					->setCellValue('B4', 'First')
					->setCellValue('C4', 'Last')
					->setCellValue('D4', 'State Org')
					->setCellValue('E4', 'State Title')
					->setCellValue('F4', 'Company')
					->setCellValue('G4', 'Company Title')
					->setCellValue('H4', 'Email')
					->setCellValue('I4', 'Phone')
					->setCellValue('J4', 'Address')
					->setCellValue('K4', 'Address 2')
					->setCellValue('L4', 'City')
					->setCellValue('M4', 'State')
					->setCellValue('N4', 'Zip')
					->setCellValue('O4', 'Donations');
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('P4', 'Codes')
					->setCellValue('Q4', 'Committees');
					
					//Write Data
					$i = 5;
					while($row = mysql_fetch_array($result)){
						$skip = false;
						if($code != 0){
							$skip = true; 
							$codes = explode(";",$row['Codes']); 
							for($j = 0; $j < count($codes); $j += 1){
								$c = explode(":",$codes[$j]);
								if($c[0] == $code){
									$skip = false; 
									break;
								}
							}
						}
						if($comm != 0){
							if(($code != 0 && !$skip) || $code == 0){
								$skip = true; 
								$comms = explode(";",$row['Committees']);
								for($j = 0; $j < count($comms); $j += 1){
									$c = explode(":",$comms[$j]);
									if($c[$j] == $comm){
										$skip = false;
										break;
									}
								}
							}
						}
						if($stat != "a" && !$skip){
							if($row['Donations'] != NULL){
								$y = date("Y",time());
								$cfny = strtotime($y."-".$_SESSION['settings']['Fiscal_Year']);
								if($cfny > time()){$sfy = strtotime(date("Y-m-d",$cfny)." -1 year"); $efy = strtotime(date("Y-m-d",$cfny)." -1 day");}else{$sfy = $cfny; $efy = strtotime(date("Y-m-d",$cfny)." +1 year, -1 day");}
								$donations = explode(";",$row['Donations']);
								$d_dates = array();
								for($j = 0; $j < count($donations); $j += 1){
									$temp = explode(":",$donations[$j]);
									$d_dates[$j] = $temp[4];
								}
								if($stat == "p"){
									for($j = 0; $j < count($d_dates); $j += 1){
									if(strtotime($d_dates[$j]) > $sfy){$skip = true;}
									}
								}
								if($stat == "c"){
									$skip = true;
									for($j = 0; $j < count($d_dates); $j += 1){
										if(strtotime($d_dates[$j]) > $sfy){$skip = false;}
									}
								}
							}else{ $skip = true; }
						}
						if(!$skip){
							$codeList = NULL;
							if($row['Codes'] != NULL){	
								$codes = explode(";",$row['Codes']); 
								for($j = 0; $j < count($codes); $j += 1){ 
									$c = explode(":",$codes[$j]);
									if($codeList != NULL){ $codeList .= ", "; }
									$codeList .= $c[1]; 
								}
							}
							$commList = NULL;
							if($row['Committees'] != NULL){
								$comms = explode(";",$row['Committees']);
								for($j = 0; $j < count($comms); $j += 1){ 
									$c = explode(":",$comms[$j]);
									if($commList != NULL){ $commList .= ", "; }
									$commList .= $c[1];
								}
							}
							$e = explode(";",$row['Emails']);
							for($j = 0; $j < count($e); $j += 1){ 
								$temp = explode(":",$e[$j]);
								if($temp[5] == '1'){ $email = $temp[6]; break; }
							}
							if($row['Phones'] != NULL){
								$p = explode(";",$row['Phones']);
								for($j = 0; $j < count($p); $j += 1){
									$temp = explode(":",$p[$j]);
									if($temp[5] == '1'){ $phone = $temp[6]."-(".$temp[7].")-".$temp[8]; break; }
								}
							}
							if($row['Addresses'] != NULL){
								$a = explode(";",$row['Addresses']);
								for($j = 0; $j < count($a); $j += 1){ 
									$temp = explode(":",$a[$j]);
									if($temp[5] == '1'){ $address = $temp; break; }
								}
							}
							$donations = NULL;
							if($row['Donations'] != NULL){
								$d = explode(";",$row['Donations']);
								for($j = 0; $j < count($d); $j += 1){
									$temp = explode(":",$d[$j]);
									if($j > 0){ $donations .= " \n"; }
									$donations .= $temp[4] . ": $".$temp[5].".00"; 
								}
							}
							$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $row['ID'])
							->setCellValue('B'.$i, $row['First'])
							->setCellValue('C'.$i, $row['Last'])
							->setCellValue('D'.$i, $row['State_Org'])
							->setCellValue('E'.$i, $row['State_Title'])
							->setCellValue('F'.$i, $row['Company'])
							->setCellValue('G'.$i, $row['Title'])
							->setCellValue('H'.$i, $email)
							->setCellValue('I'.$i, $phone)
							->setCellValue('J'.$i, $address[6])
							->setCellValue('K'.$i, $address[7])
							->setCellValue('L'.$i, $address[8])
							->setCellValue('M'.$i, $address[9])
							->setCellValue('N'.$i, $address[10])
							->setCellValue('O'.$i, $donations)
							->setCellValue('P'.$i, $codeList)
							->setCellValue('Q'.$i, $commList);
							$i += 1;
						}
					}
					//Format Column Size
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(3);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setAutoSize(true);
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setAutoSize(true);
					//Format Column Titles
					$styleArray = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000000'))),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:Q4')->applyFromArray($styleArray);
					
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="DataExport.xlsx"');
					header('Cache-Control: max-age=0');
					
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					$objWriter->save('php://output');
				}
			break;
			case 8: //Name Search
			
				$sstr = $_POST['sstr'];
				$res = $_POST['res'];
				$fn_matches = array();
				$ln_matches = array();
				$cn = $_SESSION['contacts']->getFirstNode();
				for($i = 0; $i < $_SESSION['contacts']->size(); $i += 1){
					$c = $cn->readNode()->toArray();
					$pos=stripos($c['First'],$sstr);
					if($pos !== false && $pos == 0){$fn_matches[]=$c;}
					$cn = $cn->getNext();
				}
				$cn = $_SESSION['contacts']->getFirstNode();
				for($i = 0; $i < $_SESSION['contacts']->size(); $i += 1){
					$c = $cn->readNode()->toArray();
					$pos=stripos($c['Last'],$sstr);
					if($pos !== false && $pos == 0){$ln_matches[]=$c;}
					$cn = $cn->getNext();
				}
				for($i = 0; $i < count($ln_matches); $i += 1){
					for($j = 0; $j < count($fn_matches); $j += 1){
						if($ln_matches[$i]['ID'] == $fn_matches[$j]['ID']){
							array_splice($fn_matches,$j,1);
						}
					}
				}
				
				if(count($ln_matches) > 10){$ln_matches = array_slice($ln_matches,0,10);}
				if(count($ln_matches) < 10 && count($fn_matches) > (10 - count($ln_matches))){$fn_matches = array_slice($fn_matches,0,(10-count($ln_matches)));}
				
				$ari = -1;
				if($res == "CE"){$ari = 2;}
				if($res == "DA"){$ari = 11;}
				if($res == "reviewContactRes"){ $ari = 5;}
				
				$os = "<ul class=\"search_res\">\n";
				for($i = 0; $i < count($ln_matches); $i += 1){
					$os .= "<li onClick=\"ajax('ajax.php','ari=".$ari."&i=".$ln_matches[$i]['ID']."','".$res."');resetSessTimeout();\"><b>".$ln_matches[$i]['Last']."</b>, ".$ln_matches[$i]['First']."</li>\n";
				}
				for($i = 0; $i < count($fn_matches); $i += 1){
					$os .= "<li onClick=\"ajax('ajax.php','ari=".$ari."&i=".$fn_matches[$i]['ID']."','".$res."');resetSessTimeout();\">".$fn_matches[$i]['Last'].", <b>".$fn_matches[$i]['First']."</b></li>\n";
				}
				$os .= "</ul>";
				echo $os;
			
			break;
			case 9: //Generate Donation Data
				$start = new DateTime();
				$start->setDate(2014,1,1);
				$end = new DateTime('NOW');
				for($i = 1; $i < 43; $i += 1){
					$id = $i;
					$date = random_date_in_range($start,$end);
					$amount = rand(100,10000);
					$sql = "INSERT INTO `Donations` (`ID`,`CID`,`Date`,`Amount`) VALUES (NULL,\"".$id."\",\"".date("Y-m-d",$date->getTimestamp())."\",\"".$amount."\")";
					//$result = mysql_query($sql,$_SESSION['db']['Con']->con($_SESSION['db']['Active']));
					echo $result." - ".$id." - ".date("Y-m-d",$date->getTimestamp())." - ".$amount."<br >";
				}
			break;
			case 10: //Logout
				session_destroy();
			break;
			case 11: //Generate Add Donation Form
				$cid = $_POST['i'];
				$c = $_SESSION['contacts']->getFirstNode();
				for($i = 0; $i < $_SESSION['contacts']->size(); $i += 1){
					$ca = $c->readNode()->toArray();
					if($ca['ID'] == $cid){ break; }
					$c = $c->getNext();
				}
				$s = "<table>
				<tr class=\"formField formSetHead\"><td>Contact</td><td>Date</td><td>Amount</td></tr>
				<tr class=\"formField\"><td id=\"df_name\"><input type=\"hidden\" name=\"id\" value=\"".$ca['ID']."\" />".$ca['Last'].", ".$ca['First']."</td>
				<td><input type=\"date\" name=\"date\" /><span id=\"date_err\" class=\"err_msg\"></span></td>
				<td><input type=\"text\" name=\"amount\" /><span id=\"amount_err\" class=\"err_msg\"></span></td></tr>
				<tr><td colspan=\"3\"><input type=\"button\" value=\"Submit\" onClick=\"completeForm('addDonation','ajax.php?ari=6','donationRes',validateDonationFormData,submitDonationFormData)\"></td></tr>
				</table>";
				echo $s;
			break;
		}
	}else{echo "BAD REQUEST"; /*BAD ARI*/}
?>
