<?php
require_once("dbolist.class.php");
require_once("relation.class.php");

class Relationship{
	private $root;
	private $key;
	private $relations;

	public function __construct($r,$k){
		$this->root = $r;
		$this->key = $k;
		$this->relations = new DBOList();
	}
	public function init($root,$key){
		$this->setRoot($root);
		$this->setKey($key);
	}
	public function initMysql(/*$row,*/$rels = NULL){
		if(is_array($rels)){
			for($i = 0;$i < count($rels); $i++){
				$r = new Relation();
				$r->initMysql($rels[$i]);
				$this->relations->insertLast($r);
			}
		}
	}
	public function setRel($r){ $this->relations->insertLast($r); }
	public function setRels($con,$id){
		/*$this->mysqlEsc($con);*/
		$this->relations = new DBOList();
		$sql = "SELECT * FROM Relationships WHERE `Key` = '".$this->getKey()."' AND `RID`=".$id;
		$res = mysqli_query($con,$sql);
		while($row = mysqli_fetch_array($res)){
			$r = new Relation();
			$r->initMysql($row);
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
		return $this->getRels()->toArray();
	}
	public function dbWrite($con){
		$rel = $this->relations->getFirstNode();
		$succ = true;
		while($rel != NULL){
			$succ = $rel->readNode()->dbWrite($con);
			if(!$succ){break;}
			$rel = $rel->getNext();
		}
		return $succ;
	}
	public function dbDelete($con){
		$rel = $this->relations->getFirstNode();
		$succ = true;
		while($rel != NULL){
			$succ = $rel->readNode()->dbDelete($con);
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
	private function mysqlEsc($con){
		$this->setRoot(mysqli_escape_string($con,$this->getRoot()));
		$this->setKey(mysqli_escape_string($con,$this->getKey()));
		$rel = $this->getRels()->getFirstNode();
		while($rel != NULL){
			$rel->readNode()->mysqlEsc($con);
			$rel = $rel->getNext();
		}
	}
	private function setRoot($r){ $this->root = (string)ucfirst($r); }
	private function setKey($k){ $this->key = (string)ucfirst($k); }

	private function getRoot(){ return (string)$this->root; }
	private function getKey(){ return (string)$this->key; }
}

?>