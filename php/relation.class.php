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
/*	public function init($id,$cd,$ud,$rid,$kid,$code,$def){
		DBObj::init($id,"Relations",$cd,$ud);
		$this->setRID($rid);
		$this->setKID($kid);
		$this->setCode($code);
		$this->setDefinition($def);
	}*/
	public function init($row){ 
		DBObj::initMysql($row);
		if(isset($row['RID'])){ $this->setRID($row['RID']); }
		if(isset($row['KID'])){ $this->setKID($row['KID']); }
		if(isset($row['Code'])){ $this->setCode($row['Code']); }
		if(isset($row['Definition'])){ $this->setDefinition($row['Definition']); }
	}
	/*protected function mysqlEsc($pdo){
		DBObj::mysqlEsc($pdo);
		$this->setRID(mysqli_escape_string($pdo,$this->getRID()));
		$this->setKID(mysqli_escape_string($pdo,$this->getKID()));
		$this->setCode(mysqli_escape_string($pdo,$this->getCode()));
		$this->setDefinition(mysqli_escape_string($pdo,$this->getDefinition()));
	}*/
	public function toArray(){
		$p = DBObj::toArray();
		$p['RID'] = $this->getRID();
		$p['KID'] = $this->getKID();
		$p['Code'] = $this->getCode();
		$p['Definition'] = $this->getDefinition();
		return $p;
	}

	protected function db_select($pdo){
		/*$this->mysqlEsc($pdo);*/
		$sql = "SELECT * FROM `Relationships` WHERE `ID`=:ID";
		try{
			$stmt = $pdo->prepare($sql);
			$stmt->execute(["ID"=>$this->getID()]);
		}
		catch(PDOException $e){
			error_log("SQL Relation->Select: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());
			return false;
		}
		return $stmt;
	}
	protected function db_insert($pdo){
		//$this->mysqlEsc($pdo);
		$time = time();
		$sql = "INSERT INTO `Relations` (`ID`,`RID`,`KID`,`Created`,`Updated`) VALUES (NULL,:RID,:KID,:Created,:Updated)";
		//error_log("SQL Relation->Insert: ".$sql);
		try{
			$stmt = $pdo->prepare($sql);
			$stmt->execute(["RID"=>$this->getRID(),"KID"=>$this->getKID(),"Created"=>$time,"Updated"=>$time]);
			$this->setID($pdo->lastInsertId());
			$this->setCreated($time);
			$this->setUpdated($time);
		}
		catch(PDOException $e){
			error_log("SQL Relation->Insert: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());
			return false;
		}
		return $stmt;
	}
	protected function db_update($pdo){
		//$this->mysqlEsc($pdo);
		$time = time();
		$sql = "UPDATE `Relations` SET `RID`=:RID,`KID`=:KID,`Updated`=:Updated WHERE `ID`=:ID";
		try{
			$stmt = $pdo->prepare($sql);
			$stmt->execute(["ID"=>$this->getID(),"RID"=>$this->getRID(),"KID"=>$this->getKID(),"Updated"=>$time]);
		}
		catch(PDOException $e){
			error_log("SQL DBObj->Update: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());
			return false;
		}
		return $stmt;
	}
	protected function db_delete($pdo){
		//$this->mysqlEsc($pdo);
		$sql = "DELETE FROM `Relations` WHERE `ID`=:ID";
		try{
			$stmt = $pdo->prepare($sql);
			$stmt->execute(["ID"=>$this->getID()]);
		}
		catch(PDOException $e){
			error_log("SQL DBObj->Update: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());
			return false;
		}
		return $stmt;
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