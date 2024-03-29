<?php

	class Contact_BG extends Contact{
		protected $company;
		protected $title;
		protected $state_org;
		protected $state_title;
		protected $donations;
		
		public function __construct(){
			Contact::__construct();
			Root::setRelationships(array('Codes'=>new Relationship("Contact","Codes"),'Committees'=>new Relationship("Contact","Committees")));
			$this->company = NULL;
			$this->title = NULL;
			$this->state_org = NULL;
			$this->state_title = NULL;
			$this->donations = new DLList();
		}
/*		public function init($id,$cd,$ud,$f,$l,$bd,$c,$t,$so,$st){
			Contact::init($id,$f,$l,$bd,$cd,$ud);
			$this->setCompany($c);
			$this->setTitle($t);
			$this->setStateOrg($so);
			$this->setStateTitle($st);
		}*/
		public function initMysql($row){
			Contact::initMysql($row);
			if(isset($row['Company'])){ $this->setCompany($row['Company']); }
			if(isset($row['Title'])){ $this->setTitle($row['Title']); }
			if(isset($row['State_Org'])){ $this->setStateOrg($row['State_Org']); }
			if(isset($row['State_Title'])){ $this->setStateTitle($row['State_Title']); }
			if(isset($row['Donations']) && $row['Donations'] != NULL){
				$dn = explode(";",$row['Donations']);
				$this->donations = new DLList();
				for($i = 0; $i < count($dn); $i += 1){
					$d = explode(":",$dn[$i]);
					for($j = 0; $j < count($d); $j += 1){ if(!isset($d[$j])){ $d[$j] = NULL;} }
					$do = new Donation();
					$do->init($d[0],$d[1],$d[2],$d[3],$d[4],$d[5]);
					$this->getDonations()->insertLast($do);
				}
			}
		}
		public function toArray(){
			$p = Contact::toArray();
			$p['Company'] = $this->getCompany();
			$p['Title'] = $this->getTitle();
			$p['State_Org'] = $this->getStateOrg();
			$p['State_Title'] = $this->getStateTitle();
			$p['Codes'] = array();
			$g = $this->getCodes()->getFirstNode();
			for($i = 0; $i < $this->getCodes()->size(); $i += 1){
				$ar = $g->readNode()->toArray();
				$p['Codes'][$i] = $ar;
				$g = $g->getNext();
			}
			$p['Committees'] = array();
			$g = $this->getCommittees()->getFirstNode();
			for($i = 0; $i < $this->getCommittees()->size(); $i += 1){
				$ar = $g->readNode()->toArray();
				$p['Committees'][$i] = $ar;
				$g = $g->getNext();
			}
			
			$p['Donations'] = array();
			$dn = $this->getDonations()->getFirstNode();
			while($dn != NULL){
				$d = $dn->readNode()->toArray();
				$p['Donations'][] = $d;
				$dn = $dn->getNext();
			}
			return $p;
		}
		public function dbWrite($con){
			if(Contact::dbWrite($con)){
		
			}
		}
		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Contact_Data` WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Contacts` (`ID`,`First`,`Last`,`BDay`,`Company`,`Title`,`State_Org`,`State_Title`,`Created`,`Updated`) VALUES (NULL,\"".$this->getFirst()."\",\"".$this->getLast()."\",\"".$this->getBDay(NULL)."\",\"".$this->getCompany()."\",\"".$this->getTitle()."\",\"".$this->getStateOrg()."\",\"".$this->getStateTitle()."\",\"".time()."\",\"".time()."\")";
			$res = mysql_query($sql,$con);
			if($res){ $this->setID(mysql_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Contacts` SET `First`=\"".$this->getFirst()."\",`Last`=\"".$this->getLast()."\",`BDay`=\"".$this->getBDay(NULL)."\",`Company`=\"".$this->getCompany()."\",`Title`=\"".$this->getTitle()."\",`State_Org`=\"".$this->getStateOrg()."\",`State_Title`=\"".$this->getStateTitle()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function mysqlEsc(){
			Contact::mysqlEsc();
			$this->setCompany(mysql_escape_string($this->getCompany()));
			$this->setTitle(mysql_escape_string($this->getTitle()));
			$this->setStateOrg(mysql_escape_string($this->getStateOrg()));
			$this->setStateTitle(mysql_escape_string($this->getStateTitle()));
		}
		
		protected function setStateOrg($so){ $this->state_org = $so; }
		protected function setStateTitle($st){ $this->state_title = $st; }
		public function setCodes($con){ Root::setRelation("Contact","Codes",$con); }
		public function setCommittees($con){ Root::setRelation("Contact","Committees",$con); }
		public function setDonations($con){
			$this->donations = new DLList();
			$sql = "SELECT * FROM Donations WHERE CID=\"".$this->getID()."\"";
			$res = mysql_query($sql,$con);
			while($row = mysql_fetch_array($res)){
				$d = new Donation();
				$d->initMysql($row);
				$this->donations->insertLast($d);
			}
		}
		
		protected function setTitle($title){ $this->title = $title; }
		protected function setCompany($company){ $this->company = $company; }
		protected function setComp($company,$title){ $this->setTitle($title); $this->setCompany($company); }
		protected function getTitle(){ return $this->title; }
		protected function getCompany(){ return $this->company; }
		protected function getStateOrg(){ return $this->state_org; }
		protected function getStateTitle(){ return $this->state_title; }
		public function getCodes(){ $rels = Root::getRelationships(); return $rels['Codes']->getRels(); }
		public function getCommittees(){ $rels = Root::getRelationships(); return $rels['Committees']->getRels(); }
		public function getDonations(){ return $this->donations; }
	}

	class Donation extends DBObj{
		private $cid;
		private $date;
		private $amount;
		
		public function __construct(){
			DBObj::__construct();
			$this->cid = NULL;
			$this->date = NULL;
			$this->amount = NULL;
		}
		public function init($id,$cd,$ud,$cid,$d,$a){
			DBObj::init($id,$cd,$ud);
			$this->setCID($cid);
			$this->setDate($d);
			$this->setAmount($a);
		}
		public function initMysql($row){ $this->init($row['ID'],$row['Created'],$row['Updated'],$row['CID'],$row['Date'],$row['Amount']); }
		public function toArray(){
			$p = DBObj::toArray();
			$p['CID'] = $this->getCID();
			$p['Date'] = $this->getDate();
			$p['Amount'] = $this->getAmount();
			return $p;
		}
		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Donations` WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		public function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Donations` (`ID`,`CID`,`Date`,`Amount`,`Created`,`Updated`) VALUES (NULL,\"".$this->getCID()."\",\"".$this->getDate()."\",\"".$this->getAmount()."\",\"".time()."\",\"".time()."\")";
			$res = mysql_query($sql,$con);
			$this->setID(mysql_insert_id($con));
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Donations` SET `CID`=\"".$this->getCID()."\",`Date`=\"".$this->getDate(NULL)."\",`Amount`=\"".$this->getAmount()."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysql_query($sql,$con);
		}
		protected function mysqlEsc(){
            DBObj::mysqlEsc();
			$this->setCID(mysql_escape_string($this->getCID()));
			$this->setDate(mysql_escape_string($this->getDate()));
			$this->setAmount(mysql_escape_string($this->getAmount()));
		}
		private function setCID($id){ $this->cid = $id; }
		private function setDate($d){ $this->date = $d; }
		private function setAmount($a){ $this->amount = $a; }

		private function getCID(){ return $this->cid; }
		private function getDate(){ return $this->date; }
		private function getAmount(){ return $this->amount; }
	}
?>
