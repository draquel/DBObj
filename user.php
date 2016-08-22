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
			$this->setUname($row['Username']);
			$this->setPass($row['Password']);
			$this->setLLogin($row['LLogin']);
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
			$this->setUname($u);
			$this->setPass(sha1($p));
			$row = $this->authenticate($con);
			if($row){
				$this->initMysql($row);
				$this->setGroups($con);
				return TRUE;
			}else{ return FALSE; }
		}
		public function setContactInfo($con){
			Contact::setContactInfo($con);
		}
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Users_Data` WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Users` (`ID`,`First`,`Last`,`BDay`,`Created`,`Updated`,`Username`,`Password`,`LLogin`) VALUES (NULL,\"".$this->getFirst()."\",\"".$this->getLast()."\",\"".$this->getBDay(NULL)."\",\"".time()."\",\"".time()."\",\"".$this->getUname()."\",\"".$this->getPass()."\",\"".$this->getLLogin(NULL)."\")";
			$res = mysql_query($sql,$con);
			if($res){ $this->setID(mysql_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Users` SET `First`=\"".$this->getFirst()."\",`Last`=\"".$this->getLast()."\",`Updated`=\"".time()."\",`BDay`=\"".$this->getBDay(NULL)."\",`Username`=\"".$this->getUname()."\",`Password`=\"".$this->getPass()."\",`LLogin`=\"".$this->getLLogin()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function mysqlEsc(){
			Contact::mysqlEsc();
			$this->setUname(mysql_escape_string($this->getUname()));
			$this->setPass(mysql_escape_string($this->getPass()));
		}
		protected function authenticate($con){
			$this->mysqlEsc(false);
			$sql = "SELECT * FROM Users WHERE Username=\"".$this->getUname()."\" AND Password=\"".$this->getPass()."\"";
			$res = mysql_query($sql,$con);
			if(mysql_num_rows($res) == 1){ return mysql_fetch_array($res); }else{ return FALSE; }
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
