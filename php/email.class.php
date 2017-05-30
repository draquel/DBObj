<?php
require_once("contactInfo.class.php");
require_once("relationship.class.php");

class Email extends ContactInfo{
	private $address;

	public function __construct($id,$ptype){
		ContactInfo::__construct($id,"Emails",$ptype);
		Root::setRelationships(array('EmailsParent'=>new Relationship($this->_ptype,"Parent")));
		$this->address = NULL;
	}
	public function initMysql($row){ 
		ContactInfo::initMysql($row);
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
	public function db_unique($con){
		$this->msqlEsc();
		$sql = "SELECT * FROM `Emails` WHERE `Address`=\"".$this->getAddress()."\"";
		if($this->getID() != 0){ $sql .= " AND ID !=\"".$this->getID()."\""; }
		$res = mysqli_query($con,$sql);
		if(mysql_num_rows($res) == 0){ return true; }else{ return false; }
	}
	protected function mysqlEsc($con){
		ContactInfo::mysqlEsc($con);
		$this->setAddress(mysql_escape_string($con,$this->getAddress()));
	}

	private function getAddress(){ return (string)$this->address; }
	private function setAddress($a){ $this->address = (string)$a; }
}

?>