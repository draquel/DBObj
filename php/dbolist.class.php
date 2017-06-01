<?php
require_once("dllist.class.php");

class DBOList extends DLList{
	public function __construct(){
		DLList::__construct();
	}
/*		public function getKeyList($key,$code){
		$result = new DLList();
		$node = DLList::getFirstNode();
		for($i = 0; $i < DLList::size(); $i += 1){
			$d = $node->readNode();
			if($rel = $d->getRelation($key)){
				$r = $rel->getRels()->getFirstNode();
				while($r != NULL){ 
					$a = $r->readNode()->toArray();
					if($a['Code'] == $code){ $result->insertLast($r); break; } 
					$r = $r->getNext(); 
				}
			}
			$node = $node->getNext();
		}
		if($result->size() > 0){ return $result; }else{ return false; }
	}*/
	public function getArchive(){
		$result = array();
		$node = DLList::getFirstNode();
		for($i = 0; $i < DLList::size(); $i += 1){
			$d = $node->readNode();
			$da = $d->toArray();
			$ds = date("Ym",$da['Created']);
			if(!isset($result[$ds])){ $result[$ds] = array(); }
			$result[$ds][] = $d;
			$node = $node->getNext();
		}
		if(count($result) > 0){ return $result; }else{ return false; }	
	}
	/* //Moved to DLList in anticipation of depricating DBOList Class...
	public function toArray(){
		$a = array();
		$node = DLList::getFirstNode();
		while($node != NULL){ $a[] = $node->readNode()->toArray(); $node = $node->getNext(); }
		return $a;
	}*/
}

?>