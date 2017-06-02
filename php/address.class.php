<?php
require_once("contactInfo.class.php");
require_once("relationship.class.php");

class Address extends ContactInfo{
	private $address;
	private $address2;
	private $city;
	private $state;
	private $zip;

	public function __construct($id,$ptype){
		ContactInfo::__construct($id,"Addresses",$ptype);
		Root::setRelationships(array('Parent'=>new Relationship($this->_ptype,"Parent")));
		$this->address = NULL;
		$this->address2 = NULL;
		$this->city = NULL;
		$this->state = NULL;
		$this->zip = NULL;
	}
	public function init($row){
		ContactInfo::init($row);
		$this->setAddress($row['Address']);
		$this->setAddress2($row['Address2']);
		$this->setCity($row['City']);
		$this->setState($row['State']);
		$this->setZip($row['Zip']);
	}
	public function sameAs($oa){
		$same = true;
		if(strtolower($oa['Address']) != strtolower($this->getAddress())){ $same = false;}
		if(strtolower($oa['Address2']) != strtolower($this->getAddress2())){ $same = false; }
		if(strtolower($oa['City']) != strtolower($this->getCity())){ $same = false; }
		if(strtolower($oa['State']) != strtolower($this->getState())){ $same = false; }
		if(strtolower($oa['Zip']) != strtolower($this->getZip())){ $same = false; }
		return $same;
	}
	public function toArray(){
		$p = ContactInfo::toArray();
		$p['Address'] = $this->getAddress();
		$p['Address2'] = $this->getAddress2();
		$p['City'] = $this->getCity();
		$p['State'] = $this->getState();
		$p['Zip'] = $this->getZip();
		return $p;
	}
	protected function mysqlEsc($con){
		ContactInfo::mysqlEsc($con);
		$this->setAddress(mysql_escape_string($con,$this->getAddress()));
		$this->setAddress2(mysql_escape_string($con,$this->getAddress2()));
		$this->setCity(mysql_escape_string($con,$this->getCity()));
		$this->setState(mysql_escape_string($con,$this->getState()));
		$this->setZip(mysql_escape_string($con,$this->getZip()));
	}

	private function setAddress($address){ $this->address = (string)$address; }
	private function setAddress2($address2){ $this->address2 = (string)$address2; }
	private function setCity($city){ $this->city = (string)$city; }
	private function setState($state){ $this->state = (string)$state; }
	private function setZip($zip){ $this->zip = (string)$zip; }

	private function getAddress(){ return (string)$this->address; }
	private function getAddress2(){ return (string)$this->address2; }
	private function getCity(){ return (string)$this->city; }
	private function getState(){ return (string)$this->state; }
	private function getZip(){ return (string)$this->zip; }
}

?>