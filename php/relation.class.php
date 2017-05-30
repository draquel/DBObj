<?php
require_once("dbobj.class.php");

class Relation extends DBObj{
	private $rid;
	private $kid;
	private $code;
	private $definition;

	public function __construct(){
		DBObj::__construct(0,"Relations");
		$this->rid = NULL;
		$this->kid = NULL;
		$this->code = NULL;
		$this->definition = NULL;
	}
/*		public function init($id,$cd,$ud,$rid,$kid,$code,$def){
		DBObj::init($id,"Relations",$cd,$ud);
		$this->setRID($rid);
		$this->setKID($kid);
		$this->setCode($code);
		$this->setDefinition($def);
	}*/
	public function initMysql($row){ 
		DBObj::initMysql($row);
		if(isset($row['RID'])){ $this->setRID($row['RID']); }
		if(isset($row['KID'])){ $this->setKID($row['KID']); }
		if(isset($row['Code'])){ $this->setCode($row['Code']); }
		if(isset($row['Definition'])){ $this->setDefinition($row['Definition']); }
	}
	protected function mysqlEsc($con){
		DBObj::mysqlEsc($con);
		$this->setRID(mysqli_escape_string($con,$this->getRID()));
		$this->setKID(mysqli_escape_string($con,$this->getKID()));
		$this->setCode(mysqli_escape_string($con,$this->getCode()));
		$this->setDefinition(mysqli_escape_string($con,$this->getDefinition()));
	}
	public function toArray(){
		$p = DBObj::toArray();
		$p['RID'] = $this->getRID();
		$p['KID'] = $this->getKID();
		$p['Code'] = $this->getCode();
		$p['Definition'] = $this->getDefinition();
		return $p;
	}

	protected function db_select($con){
		/*$this->mysqlEsc($con);*/
		$sql = "SELECT * FROM `Relationships` WHERE `ID`=\"".$this->getID()."\"";
		return mysqli_query($con,$sql);
	}
	protected function db_insert($con){
		$this->mysqlEsc($con);
		$time = time();
		$sql = "INSERT INTO `Relations` (`ID`,`RID`,`KID`,`Created`,`Updated`) VALUES (NULL,\"".$this->getRID()."\",\"".$this->getKID()."\",\"".$time."\",\"".$time."\")";
		//error_log("SQL Relation->Insert: ".$sql);
		$res = mysqli_query($con,$sql);
		if($res){ 
			$this->setID(mysqli_insert_id($con)); 
			$this->setCreated($time);
			$this->setUpdated($time);
		}else{ error_log("SQL Relation->Insert: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		return $res;
	}
	protected function db_update($con){
		//$this->mysqlEsc($con);
		$time = time();
		$sql = "UPDATE `Relations` SET `RID`=\"".$this->getRID()."\",`KID`=\"".$this->getKID()."\",`Updated`=\"".$time."\" WHERE `ID`=\"".$this->getID()."\"";
		$res = mysqli_query($con,$sql);
		if($res){ $this->setUpdated($time); }else{ error_log("SQL Relation->Update: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		return $res;
	}
	protected function db_delete($con){
		//$this->mysqlEsc($con);
		$sql = "DELETE FROM `Relations` WHERE `ID`=".$this->getID();
		return mysqli_query($con,$sql);
	}
	public function setRID($id){ $this->rid = (int)$id;}
	private function setKID($id){ $this->kid = (int)$id;}
	private function setDefinition($def){ $this->definition = (string)$def; }
	private function setCode($code){ $this->code = (string)$code; }

	private function getRID(){ return (int)$this->rid; }
	private function getKID(){ return (int)$this->kid; }
	private function getDefinition(){ return (string)$this->definition; }
	private function getCode(){ return (string)$this->code; }
}

?>