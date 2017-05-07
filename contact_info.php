<?php

	class ContactInfo extends Root{
		protected $name;
		protected $pid;
		protected $primary;
		protected $_ptype;

		public function __construct($id,$t,$ptype){
			DBObj::__construct($id,$t);
			$this->name = NULL;
			$this->pid = NULL;
			$this->primary = NULL;
		}
		public function initMysql($row){
			Root::initMysql($row);
			if(isset($row['Name'])){ $this->setName($row['Name']); }
			if(isset($row['PID'])){ $this->setPID($row['PID']); }
			if(isset($row['Primary'])){ $this->setPrimary($row['Primary']); }
		}
		public function toArray(){
			$p = Root::toArray();
			$p['Name'] = $this->getName();
			$p['PID'] = $this->getPID();
			$p['Primary'] = $this->getPrimary();
			return $p;
		}
		protected function mysqlEsc($con){
			Root::mysqlEsc($con);
			$this->setName(mysql_escape_string($con,$this->getName()));
			$this->setPID(mysql_escape_string($con,$this->getPID()));
			$this->setPrimary(mysql_escape_string($con,$this->getPrimary()));
		}
		protected function getName(){ return (string)$this->name; }
		public function getPID(){ return (int)$this->pid; }
		protected function getPrimary(){ return (int)$this->primary; }
		protected function setName($n){ $this->name = (string)$n; }
		public function setPID($id){ $this->pid = (int)$id; }
		protected function setPrimary($p){ $this->primary = (int)$p; }
	}

	class Address extends ContactInfo{
		private $address;
		private $address2;
		private $city;
		private $state;
		private $zip;
		
		public function __construct($id,$ptype){
			ContactInfo::__construct($id,"Addresses",$ptype);
			Root::setRelationships(array('Parent'=>new Relationship($ptype,"Parent")));
			$this->address = NULL;
			$this->address2 = NULL;
			$this->city = NULL;
			$this->state = NULL;
			$this->zip = NULL;
		}
		public function initMysql($row){
			ContactInfo::initMysql($row);
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

	class Phone extends ContactInfo{
		private $region;
		private $area;
		private $num;
		private $ext;

		public function __construct($id,$ptype){
			ContactInfo::__construct($id,"Phones",$ptype);
			Root::setRelationships(array('Parent'=>new Relationship($ptype,"Parent")));
			$this->region = NULL;
			$this->area = NULL;
			$this->num = NULL;
			$this->ext = NULL;
		}
		public function initMysql($row){ 
			ContactInfo::initMysql($row); 
			$this->setRegion($row['Region']); 
			$this->setArea($row['Area']); 
			$this->setNumber($row['Number']); 
			$this->setExtention($row['Ext']); 
		}
		public function sameAs($oa){
			$same = true;
			if($oa['Region'] != $this->getRegion()){ $same = false;}
			if($oa['Area'] != $this->getArea()){ $same = false; }
			if($oa['Number'] != str_replace(".","",str_replace("-","",$this->getNumber()))){ $same = false; }
			if($oa['Ext'] != $this->getExtention()){ $same = false; }
			return $same;
		}
		public function toArray(){
			$p = ContactInfo::toArray();
			$p['Region'] = $this->getRegion();
			$p['Area'] = $this->getArea();
			$p['Number'] = $this->getNumber();
			$p['Ext'] = $this->getExtention();
			return $p;
		}
		protected function mysqlEsc($con){
			ContactInfo::mysqlEsc($con);
			$this->setRegion(mysql_escape_string($con,$this->getRegion()));
			$this->setArea(mysql_escape_string($con,$this->getArea()));
			$this->setNumber(mysql_escape_string($con,$this->getNumber()));
			$this->setExtention(mysql_escape_string($con,$this->getExtention()));
		}

		private function setRegion($r){ $this->region = (string)$r; }
		private function setArea($a){ $this->area = (string)$a; }
		private function setNumber($n){ $this->num = (string)$n; }
		private function setExtention($e){ $this->ext = (string)$e; }
		private function getRegion(){ return (string)$this->region; }
		private function getArea(){ return (string)$this->area; }
		private function getNumber(){ return (string)$this->num; }
		private function getExtention(){ return (string)$this->ext; }
	}

	class Email extends ContactInfo{
		private $address;

		public function __construct($id,$ptype){
			ContactInfo::__construct($id,"Emails",$ptype);
			Root::setRelationships(array('EmailsParent'=>new Relationship($ptype,"Parent")));
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
		private function setAddress($a){ (string)$this->address = $a; }
	}
?>
