<?php
require_once("root.class.php");

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
		$this->_ptype = $ptype;
	}
	public function init($row){
		Root::init($row);
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

?>