<?php
require_once("dbobj.class.php");

class Root extends DBObj{
	private $relationships;

	public function __construct($id,$t){
		DBObj::__construct($id,$t);
		$this->relationships = array();
	}
	public function dbRead($pdo){
		if(DBObj::dbRead($pdo)){
			foreach($this->getRelationships() as $key => $rel){ $this->setRelation($this->getTable(),$key,$pdo); }
			return true;
		}else{ return false; }
	}
	public function dbWrite($pdo){
		if(DBObj::dbWrite($pdo)){
			$a = $this->getRelationships();
			$succ = true;
			foreach($a as $k => $r){ 
				if($k == "Parent"){ $ra = $r->toArray(); if($ra[0]['ID'] == 0){ $this->setParentID($pdo,$ra[0]['RID']); } }
				$r->setRelRID($this->getID());
				$succ = $r->dbWrite($pdo); 
				if(!$succ){break;}
			}
			return $succ;
		}else{ return false; }
	}
	public function dbDelete($pdo){
		$a = $this->getRelationships();
		foreach($a as $k => $r){ 
			$r->setRelRID($this->getID());
			$succ = $r->dbDelete($pdo);
			if(!$succ){break;}
		}
		if($succ){ return DBObj::dbDelete($pdo); }else{ error_log("Root->Delete: SQL ERROR"); return false; }
	}
	protected function mysqlEsc($pdo){
		DBObj::mysqlEsc($pdo);

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

	protected function setParentID($pdo,$pid){ 
		$sql = "UPDATE ".$this->getTable()." SET PID=:PID WHERE DBO_ID=:ID";
		try{ $stmt = $pdo->prepare($sql); $stmt->execute(["ID"=>$this->getID(),"PID"=>$pid]); }
		catch(PDOException $e){ error_log("SQL Root->setParentID: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false;	}
		return true; 
	}
	protected function setRelationships($rel){ if(is_array($rel)){ $this->relationships = $rel; return TRUE; }else{ return FALSE; } }
	protected function setRelation($root,$key,$pdo){
		$this->relationships[$key] = new Relationship($root,$key);
		$this->relationships[$key]->setRels($pdo,$this->getID());
	}
}

?>