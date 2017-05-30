<?php
require_once("contact.class.php");
require_once("relationship.class.php");

class User extends Contact{
	protected $username;
	private $password;
	protected $status;
	protected $llogin;

	public function __construct($id){
		Contact::__construct($id,"Users");
		Root::setRelationships(array('Group'=>new Relationship("User","Group")));
		$this->username = NULL;
		$this->password = NULL;
		$this->status = NULL;
		$this->llogin = NULL;
	}
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
			$relations['Group']->initMysql($groups);
			$this->setRelationships($relations);
		}
	}
	public function toArray(){
		$a = Contact::toArray();
		$a['Username'] = $this->getUname();
		$a['LLogin'] = $this->getLLogin('Y-m-d');
		return $a; 
	}
	public function login($u,$p,$con){
		$ea = false;
		if(validEmail($u)){
			$ea = true;
			$e = new Email(NULL,rtrim($this->getTable(),"s"));
			$e->initMysql(array("ID"=>NULL,"Created"=>NULL,"Updated"=>NULL,"Name"=>"Login","PID"=>NULL,"Primary"=>1,"Address"=>$u));
			$this->getEmails()->insertLast($e);
		}else{ $this->setUname($u); }
		$this->setPass(sha1($p));
		$row = $this->authenticate($con);
		if($row){
			if($ea){ $this->setEmails($con); }
			$this->initMysql($row);
			return TRUE;
		}else{ return FALSE; }
	}
	public function setContactInfo($con){
		Contact::setContactInfo($con);
	}
	protected function mysqlEsc($con){
		Contact::mysqlEsc($con);
		$this->setUname(mysqli_escape_string($con,$this->getUname()));
		$this->setPass(mysqli_escape_string($con,$this->getPass()));
	}
	protected function authenticate($con){
		/*$this->mysqlEsc($con);*/
		if($this->getUname() != NULL){
			$sql = "SELECT d.*, u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`DBO_ID`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`DBO_ID`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`DBO_ID`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails`  FROM DBObj d INNER JOIN Users u ON d.ID = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `d`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `d`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `d`.`ID` WHERE u.Username = '".$this->getUname()."' AND u.Password = '".$this->getPass()."' GROUP BY d.ID ORDER BY d.Created DESC";
		}elseif($this->getEmails()->size() > 0){
			$ea = $this->getEmails()->getFirstNode()->readNode()->toArray();
			$sql = "SELECT d.*, u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`DBO_ID`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`DBO_ID`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`DBO_ID`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails`  FROM DBObj d INNER JOIN Users u ON d.ID = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `d`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `d`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `d`.`ID` WHERE e.Address = '".$ea['Address']."' AND u.Password = '".$this->getPass()."' GROUP BY d.ID ORDER BY d.Created DESC";	
		}else{ return false; }
		$res = mysqli_query($con,$sql);
		if(mysqli_num_rows($res) == 1){ return mysqli_fetch_array($res); }else{ return FALSE; }
	}
	protected function setUname($un){ $this->username = (string)$un; }
	protected function setPass($p){ $this->password = (string)$p; }
	protected function setLLogin($t){ $this->llogin = (int)$t;  }
	protected function setGroups($con){ Root::setRelation("User","Group",$con); }
	protected function getUname(){ return (string)$this->username; }
	protected function getPass(){ return (string)$this->password; }
	protected function getLLogin($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->llogin); }else{ return (int)$this->llogin; } }
	protected function getGroups(){ $rels = Root::getRelationships(); return $rels['Group']->getRels(); }
}

?>