<?php
require_once("dllist.class.php");
require_once("relation.class.php");

class Relationship{
	private $root;
	private $key;
	private $relations;

	public function __construct($r,$k){
		$this->root = $r;
		$this->key = $k;
		$this->relations = new DLList();
	}
	public function init(/*$row,*/$rels = NULL){
		if(is_array($rels)){
			for($i = 0;$i < count($rels); $i++){
				$r = new Relation();
				$r->init($rels[$i]);
				$this->relations->insertLast($r);
			}
		}
	}
	public function setRel($r){ $this->relations->insertLast($r); }
	public function setRels($pdo,$id){
		$this->relations = new DLList();
		$sql = "SELECT * FROM Relationships WHERE `Key`=:Key AND `RID`=:RID";
		try{ $stmt = $pdo->prepare($sql); $stmt->execute(["Key"=>$this->getKey(),"RID"=>$id]);	}
		catch(PDOException $e){	error_log("SQL DBObj->Delete: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false;	}
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$r = new Relation();
			$r->init($row);
			$this->relations->insertLast($r);
		}
	}
	public function hasRel($kid){
		$rel = $this->relations->getFirstNode();
		$succ = false;
		while($rel != NULL){
			$a = $rel->readNode()->toArray();
			if($a['KID'] == $kid){ $succ = true; break;}
			$rel = $rel->getNext();
		}
		return $succ;
	}
	public function toArray(){
		return $this->getRels()->toArray(); // ONLY USED FUNCTIONALITY OF DBOLIST -> PUSH INTO DLLIST
	}
	public function dbWrite($pdo){
		$rel = $this->relations->getFirstNode();
		$succ = true;
		while($rel != NULL){
			$succ = $rel->readNode()->dbWrite($pdo);
			if(!$succ){break;}
			$rel = $rel->getNext();
		}
		return $succ;
	}
	public function dbDelete($pdo){
		$rel = $this->relations->getFirstNode();
		$succ = true;
		while($rel != NULL){
			$succ = $rel->readNode()->dbDelete($pdo);
			if(!$succ){break;}
			$rel = $rel->getNext();
		}
		return $succ;
	}

	public function getRels(){ return $this->relations; }
	public function setRelRID($id){
		$nRels = new DLList();
		$rel = $this->relations->getFirstNode();
		while($rel != NULL){
			$r = $rel->readNode();
			$r->setRID($id);
			$nRels->insertLast($r);
			$rel = $rel->getNext();
		}
		$this->relations = $nRels;
	}
	private function mysqlEsc($pdo){
		$this->setRoot(mysqli_escape_string($pdo,$this->getRoot()));
		$this->setKey(mysqli_escape_string($pdo,$this->getKey()));
		$rel = $this->getRels()->getFirstNode();
		while($rel != NULL){
			$rel->readNode()->mysqlEsc($pdo);
			$rel = $rel->getNext();
		}
	}
	private function setRoot($r){ $this->root = (string)ucfirst($r); }
	private function setKey($k){ $this->key = (string)ucfirst($k); }

	private function getRoot(){ return (string)$this->root; }
	private function getKey(){ return (string)$this->key; }
}

?>