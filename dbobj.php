<?php
	include("lib.php");
	include("dllist.php");
	include("sql.php");
	
	class DBObj{
		private $id;
		private $table;
		private $created;
		private $updated;
		
		public function __construct($id,$t){
			$this->id = $id;
			$this->table = $t;
			$this->created = NULL;
			$this->updated = NULL;
		}
/*		public function init($id,$t,$c,$u){
			$this->setID($id);
			$this->setTable($t);
			$this->setCreated($c);
			$this->setUpdated($u);
		}*/
		public function initMysql($row){ $this->setID($row['ID']); $this->setCreated($row['Created']); $this->setUpdated($row['Updated']); }
		public function dbRead($con){
			if(isset($this->id) && $this->getID() != NULL && $this->getID() != 0){ 
				$res = $this->db_select($con);
				if($res){ $this->initMysql(mysqli_fetch_array($res)); return true; }else{ return $res; }
			}else{ return false; }
		}
		public function dbWrite($con){
			if(isset($this->id)){
				if($this->getID() == 0){ return $this->db_insert($con); }else{ return $this->db_update($con); }
			}else{ return false; }
		}
		public function dbDelete($con){
			if(isset($this->id)){
				if($this->getID() == 0){ return false; }else{ return $this->db_delete($con); }
			}else{ return false; }
		}
		
		protected function db_select($con){
			if($this->getID() != NULL && $this->getID() != 0){
				$sql = "SELECT * FROM ".$this->table." WHERE ID = ".$this->getID();
				return mysqli_query($con,$sql);
			}else{ return false; }
		}
		protected function db_insert($con){
			$a = $this->toArray(); $fields = ""; $values = "";
			foreach($a as $key => $value){
				if($key == "Rels" || $key == "ID" || gettype($value) == "object"){ continue; }
				if($fields != ""){ $fields .= ","; }
				$fields .= $key;
				if($values != ""){ $values .= ","; }
				if(gettype($value) == "array"){ $values .= "'".explode(",",$value)."'"; }elseif(gettype($value) == "integer"){ $values .= $value; }else{ $values .= "'".$value."'"; }
			}
			$sql = "INSERT INTO ".$this->table." (".$fields.") VALUES (".$values.")";
			$res = mysqli_query($sql,$con);
			if($res){ $this->setID(mysqli_insert_id($con)); }
			return $res;
		}
		protected function db_update($con){ 
			$a = $this->toArray();
			$sql = "UPDATE ".$this->getTable()." SET ";
			$i = 0;
			foreach($a as $key => $val){
				$i++;
				if($key == "Rels" || $key == "ID" || gettype($value) == "object"){ continue; }
				$sql .= ucfirst($key)." = ";
				if(gettype($value) == "array"){ $values .= "'".explode(",",$value)."'"; }elseif(gettype($value) == "integer"){ $sql .= $val; }else{ $sql .= "'".$val."'"; }
				if($i != count($a)){ $sql .= ","; }
			}
			$sql .= " WHERE ID = ".$this->getID();
			return mysqli_query($con,$sql);		
		}
		protected function mysqlEsc($con){
			$this->setID(mysqli_escape_string($con,$this->getID()));
			$this->setCreated(mysqli_escape_string($con,$this->getCreated(NULL)));
			$this->setUpdated(mysqli_escape_string($con,$this->getUpdated(NULL)));			
		}
		public function toArray(){ return array("ID"=>$this->getID(),"Created"=>$this->getCreated(NULL),"Updated"=>$this->getUpdated(NULL));}
		
		protected function getID(){ return (int)$this->id; }
		protected function getTable(){ return (string)$this->table; }
		protected function getCreated($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->created); }else{ return (int)$this->created; } }
		protected function getUpdated($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->updated); }else{ return (int)$this->updated; } }
		
		protected function setID($id){ $this->id = (int)$id; }
		protected function setTable($t){ $this->table = (string)$t; }
		protected function setCreated($c){ $this->created = (int)$c; }
		protected function setUpdated($u){ $this->updated = (int)$u; }
	}

	class Root extends DBObj{
		private $relationships;
		
		public function __construct($id,$t){
			DBObj::__construct($id,$t);
			$this->relationships = array();
		}
		public function dbWrite($con){
			if(DBObj::dbWrite($con)){
				//Write Relationships
				$a = $this->getRelationships();
				$succ = true;
				foreach($a as $r){ 
					$r->setRelRID($this->getID());
					$succ = $r->dbWrite($con); 
					if(!$succ){break;}
				}
				return $succ;
			}else{ return false; }
		}
		public function toArray(){
			$p = DBObj::toArray();
			$p['Rels'] = array();
			$a = $this->getRelationships();
			if(count($a) > 0){ foreach($a as $k => $v){ $p['Rels'][$k] = $v->toArray(); } }
			return $p;
		}
		
		protected function getRelationships(){ return $this->relationships; }
		protected function getRelation($key){ if(isset($this->relationships[$key])){ return $this->relationships[$key]; }else{ return false; } }
		
		protected function setRelationships($rel){ if(is_array($rel)){ $this->relationships = $rel; return TRUE; }else{ return FALSE; } }
		protected function setRelation($root,$key,$con){
			$this->relationships[$key] = new Relationship($root,$key);
			$this->relationships[$key]->setRels($con,$this->getID());
		}
	}
	class Relation extends DBObj{
		private $rid;
		private $kid;
		private $code;
		private $definition;
		
		public function __construct(){
			DBObj::__construct(NULL,"Relations");
			$this->rid = NULL;
			$this->kid = NULL;
			$this->code = NULL;
			$this->definition = NULL;
		}
/*		public function init($id,$cd,$ud,$rid,$kid,$code,$def){
			DBObj::init($id,"Relations",$cd,$ud);
			$this->setRID($rid);
			$this->setKID($kid);
			$this->setCode($code);
			$this->setDefinition($def);
		}*/
		public function initMysql($row){ 
			DBObj::initMysql($row);
			$this->setRID($row['RID']);
			$this->setKID($row['KID']);
			$this->setCode($row['Code']);
			$this->setDefinition($row['Definition']); 
		}
		public function toArray(){
			$p = DBObj::toArray();
			$p['RID'] = $this->getRID();
			$p['KID'] = $this->getKID();
			$p['Code'] = $this->getCode();
			$p['Definition'] = $this->getDefinition();
			return $p;
		}
		protected function db_select($con){
			/*$this->mysqlEsc();*/
			$sql = "SELECT * FROM `Relationships` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($sql,$con);
		}
		protected function db_insert($con){
			$this->mysqlEsc();
			$time = time();
			$sql = "INSERT INTO `Relations` (`ID`,`RID`,`KID`,`Created`,`Updated`) VALUES (NULL,\"".$this->getRID()."\",\"".$this->getKID()."\",\"".$time."\",\"".$time."\")";
			$res = mysqli_query($sql,$con);
			if($res){ 
				$this->setID(mysqli_insert_id($con)); 
				$this->setCreated($time);
				$this->setUpdated($time);
			}
			return $res;
		}
		protected function db_update($con){
			$this->mysqlEsc();
			$time = time();
			$sql = "UPDATE `Relations` SET `RID`=\"".$this->getRID()."\",`KID`=\"".$this->getKID()."\",`Updated`=\"".$time."\" WHERE `ID`=\"".$this->getID()."\"";
			$res = mysqli_query($sql,$con);
			if($res){ $this->setUpdated($time); }
			return $res;
		}
		protected function db_delete($con){
			$this->mysqlEsc();
			$sql = "DELETE FROM `Relations` WHERE `ID`=".$this->getID();
			return mysqli_query($sql,$con);
		}
		protected function mysqlEsc($con){
			DBObj::mysqlEsc($con);
			$this->setKID(mysqli_escape_string($con,$this->getKID()));
			$this->setRID(mysqli_escape_string($con,$this->getRID()));
			$this->setCode(mysqli_escape_string($con,$this->getCode()));
			$this->setDefinition(mysqli_escape_string($con,$this->getDefinition()));
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

	class Relationship{
		private $root;
		private $key;
		private $relations;
		
		public function __construct($r,$k){
			$this->root = $r;
			$this->key = $k;
			$this->relations = new DLList();
		}
		public function init($root,$key){
			$this->setRoot($root);
			$this->setKey($key);
		}
		public function initMysql(/*$row,*/$rels = NULL){
			/*$this->setRoot($row['Root']);
			$this->setKey($row['Key']);*/
			if(is_array($rels)){
				for($i = 0;$i < count($rels); $i++){
					$r = new Relation();
					$r->initMysql($rels[$i]);
					$this->relations->insertLast($r);
				}
			}
		}
		public function setRels($con,$id){
			/*$this->mysqlEsc();*/
			$this->relations = new DLList();
			$sql = "SELECT * FROM Relationships WHERE `Key` = '".$this->getKey()."' AND `RID`=".$id;
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$r = new Relation();
				$r->initMysql($row);
				$this->relations->insertLast($r);
			}
		}
		public function toArray(){
			$a = array();
			$rn = $this->getRels()->getFirstNode();
			while($rn != NULL){
				$r = $rn->readNode()->toArray();
				$r['Root'] = $this->getRoot();
				$r['Key'] = $this->getKey();
				$a[] = $r;
				$rn = $rn->getNext();
			}
			return $a;
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
		}
		private function setRoot($r){ $this->root = (string)ucfirst($r); }
		private function setKey($k){ $this->key = (string)ucfirst($k); }
		
		private function getRoot(){ return (string)$this->root; }
		private function getKey(){ return (string)$this->key; }
	}

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
	}
	
	include("contact_info.php");
	include("contact.php");
	include("user.php");
	include("content.php");
	include("setting.php");
?>
