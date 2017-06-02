<?php
require_once("contactInfo.class.php");
require_once("relationship.class.php");

class Email extends ContactInfo{
	private $address;

	public function __construct($id,$ptype){
		ContactInfo::__construct($id,"Emails",$ptype);
		Root::setRelationships(array('Parent'=>new Relationship($this->_ptype,"Parent")));
		$this->address = NULL;
	}
	public function init($row){ 
		ContactInfo::init($row);
		$this->setAddress($row['Address']);
	}
	public function sameAs($oa){
		$same = true;
		if(strtolower($oa['Address']) != strtolower($this->getAddress())){ $same = false;}
		return $same;
	}
	public function toArray(){
		$p = ContactInfo::toArray();
		$p['Address'] = $this->getAddress();
		return $p;
	}
	public function dbRead($pdo){
		if(ContactInfo::dbRead($pdo)){
			return true;
		}else{ return false; }
	}
	public function db_unique($con){
		$this->msqlEsc();
		$sql = "SELECT * FROM `Emails` WHERE `Address`=:Address";
		if($this->getID() != 0){ $sql .= " AND ID !=:ID"; }
		try{ $stmt = $pdo->prepare($sql); $stmt->execute(["ID"=>$this->getID(),"Address"=>$this->getAddress()]); }
		catch(PDOException $e){
			error_log("SQL Email->Uniqueness: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());
			return false;
		}
		if($stmt->rowCount() == 0){ return true; }else{ return false; }
	}
	protected function mysqlEsc($con){
		ContactInfo::mysqlEsc($con);
		$this->setAddress(mysql_escape_string($con,$this->getAddress()));
	}

	private function getAddress(){ return (string)$this->address; }
	private function setAddress($a){ $this->address = (string)$a; }
}

?>