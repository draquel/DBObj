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
	public function init($row){ 
		DBObj::init($row);
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