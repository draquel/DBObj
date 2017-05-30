<?php
require_once("dllist.class.php");
require_once("person.class.php");
require_once("address.class.php");
require_once("phone.class.php");
require_once("email.class.php");

class Contact extends Person{
	protected $emails;
	protected $addresses;
	protected $phones;

	public function __construct($id,$t){
		if(!isset($t) || $t == NULL || $t == ""){ $t = "Contacts"; }
		Person::__construct($id,$t);
		$this->emails = new DLList();
		$this->addresses = new DLList();
		$this->phones = new DLList();
	}
	public function initMysql($row){
		Person::initMysql($row);
		if(isset($row['Phones']) && $row['Phones'] != NULL){
			$ph = explode(";",$row['Phones']);
			$this->phones = new DLList();
			for($i = 0; $i < count($ph); $i += 1){
				$p = explode(":",$ph[$i]);
				for($j = 0; $j < count($p); $j += 1){ if(!isset($p[$j])){ $p[$j] = NULL;} }
				$po = new Phone(NULL,rtrim($this->getTable(),"s"));
				$po->initMysql(array("ID"=>$p[0],"Created"=>$p[1],"Updated"=>$p[2],"Name"=>$p[3],"PID"=>$p[4],"Primary"=>$p[5],"Region"=>$p[6],"Area"=>$p[7],"Number"=>$p[8],"Ext"=>$p[9]));
				$this->getPhones()->insertLast($po);
			}
		}
		if(isset($row['Emails']) && $row['Emails'] != NULL){
			$em = explode(";",$row['Emails']);
			$this->emails = new DLList();
			for($i = 0; $i < count($em); $i += 1){
				$e = explode(":",$em[$i]);
				for($j = 0; $j < count($e); $j += 1){ if(!isset($e[$j])){ $e[$j] = NULL;} }
				$eo = new Email(NULL,rtrim($this->getTable(),"s"));
				//$eo->init($e[0],$e[1],$e[2],$e[3],$e[4],$e[5],$e[6]);
				$eo->initMysql(array("ID"=>$e[0],"Name"=>$e[1],"PID"=>$e[2],"Primary"=>$e[3],"Address"=>$e[4]));
				$this->getEmails()->insertLast($eo);
			}
		}
		if(isset($row['Addresses']) && $row['Addresses'] != NULL){
			$ad = explode(";",$row['Addresses']);
			$this->addresses = new DLList();
			for($i = 0; $i < count($ad); $i += 1){
				$a = explode(":",$ad[$i]);
				for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} }
				$ao = new Address(NULL,rtrim($this->getTable(),"s"));
				//$ao->init($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8],$a[9],$a[10]);
				$ao->initMysql(array("ID"=>$a[0],"Created"=>$a[1],"Updated"=>$a[2],"Name"=>$a[3],"PID"=>$a[4],"Primary"=>$a[5],"Address"=>$a[6],"Address2"=>$a[7],"City"=>$a[8],"State"=>$a[9],"Zip"=>$a[10]));
				$this->getAddresses()->insertLast($ao);
			}
		}
	}
	public function initContactInfo($type,$iArr){
		$type = strToLower($type);
		if(isset($type) && ($type == "email" || $type == "em" || $type == "e" || $type == "phone" || $type == "ph" || $type == "p" || $type == "address" || $type == "ad" || $type == "a")){
			switch($type){
				case "email" || "em" || "e":
					$e = new Email();
					$e->init($iArr['ID'],$iArr['Created'],$iArr['Updated'],$iArr['Name'],$this->getID(),$iArr['Primary'],$iArr['Address']);
					$this->getEmails()->insertLast($e);
				break;
				case "phone" || "ph" || "p":
					$p = new Phone();
					$p->init($iArr['ID'],$iArr['Created'],$iArr['Updated'],$iArr['Name'],$this->getID(),$iArr['Primary'],$iArr['Region'],$iArr['Area'],$iArr['Number'],$iArr['Ext']);
					$this->getPhones()->insertLast($p);
				break;
				case "address" || "ad" || "a":
					$a = new Address();
					$a->init($iArr['ID'],$iArr['Created'],$iArr['Updated'],$iArr['Name'],$this->getID(),$iArr['Primary'],$iArr['Address'],$iArr['Address2'],$iArr['City'],$iArr['State'],$iArr['Zip']);
					$this->getAddresses()->insertLast($a);
				break;
				default:
					return false;
				break;
			}
		}else{ return false; }
	}
	public function dbWrite($con){
		if(Root::dbWrite($con)){
			//Write Contact Info
			$this->setContactInfoPID();
			$em = $this->getEmails()->getFirstNode();
			while($em != NULL){
				$e = $em->readNode();
				$e->dbWrite($con);
				$em = $em->getNext();
			}
			$ph = $this->getPhones()->getFirstNode();
			while($ph != NULL){
				$p = $ph->readNode();
				$p->dbWrite($con);
				$ph = $ph->getNext();
			}
			$ad = $this->getAddresses()->getFirstNode();
			while($ad != NULL){
				$a = $ad->readNode();
				$a->dbWrite($con);
				$ad = $ad->getNext();
			}
			return true;
		}else{ return false; }
	}
	public function toArray(){
		$a = Person::toArray();
		$a['Addresses'] = array();
		$ad = $this->getAddresses()->getFirstNode();
		while($ad != NULL){
			$d = $ad->readNode()->toArray();
			$a['Addresses'][$d['Name']] = $d; 
			$ad = $ad->getNext();
		}
		$a['Phones'] = array();
		$ph = $this->getPhones()->getFirstNode();
		while($ph != NULL){
			$p = $ph->readNode()->toArray();
			$a['Phones'][$p['Name']] = $p; 
			$ph = $ph->getNext();
		}
		$a['Emails'] = array();
		$em = $this->getEmails()->getFirstNode();
		while($em != NULL){
			$e = $em->readNode()->toArray();
			$a['Emails'][$e['Name']] = $e;
			$em = $em->getNext();
		}
		return $a;
	}

	protected function mysqlEsc($con){
		Person::mysqlEsc($con);
	}
	protected function setAddresses($con){ 
		$this->addresses = new DLList();
		$sql = "SELECT a.* FROM Addresses a LEFT JOIN Relationships r ON a.DBO_ID = r.RID AND r.Key = 'Parent' WHERE a.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
		$res = mysqli_query($con,$sql);
		if($res){
			while($row = mysqli_fetch_array($res)){
				$a = new Address(NULL,rtrim($this->getTable()));
				$a->initMysql($row);
				$this->addresses->insertLast($a);
			}
			return true;
		}else{ error_log("SQL Contact->SetAddresses: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
	}
	protected function setPhones($con){
		$this->phones = new DLList();
		$sql = "SELECT p.* FROM Phones p LEFT JOIN Relationships r ON p.DBO_ID = r.RID AND r.Key = 'Parent' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
		$res = mysqli_query($con,$sql);
		if($res){
			while($row = mysqli_fetch_array($res)){
				$p = new Phone(NULL,rtrim($this->getTable()));
				$p->initMysql($row);
				$this->phones->insertLast($p);
			}
			return true;
		}else{ error_log("SQL Contact->SetPhones: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
	}
	protected function setEmails($con){
		$this->emails = new DLList();
		$sql = "SELECT e.* FROM Emails e LEFT JOIN Relationships r ON e.DBO_ID = r.RID AND r.Key = 'Parent' WHERE e.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
		$res = mysqli_query($con,$sql);
		if($res){
			while($row = mysqli_fetch_array($res)){
				$p = new Email(NULL,rtrim($this->getTable()));
				$p->initMysql($row);
				$this->emails->insertLast($p);
			}
			return true;
		}else{ error_log("SQL Contact->SetEmails: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
	}
	private function setContactInfoPID(){
		$em = $this->getEmails()->getFirstNode();
		while($em != NULL){
			$e = $em->readNode();
			$e->setPID($this->getID());
			$em = $em->getNext();
		}
		$ph = $this->getPhones()->getFirstNode();
		while($ph != NULL){
			$p = $ph->readNode();
			$p->setPID($this->getID());
			$ph = $ph->getNext();
		}
		$ad = $this->getAddresses()->getFirstNode();
		while($ad != NULL){
			$a = $ad->readNode();
			$a->setPID($this->getID());
			$ad = $ad->getNext();
		}	
	}
	public function setContactInfo($con){
		$this->setAddresses($con);
		$this->setPhones($con);
		$this->setEmails($con);
	}
	public function getEmails(){ return $this->emails; }
	public function getAddresses(){ return $this->addresses; }
	public function getPhones(){ return $this->phones; }
}

?>