<?php
require_once("root.class.php");

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
	public function init($row){ 
		Root::init($row);
		if(isset($row['First'])){ $this->setFirst($row['First']); }
		if(isset($row['Last'])){ $this->setLast($row['Last']); }
		if(isset($row['BDay'])){ $this->setBDay($row['BDay']); }
	}
	public function toArray(){
		$p = Root::toArray();
		$p['First'] = $this->getFirst();
		$p['Last'] = $this->getLast();
		$p['Bday'] = $this->getBday("Y-m-d");
		return $p;
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

?>