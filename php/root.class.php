<?php
require_once("dbobj.class.php");

class Root extends DBObj{
	private $relationships;

	public function __construct($id,$t){
		DBObj::__construct($id,$t);
		$this->relationships = array();
	}
	public function dbRead($con){
		if(DBObj::dbRead($con)){
			foreach($this->getRelationships() as $key => $rel){ $this->setRelation($this->getTable(),$key,$con); }
			return true;
		}else{ return false; }
	}
	public function dbWrite($con){
		if(DBObj::dbWrite($con)){
			$a = $this->getRelationships();
			$succ = true;
			foreach($a as $k => $r){ 
				if($k == "Parent"){ $ra = $r->toArray(); if($ra[0]['ID'] == 0){ $this->setParentID($con,$ra[0]['RID']); } }
				$r->setRelRID($this->getID());
				$succ = $r->dbWrite($con); 
				if(!$succ){break;}
			}
			return $succ;
		}else{ return false; }
	}
	public function dbDelete($con){
		$a = $this->getRelationships();
		foreach($a as $k => $r){ 
			$r->setRelRID($this->getID());
			$succ = $r->dbDelete($con);
			if(!$succ){break;}
		}
		if($succ){ return DBObj::dbDelete($con); }else{ error_log("Root->Delete: MYSQL ERROR: ".mysqli_error($con)); return false; }
	}
	protected function mysqlEsc($con){
		DBObj::mysqlEsc($con);

	}
	public function toArray(){
		$p = DBObj::toArray();
		$p['Rels'] = array();
		$a = $this->getRelationships();
		if(count($a) > 0){ foreach($a as $k => $v){ $p['Rels'][$k] = $v->toArray(); } }
		return $p;
	}
	public function view($html,$ds = "F j, Y, g:i a"){
		$html = DBObj::view($html,$ds);
		return $html;
	}
	protected function viewRel($key,$html,$ds = "F j, Y, g:i a"){ 
		if(!isset($this->relationships[$key])){ return false; }
		else{
			$html_out = "";
			$a = $this->toArray();
			for($i = 0; $i < count($a['Rels'][$key]); $i++){ 
				$c = $a['Rels'][$key][$i];
				$s = DBObj::view($html,$ds);
				$s = str_replace("{RID}",$c['RID'],$s);
				$s = str_replace("{KID}",$c['KID'],$s);
				$s = str_replace("{Code}",$c['Code'],$s);
				$s = str_replace("{Definition}",$c['Definition'],$s);
				$html_out .= $s;
			}
			return $html_out;
		}
	}
	public function setParentRel($r){ $rels = $this->getRelationships(); $rels['Parent']->setRel($r); $this->setRelationships($rels); }

	protected function getRelationships(){ return $this->relationships; }
	public function getRelation($key){ if(isset($this->relationships[$key])){ return $this->relationships[$key]; }else{ return false; } }

	protected function setParentID($con,$pid){ $sql = "UPDATE ".$this->getTable()." SET PID=".$pid." WHERE DBO_ID=".$this->getID(); return mysqli_query($con,$sql); }
	protected function setRelationships($rel){ if(is_array($rel)){ $this->relationships = $rel; return TRUE; }else{ return FALSE; } }
	protected function setRelation($root,$key,$con){
		$this->relationships[$key] = new Relationship($root,$key);
		$this->relationships[$key]->setRels($con,$this->getID());
	}
}

?>