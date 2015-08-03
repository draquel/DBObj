<?php

	class ContactInfo extends DBObj{
		private $name;
		private $cid;
		private $primary;

		public function __construct(){
			DBObj::__construct();
			$this->name = NULL;
			$this->cid = NULL;
			$this->primary = NULL;
		}
		public function init($id,$cd,$ud,$n,$cid,$p){
			DBObj::init($id,$cd,$ud);
			$this->setName($n);
			$this->setCID($cid);
			$this->setPrimary($p);
		}
		public function initMysql($row){
			DBObj::initMysql($row);
			$this->setName($row['Name']);
			$this->setCID($row['CID']);
			$this->setPrimary($row['Primary']);
		}
		public function toArray(){
			$p = DBObj::toArray();
			$p['Name'] = $this->getName();
			$p['CID'] = $this->getCID();
			$p['Primary'] = $this->getPrimary();
			return $p;
		}

		protected function mysqlEsc(){
			DBObj::mysqlEsc();
			$this->setName(mysql_escape_string($this->getName()));
			$this->setCID(mysql_escape_string($this->getCID()));
			$this->setPrimary(mysql_escape_string($this->getPrimary()));
		}
		protected function getName(){ return $this->name; }
		public function getCID(){ return (int)$this->cid; }
		protected function getPrimary(){ return (int)$this->primary; }
		protected function setName($n){ $this->name = $n; }
		public function setCID($id){ $this->cid = (int)$id; }
		protected function setPrimary($p){ $this->primary = (int)$p; }
	}

	class Address extends ContactInfo{
		private $address;
		private $address2;
		private $city;
		private $state;
		private $zip;
		
		public function __construct(){
			ContactInfo::__construct();
			$this->address = NULL;
			$this->address2 = NULL;
			$this->city = NULL;
			$this->state = NULL;
			$this->zip = NULL;
		}
		public function init($id,$cd,$ud,$n,$cid,$p,$ad,$ad2,$c,$s,$z){
			ContactInfo::init($id,$cd,$ud,$n,$cid,$p);
			$this->setAddress($ad);
			$this->setAddress2($ad2);
			$this->setCity($c);
			$this->setState($s);
			$this->setZip($z);
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
		protected function db_select($con){
                        $this->mysqlEsc();
                        $sql ="SELECT * FROM `Addresses` WHERE `ID`=\"".$this->getID()."\"";
                        return mysql_query($sql,$con);
                }
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Addresses` (`ID`,`CID`,`Name`,`Address`,`Address2`,`City`,`State`,`Zip`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getCID()."\",\"".$this->getName()."\",\"".$this->getAddress()."\",\"".$this->getAddress2()."\",\"".$this->getCity()."\",\"".$this->getState()."\",\"".$this->getZip()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
			$this->setID(mysql_insert_id($con));
			return mysql_query($sql,$con);
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql ="UPDATE `Addresses` SET `CID`=\"".$this->getCID()."\",`Name`=\"".$this->getName()."\",`Address`=\"".$this->getAddress2()."\",`Address2`=\"".$this->getAddress2()."\",`City`=\"".$this->getCity()."\",`State`=\"".$this->getState()."\",`Zip`=\"".$this->getZip()."\",`Primary`=\"".$this->getPrimary()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Addresses` WHERE `ID`=".$this->getID();
			return mysql_query($con);
		}
		protected function mysqlEsc(){
			ContactInfo::mysqlEsc();
                        $this->setAddress(mysql_escape_string($this->getAddress()));
                        $this->setAddress2(mysql_escape_string($this->getAddress2()));
                        $this->setCity(mysql_escape_string($this->getCity()));
                        $this->setState(mysql_escape_string($this->getState()));
                        $this->setZip(mysql_escape_string($this->getZip()));
		}
		private function setAddress($address){ $this->address = $address; }
		private function setAddress2($address2){ $this->address2 = $address2; }
		private function setCity($city){ $this->city = $city; }
		private function setState($state){ $this->state = $state; }
		private function setZip($zip){ $this->zip = $zip; }

		private function getAddress(){ return $this->address; }
		private function getAddress2(){ return $this->address2; }
		private function getCity(){ return $this->city; }
		private function getState(){ return $this->state; }
		private function getZip(){ return $this->zip; }
	}

	class Phone extends ContactInfo{
		private $region;
		private $area;
		private $num;
		private $ext;

		public function __construct(){
			$this->region = NULL;
			$this->area = NULL;
			$this->num = NULL;
			$this->ext = NULL;
		}
		public function init($id,$cd,$ud,$n,$cid,$p,$r,$a,$num,$e){
			ContactInfo::init($id,$cd,$ud,$n,$cid,$p);
			$this->setRegion($r);
			$this->setArea($a);
			$this->setNumber($num);
			$this->setExtention($e);
		}
		public function initMysql($row){ $this->init($row['ID'],$row['Created'],$row['Updated'],$row['Name'],$row['CID'],$row['Primary'],$row['Region'],$row['Area'],$row['Number'],$row['Ext']); }
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

		protected function db_select($con){
                        $this->mysqlEsc();
                        $sql = "SELECT * FROM `Phones` WHERE `ID`=\"".$this->getID()."\"";
                        return mysql_query($sql,$con);
                }
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Phones` (`ID`,`CID`,`Region`,`Area`,`Number`,`Name`,`Ext`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getCID()."\",\"".$this->getRegion()."\",\"".$this->getArea()."\",\"".$this->getNumber()."\",\"".$this->getName()."\",\"".$this->getExtention()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
			$res = mysql_query($sql,$con);
			$this->setID(mysql_insert_id($con));
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Phones` SET `CID`=\"".$this->getCID()."\",`Region`=\"".$this->getRegion()."\",`Area`=\"".$this->getArea()."\",`Number`=\"".$this->getNumber()."\",`Name`=\"".$this->getName()."\",`Ext`=\"".$this->getExtention()."\",`Primary`=\"".$this->getPrimary()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Phones` WHERE `ID`=".$this->getID();
		}
		protected function mysqlEsc(){
			ContactInfo::mysqlEsc();
                        $this->setRegion(mysql_escape_string($this->getRegion()));
                        $this->setArea(mysql_escape_string($this->getArea()));
                        $this->setNumber(mysql_escape_string($this->getNumber()));
                        $this->setExtention(mysql_escape_string($this->getExtention()));
		}

		private function setRegion($r){ $this->region = $r; }
		private function setArea($a){ $this->area = $a; }
		private function setNumber($n){ $this->number = $n; }
		Private function setExtention($e){ $this->ext = $e; }
		private function getRegion(){ return $this->region; }
		private function getArea(){ return $this->area; }
		private function getNumber(){ return $this->number; }
		private function getExtention(){ return $this->ext; }
	}

	class Email extends ContactInfo{
		private $address;

		public function __construct(){
			ContactInfo::__construct();
			$this->address = NULL;
		}
		public function init($id,$cd,$ud,$n,$cid,$p,$a){
			ContactInfo::init($id,$cd,$ud,$n,$cid,$p);
			$this->setAddress($a);
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
			$res = mysql_query($sql,$con);
			if(mysql_num_rows($res) == 0){ return true; }else{ return false; }
		}
		protected function db_select($con){
                        $this->mysqlEsc();
                        $sql = "SELECT * FROM `Emails` WHERE `ID`=\"".$this->getID()."\"";
                        return mysql_query($sql,$con);
                }
                protected function db_insert($con){
                        $this->mysqlEsc();
                        $sql = "INSERT INTO `Emails` (`ID`,`CID`,`Name`,`Address`,`Primary`,`Created`,`Updated`) VALUES (NULL,\"".$this->getCID()."\",\"".$this->getName()."\",\"".$this->getAddress()."\",\"".$this->getPrimary()."\",\"".time()."\",\"".time()."\")";
                        $res = mysql_query($sql,$con);
                        $this->setID(mysql_insert_id($con));
                        return $res;
                }
                protected function db_update($con){
                        $this->mysqlEsc();
                        $sql = "UPDATE `Emails` SET `CID`=\"".$this->getCID()."\",`Address`=\"".$this->getAddress()."\",`Primary`=\"".$this->getPrimary()."\",`Name`=\"".$this->getName()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
                        return mysql_query($sql,$con);
                }
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Emails` WHERE `ID`=".$this->getID();
		}
		protected function mysqlEsc(){
			ContactInfo::mysqlEsc();
			$this->setAddress(mysql_escape_string($this->getAddress()));
		}

		private function getAddress(){ return $this->address; }
		private function setAddress($a){ $this->address = $a; }
	}
?>
