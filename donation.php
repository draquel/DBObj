<?php
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
