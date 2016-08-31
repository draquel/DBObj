<?php

	class Person extends Root{
		protected $first;
        protected $last;
		protected $bday;

		public function __construct($id,$t){
			Root::__construct($id,$t);	
			$this->first = NULL;
			$this->last = NULL;
			$this->bday = NULL;
		}
/*		public function init($id,$f,$l,$bd,$cd,$ud){
			Root::init($id,$cd,$ud);
			$this->setFirst($f);
			$this->setLast($l);
			$this->setBDay($bd);
		}*/
		public function initMysql($row){ 
			Root::initMysql($row);
			$this->setFirst($row['First']);
			$this->setLast($row['Last']);
			$this->setBDay($row['BDay']);
		}
		public function toArray(){
			$p = Root::toArray();
			$p['First'] = $this->getFirst();
			$p['Last'] = $this->getLast();
			$p['Bday'] = $this->getBday("Y-m-d");
			return $p;
		}
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Persons` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Persons` (`ID`,`First`,`Last`,`BDay`,`Created`,`Updated`) VALUES (NULL,\"".$this->getFirst()."\",\"".$this->getLast()."\",\"".$this->getBDay(NULL)."\",\"".time()."\",\"".time()."\")";
			$res = mysqli_query($con,$sql);
			if($res){ $this->setID(mysqli_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Persons` SET `First`=\"".$this->getFirst()."\",`Last`=\"".$this->getLast()."\",`BDay`=\"".$this->getBDay(NULL)."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}
		protected function mysqlEsc($con){
			Root::mysqlEsc($con);
			$this->setFirst(mysqli_escape_string($con,$this->getFirst()));
			$this->setLast(mysqli_escape_string($con,$this->getLast()));
			$this->setBDay(mysqli_escape_string($con,$this->getBDay(NULL)));
		}
		protected function getFirst(){ return (string)$this->first; }
		protected function getLast(){ return (string)$this->last; }
		protected function getBDay($ds){ if(isset($ds) && $ds != NULL){ return (string)date($ds,$this->bday); }else{ return (int)$this->bday; } }
		protected function setFirst($first){ $this->first = (string)$first; }
		protected function setLast($last){ $this->last = (string)$last; }
		protected function setBDay($bd){ $this->bday = (int)$bd; }
	}

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
/*		public function init($id,$f,$l,$bd,$cd,$ud){
			Person::init($id,$f,$l,$bd,$cd,$ud);
		}*/
		public function initMysql($row){
			Person::initMysql($row);
			if(isset($row['Phones']) && $row['Phones'] != NULL){
				$ph = explode(";",$row['Phones']);
				$this->phones = new DLList();
				for($i = 0; $i < count($ph); $i += 1){
					$p = explode(":",$ph[$i]);
					for($j = 0; $j < count($p); $j += 1){ if(!isset($p[$j])){ $p[$j] = NULL;} }
					$po = new Phone(NULL);
					//$po->init($p[0],$p[1],$p[2],$p[3],$p[4],$p[5],$p[6],$p[7],$p[8],$p[9]);
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
					$eo = new Email(NULL);
					//$eo->init($e[0],$e[1],$e[2],$e[3],$e[4],$e[5],$e[6]);
					$eo->initMysql(array("ID"=>$e[0],"Created"=>$e[1],"Updated"=>$e[2],"Name"=>$e[3],"PID"=>$e[4],"Primary"=>$e[5],"Address"=>$e[6]));
					$this->getEmails()->insertLast($eo);
                }
			}
			if(isset($row['Addresses']) && $row['Addresses'] != NULL){
				$ad = explode(";",$row['Addresses']);
				$this->addresses = new DLList();
				for($i = 0; $i < count($ad); $i += 1){
                	$a = explode(":",$ad[$i]);
					for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} }
					$ao = new Address(NULL);
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
				$this->setContactInfoCID();
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
/*		protected function db_select($con){
			$this->mysqlEsc();
			$sql = "SELECT * FROM `Contact_Data` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);	
		}*/
		protected function db_insert($con){
			$this->mysqlEsc();
			$sql = "INSERT INTO `Contacts` (`ID`,`First`,`Last`,`BDay`,`Created`,`Updated`) VALUES (NULL,\"".$this->getFirst()."\",\"".$this->getLast()."\",\"".$this->getBDay(NULL)."\",\"".$time()."\",\"".$time()."\")";
			$res = mysqli_query($con,$sql);
			if($res){ $this->setID(mysqli_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$sql = "UPDATE `Contacts` SET `First`=\"".$this->getFirst()."\",`Last`=\"".$this->getLast()."\",`BDay`=\"".$this->getBDay(NULL)."\",`Updated`=\"".time()."\" WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
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
			$sql = "SELECT a.* FROM Addresses a LEFT JOIN Relationships r ON a.ID = r.RID AND r.Key = 'AddressesParent' WHERE a.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$a = new Address(NULL);
				$a->initMysql($row);
				$this->addresses->insertLast($a);
			}
		}
		protected function setPhones($con){
			$this->phones = new DLList();
			$sql = "SELECT p.* FROM Phones p LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'PhonesParent' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$p = new Phone(NULL);
				$p->initMysql($row);
				$this->phones->insertLast($p);
			}
		}
		protected function setEmails($con){
			$this->emails = new DLList();
			$sql = "SELECT e.* FROM Emails e LEFT JOIN Relationships r ON e.ID = r.RID AND r.Key = 'EmailsParent' WHERE e.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."'";
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$p = new Email(NULL);
				$p->initMysql($row);
				$this->emails->insertLast($p);
			}
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
