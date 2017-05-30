<?php
require_once("contactInfo.class.php");
require_once("relationship.class.php");

class Phone extends ContactInfo{
	private $region;
	private $area;
	private $num;
	private $ext;

	public function __construct($id,$ptype){
		ContactInfo::__construct($id,"Phones",$ptype);
		Root::setRelationships(array('Parent'=>new Relationship($this->_ptype,"Parent")));
		$this->region = NULL;
		$this->area = NULL;
		$this->num = NULL;
		$this->ext = NULL;
	}
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
	protected function mysqlEsc($con){
		ContactInfo::mysqlEsc($con);
		$this->setRegion(mysql_escape_string($con,$this->getRegion()));
		$this->setArea(mysql_escape_string($con,$this->getArea()));
		$this->setNumber(mysql_escape_string($con,$this->getNumber()));
		$this->setExtention(mysql_escape_string($con,$this->getExtention()));
	}

	private function setRegion($r){ $this->region = (string)$r; }
	private function setArea($a){ $this->area = (string)$a; }
	private function setNumber($n){ $this->num = (string)$n; }
	private function setExtention($e){ $this->ext = (string)$e; }
	private function getRegion(){ return (string)$this->region; }
	private function getArea(){ return (string)$this->area; }
	private function getNumber(){ return (string)$this->num; }
	private function getExtention(){ return (string)$this->ext; }
}

?>