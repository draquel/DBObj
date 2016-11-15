<?php
	class User extends Contact{
		protected $username;
		private $password;
		protected $status;
		protected $llogin;
		
		public function __construct($id){
			Contact::__construct($id,"Users");
			Root::setRelationships(array('Groups'=>new Relationship("User","Groups")));
			$this->username = NULL;
			$this->password = NULL;
			$this->status = NULL;
			$this->llogin = NULL;
		}
/*		public function init($id,$f,$l,$bd,$cd,$ud,$c,$t,$e,$u,$p,$ld){
			Contact::init($id,$f,$l,$bd,$cd,$ud,$c,$t,$e);
			$this->setUname($u);
			$this->setPass($p);
			$this->setLLogin($ld);
		}*/
		public function initMysql($row){
			Contact::initMysql($row);
			if(isset($row['Username'])){ $this->setUname($row['Username']); }
			if(isset($row['Password'])){ $this->setPass($row['Password']); }
			if(isset($row['LLogin'])){ $this->setLLogin($row['LLogin']); }
			if(isset($row['Groups'])){
				$row['Groups'] = explode(";",$row['Groups']);
				$groups = array();
				foreach($row['Groups'] as $cat){ 
					$a = explode(":",$cat); 
					for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} } 
					$groups[] = array("ID"=>$a[0],"Created"=>NULL,"Updated"=>NULL,"RID"=>$a[1],"KID"=>$a[2],"Key"=>$a[3],"Code"=>$a[4],"Definition"=>$a[5]); 
				}
				$relations = $this->getRelationships();
				$relations['Groups']->initMysql($groups);
				$this->setRelationships($relations);
			}
		}
		public function toArray(){
			$a = Contact::toArray();
			$a['Username'] = $this->getUname();
			$a['LLogin'] = $this->getLLogin('Y-m-d');
			$a['Groups'] = array();
			$g = $this->getGroups()->getFirstNode();
			for($i = 0; $i < $this->getGroups()->size(); $i += 1){ 
				$ar = $g->readNode()->toArray();
				$a['Groups'][$i] = $ar;
				$g = $g->getNext();
			}
			return $a; 
		}
		public function login($u,$p,$con){
			$ea = false;
			if(validEmail($u)){
				$ea = true;
				$e = new Email(NULL);
				$e->initMysql(array("ID"=>NULL,"Created"=>NULL,"Updated"=>NULL,"Name"=>"Login","PID"=>NULL,"Primary"=>1,"Address"=>$u));
				$this->getEmails()->insertLast($e);
			}else{ $this->setUname($u); }
			$this->setPass(sha1($p));
			$row = $this->authenticate($con);
			if($row){
				if($ea){ $this->setEmails(new DLList()); }
				$this->initMysql($row);
				return TRUE;
			}else{ return FALSE; }
		}
		public function setContactInfo($con){
			Contact::setContactInfo($con);
		}
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Users_Data` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Users` (`ID`,`First`,`Last`,`BDay`,`Created`,`Updated`,`Username`,`Password`,`LLogin`) VALUES (NULL,\"".$this->getFirst()."\",\"".$this->getLast()."\",\"".$this->getBDay(NULL)."\",\"".time()."\",\"".time()."\",\"".$this->getUname()."\",\"".$this->getPass()."\",\"".$this->getLLogin(NULL)."\")";
			$res = mysqli_query($con,$sql);
			if($res){ $this->setID(mysqli_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Users` SET `First`=\"".$this->getFirst()."\",`Last`=\"".$this->getLast()."\",`Updated`=\"".time()."\",`BDay`=\"".$this->getBDay(NULL)."\",`Username`=\"".$this->getUname()."\",`Password`=\"".$this->getPass()."\",`LLogin`=\"".$this->getLLogin()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function mysqlEsc($con){
			Contact::mysqlEsc($con);
			$this->setUname(mysqli_escape_string($con,$this->getUname()));
			$this->setPass(mysqli_escape_string($con,$this->getPass()));
		}
		protected function authenticate($con){
			/*$this->mysqlEsc($con);*/
			if($this->getUname() != NULL){
				$sql = "SELECT u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`ID`,':',`p`.`Created`,':',`p`.`Updated`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`ID`,':',`a`.`Created`,':',`a`.`Updated`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`ID`,':',`e`.`Created`,':',`e`.`Updated`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails` FROM Users u LEFT JOIN Relationships r ON u.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `u`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `u`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `u`.`ID` WHERE u.Username = '".$this->getUname()."' AND u.Password = '".$this->getPass()."' GROUP BY u.ID ORDER BY u.Created DESC";
			}elseif($this->getEmails()->size() > 0){
				$ea = $this->getEmails()->getFirstNode()->readNode()->toArray();
				$sql = "SELECT u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`ID`,':',`p`.`Created`,':',`p`.`Updated`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`ID`,':',`a`.`Created`,':',`a`.`Updated`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`ID`,':',`e`.`Created`,':',`e`.`Updated`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails` FROM Users u LEFT JOIN Relationships r ON u.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `u`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `u`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `u`.`ID` WHERE e.Address = '".$ea['Address']."' AND u.Password = '".$this->getPass()."' GROUP BY u.ID ORDER BY u.Created DESC";	
			}else{ return false; }
			$res = mysqli_query($con,$sql);
			if(mysqli_num_rows($res) == 1){ return mysqli_fetch_array($res); }else{ return FALSE; }
		}
		protected function setUname($un){ $this->username = (string)$un; }
		protected function setPass($p){ $this->password = (string)$p; }
		protected function setLLogin($t){ $this->llogin = (int)$t;  }
		protected function setGroups($con){ Root::setRelation("User","Groups",$con); }
		protected function getUname(){ return (string)$this->username; }
		protected function getPass(){ return (string)$this->password; }
		protected function getLLogin($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->llogin); }else{ return (int)$this->llogin; } }
		protected function getGroups(){ $rels = Root::getRelationships(); return $rels['Groups']->getRels(); }
	}
?>
