<?php

	class ContactInfo extends Root{
		private $name;
		private $pid;
		private $primary;

		public function __construct($id,$t){
			DBObj::__construct($id,$t);
			$this->name = NULL;
			$this->pid = NULL;
			$this->primary = NULL;
		}
/*		public function init($id,$t,$cd,$ud,$n,$cid,$p){
			DBObj::init($id,$t,$cd,$ud);
			$this->setName($n);
			$this->setPID($cid);
			$this->setPrimary($p);
		}*/
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
		
		public function __construct($id){
			ContactInfo::__construct($id,"Addresses");
			Root::setRelationships(array('AddressesParent'=>new Relationship("Address","AddressesParent")));
			$this->address = NULL;
			$this->address2 = NULL;
			$this->city = NULL;
			$this->state = NULL;
			$this->zip = NULL;
		}
/*		public function init($id,$cd,$ud,$n,$cid,$p,$ad,$ad2,$c,$s,$z){
			ContactInfo::init($id,"Addresses",$cd,$ud,$n,$cid,$p);
			$this->setAddress($ad);
			$this->setAddress2($ad2);
			$this->setCity($c);
			$this->setState($s);
			$this->setZip($z);
		}*/
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
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql ="SELECT * FROM `Addresses` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Addresses` (`ID`,`PID`,`Name`,`Address`,`Address2`,`City`,`State`,`Zip`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getPID()."\",\"".$this->getName()."\",\"".$this->getAddress()."\",\"".$this->getAddress2()."\",\"".$this->getCity()."\",\"".$this->getState()."\",\"".$this->getZip()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
			$this->setID(mysql_insert_id($con));
			return mysqli_query($con,$sql);
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql ="UPDATE `Addresses` SET `PID`=\"".$this->getPID()."\",`Name`=\"".$this->getName()."\",`Address`=\"".$this->getAddress()."\",`Address2`=\"".$this->getAddress2()."\",`City`=\"".$this->getCity()."\",`State`=\"".$this->getState()."\",`Zip`=\"".$this->getZip()."\",`Primary`=\"".$this->getPrimary()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Addresses` WHERE `ID`=".$this->getID();
			return mysqli_query($con,$sql);
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

		public function __construct($id){
			ContactInfo::__construct($id,"Phones");
			Root::setRelationships(array('PhonesParent'=>new Relationship("Phone","PhonesParent")));
			$this->region = NULL;
			$this->area = NULL;
			$this->num = NULL;
			$this->ext = NULL;
		}
/*		public function init($id,$cd,$ud,$n,$cid,$p,$r,$a,$num,$e){
			ContactInfo::init($id,"Phones",$cd,$ud,$n,$cid,$p);
			$this->setRegion($r);
			$this->setArea($a);
			$this->setNumber($num);
			$this->setExtention($e);
		}*/
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

/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Phones` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Phones` (`ID`,`PID`,`Region`,`Area`,`Number`,`Name`,`Ext`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getPID()."\",\"".$this->getRegion()."\",\"".$this->getArea()."\",\"".$this->getNumber()."\",\"".$this->getName()."\",\"".$this->getExtention()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
			$res = mysqli_query($con,$sql);
			$this->setID(mysql_insert_id($con));
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Phones` SET `PID`=\"".$this->getPID()."\",`Region`=\"".$this->getRegion()."\",`Area`=\"".$this->getArea()."\",`Number`=\"".$this->getNumber()."\",`Name`=\"".$this->getName()."\",`Ext`=\"".$this->getExtention()."\",`Primary`=\"".$this->getPrimary()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Phones` WHERE `ID`=".$this->getID();
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
		private function setNumber($n){ $this->number = (string)$n; }
		private function setExtention($e){ $this->ext = (string)$e; }
		private function getRegion(){ return (string)$this->region; }
		private function getArea(){ return (string)$this->area; }
		private function getNumber(){ return (string)$this->number; }
		private function getExtention(){ return (string)$this->ext; }
	}

	class Email extends ContactInfo{
		private $address;

		public function __construct($id){
			ContactInfo::__construct($id,"Emails");
			Root::setRelationships(array('EmailsParent'=>new Relationship("Email","EmailsParent")));
			$this->address = NULL;
		}
/*		public function init($id,$cd,$ud,$n,$cid,$p,$a){
			ContactInfo::init($id,"Emails",$cd,$ud,$n,$cid,$p);
			$this->setAddress($a);
		}*/
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
			if($this->getID != 1){ $sql .= " AND ID !=\"".$this->getID()."\""; }
			$res = mysqli_query($con,$sql);
			if(mysql_num_rows($res) == 0){ return true; }else{ return false; }
		}
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Emails` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Emails` (`ID`,`PID`,`Name`,`Address`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getPID()."\",\"".$this->getName()."\",\"".$this->getAddress()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
			$res = mysqli_query($con,$sql);
			$this->setID(mysql_insert_id($con));
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Emails` SET `PID`=\"".$this->getPID()."\",`Address`=\"".$this->getAddress()."\",`Primary`=\"".$this->getPrimary()."\",`Name`=\"".$this->getName()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Emails` WHERE `ID`=".$this->getID();
			return mysqli_query($con,$sql);
		}
		protected function mysqlEsc($con){
			ContactInfo::mysqlEsc($con);
			$this->setAddress(mysql_escape_string($con,$this->getAddress()));
		}

		private function getAddress(){ return (string)$this->address; }
		private function setAddress($a){ (string)$this->address = $a; }
	}
?>
