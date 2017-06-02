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
	public function init($row){
		Contact::init($row);
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
			$relations['Group']->init($groups);
			$this->setRelationships($relations);
		}
	}
	public function toArray(){
		$a = Contact::toArray();
		$a['Username'] = $this->getUname();
		$a['LLogin'] = $this->getLLogin('Y-m-d');
		return $a; 
	}
	public function login($u,$p,$pdo){
		$ea = false;
		if(validEmail($u)){
			$ea = true;
			$e = new Email(NULL,rtrim($this->getTable(),"s"));
			$e->init(array("ID"=>NULL,"Created"=>NULL,"Updated"=>NULL,"Name"=>"Login","PID"=>NULL,"Primary"=>1,"Address"=>$u));
			$this->getEmails()->insertLast($e);
		}else{ $this->setUname($u); }
		$this->setPass(sha1($p));
		$row = $this->authenticate($pdo);
		if($row){
			if($ea){ $this->setEmails($pdo); }
			$this->init($row);
			return TRUE;
		}else{ return FALSE; }
	}
	public function setContactInfo($pdo){
		Contact::setContactInfo($pdo);
	}
	protected function mysqlEsc($pdo){
		Contact::mysqlEsc($pdo);
		$this->setUname(mysqli_escape_string($pdo,$this->getUname()));
		$this->setPass(mysqli_escape_string($pdo,$this->getPass()));
	}
	protected function authenticate($pdo){
		/*$this->mysqlEsc($pdo);*/
		if($this->getUname() != NULL){
			$sql = "SELECT d.*, u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`DBO_ID`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`DBO_ID`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`DBO_ID`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails`  FROM DBObj d INNER JOIN Users u ON d.ID = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `d`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `d`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `d`.`ID` WHERE u.Username = :User AND u.Password = :Pass GROUP BY d.ID ORDER BY d.Created DESC";
			try{ $stmt = $pdo->prepare($sql); $stmt->execute(["User"=>$this->getUname(),"Pass"=>$this->getPass()]);	}
			catch(PDOException $e){	error_log("SQL User->Authenticate: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		}elseif($this->getEmails()->size() > 0){
			$ea = $this->getEmails()->getFirstNode()->readNode()->toArray();
			$sql = "SELECT d.*, u.*, group_concat(distinct concat(r.ID,':',r.RID,':',r.KID,':',r.Key,':',r.Code,':',r.Definition) separator ';') AS `Groups`, group_concat(distinct concat(`p`.`DBO_ID`,':',`p`.`Name`,':',`p`.`PID`,':',`p`.`Primary`,':',`p`.`Region`,':',`p`.`Area`,':',`p`.`Number`,':',`p`.`Ext`) separator ';') AS `Phones`, group_concat(distinct concat(`a`.`DBO_ID`,':',`a`.`Name`,':',`a`.`PID`,':',`a`.`Primary`,':',`a`.`Address`,':',`a`.`Address2`,':',`a`.`City`,':',`a`.`State`,':',`a`.`Zip`) separator ';') AS `Addresses`, group_concat(distinct concat(`e`.`DBO_ID`,':',`e`.`Name`,':',`e`.`PID`,':',`e`.`Primary`,':',`e`.`Address`) separator ';') AS `Emails`  FROM DBObj d INNER JOIN Users u ON d.ID = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Group' LEFT JOIN `Addresses` `a` on `a`.`PID` = `d`.`ID` LEFT JOIN `Phones` `p` on `p`.`PID` = `d`.`ID` LEFT JOIN `Emails` `e` on `e`.`PID` = `d`.`ID` WHERE e.Address = :Email AND u.Password = :Pass GROUP BY d.ID ORDER BY d.Created DESC";	
			try{ $stmt = $pdo->prepare($sql); $stmt->execute(["Pass"=>$this->getPass(),"Email"=>$ea['Address']]);	}
			catch(PDOException $e){	error_log("SQL User->Authenticate: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		}else{ return false; }
		if($stmt->rowCount() == 1){ return $stmt->fetch(PDO::FETCH_ASSOC); }else{ return FALSE; }
	}
	protected function setUname($un){ $this->username = (string)$un; }
	protected function setPass($p){ $this->password = (string)$p; }
	protected function setLLogin($t){ $this->llogin = (int)$t;  }
	protected function setGroups($pdo){ Root::setRelation("User","Group",$pdo); }
	protected function getUname(){ return (string)$this->username; }
	protected function getPass(){ return (string)$this->password; }
	protected function getLLogin($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->llogin); }else{ return (int)$this->llogin; } }
	protected function getGroups(){ $rels = Root::getRelationships(); return $rels['Group']->getRels(); }
}

?>