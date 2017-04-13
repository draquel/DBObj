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
		public function initMysql($row){ if(isset($row['ID'])){ $this->setID($row['ID']); } if(isset($row['Created'])){ $this->setCreated($row['Created']); } if(isset($row['Updated'])){ $this->setUpdated($row['Updated']); } }
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
				if($this->getID() == 0){ error_log("DBOBJ - DELETE"); return false; }else{ return $this->db_delete($con); }
			}else{ return false; }
		}
		
		protected function db_select($con){
			if($this->getID() != NULL && $this->getID() != 0){
				$sql = "SELECT * FROM DBObj po INNER JOIN ".$this->table." co ON po.ID = co.DBO_ID WHERE po.ID = ".$this->getID();
				$res = mysqli_query($con,$sql);
				if(!$res){ error_log("SQL DBObj->Select: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); }
				return $res;
			}else{ return false; }
		}
		protected function db_insert($con){
			$a = $this->toArray(); $fields = ""; $values = "";
			foreach($a as $key => $value){
				if(substr($key,0,1) == "_" || $key == "Rels" || $key == "ID" || $key == "Table" || $key == "Created" || $key == "Updated" || gettype($value) == "object"){ continue; }
				if($fields != ""){ $fields .= ","; }
				$fields .= $key;
				if($values != ""){ $values .= ","; }
				if(gettype($value) == "array"){ $values .= "\"".implode(",",$value)."\""; }elseif(gettype($value) == "integer"){ $values .= $value; }else{ $values .= "\"".mysqli_real_escape_string($con,$value)."\""; }
			}
			$sql = "INSERT INTO DBObj (`Table`,Created,Updated) VALUES (\"".$this->table."\",".time().",".time()."); INSERT INTO ".$this->table." (DBO_ID,".$fields.") VALUES (LAST_INSERT_ID(),".$values.")";
			if(mysqli_multi_query($con,$sql)){
				$i = 0;
				do{
					if($i == 0){ $this->setID($con->insert_id); }
					if($res = mysqli_store_result($con)) {
						mysqli_free_result($result);
					}
					if($con->more_results() === FALSE){ break; }else{ $i++; }
				}while(mysqli_next_result($con));
				return true;
			}else{ error_log("SQL DBObj->Insert: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		protected function db_update($con){ 
			$a = $this->toArray();
			$sql = "UPDATE DBObj SET Updated = ".time()." WHERE ID = ".$this->getID()."; UPDATE ".$this->getTable()." SET ";
			$i = 0;
			foreach($a as $key => $val){
				$i++;
				if(substr($key,0,1) == "_" || $key == "Rels" || $key == "ID" || $key == "Table" || $key == "Created" || $key == "Updated" || gettype($val) == "object"){ continue; }
				$sql .= ucfirst($key)." = ";
				if(gettype($val) == "array"){ $sql .= "\"".implode(",",$val)."\""; }elseif(gettype($val) == "integer"){ $sql .= $val; }else{ $sql .= "\"".mysqli_real_escape_string($con,$val)."\""; }
				if($i != count($a)){ $sql .= ","; }
			}
			$sql .= " WHERE DBO_ID = ".$this->getID().";";
			if(mysqli_multi_query($con,$sql)){
				do{
					if($res = mysqli_store_result($con)) {
						mysqli_free_result($result);
					}
					if($con->more_results() === FALSE){ break; }
				}while(mysqli_next_result($con));
				return true;
			}else{ error_log("SQL DBObj->Update: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false;}	
		}
		protected function db_delete($con){
			$sql = "DELETE FROM ".$this->getTable()." WHERE DBO_ID = ".$this->getID()."; DELETE FROM DBObj WHERE ID = ".$this->getID();
			if(mysqli_multi_query($con,$sql)){
				do{
					if($res = mysqli_store_result($con)) {
						mysqli_free_result($result);
					}
				}while(mysqli_next_result($con));
				return true;
			}else{ error_log("SQL DBObj->Delete: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false;}	
		}
		protected function mysqlEsc($con){
			$this->setID(mysqli_escape_string($con,$this->getID()));
			$this->setCreated(mysqli_escape_string($con,$this->getCreated(NULL)));
			$this->setUpdated(mysqli_escape_string($con,$this->getUpdated(NULL)));			
		}
		public function toArray(){ return array("ID"=>$this->getID(),"Created"=>$this->getCreated(NULL),"Updated"=>$this->getUpdated(NULL));}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = str_replace("{ID}",$this->getID(),$html);
			$html = str_replace("{Created}",$this->getCreated($ds),$html);
			$html = str_replace("{Updated}",$this->getUpdated($ds),$html);
			if(str_pos($html,"{Created-Day}") !== false){
				$days = array('j','d','D','l','N','S','w','z');
				for($i = 0; $i < count($days); $i++){ if(str_pos($ds,$days[$i]) !== false ){ $html = str_replace("{Created-Day}",$this->getCreated($days[$i]),$html); break; } }
			}
			if(str_pos($html,"{Created-Month}") !== false){
				$months = array('F','m','M','n','t');
				for($i = 0; $i < count($months); $i++){ if(str_pos($ds,$months[$i]) !== false ){ $html = str_replace("{Created-Month}",$this->getCreated($months[$i]),$html); break; } }
			}
			if(str_pos($html,"{Created-Year}") !== false){
				$years = array('Y','L','o','y');
				for($i = 0; $i < count($years); $i++){ if(str_pos($ds,$years[$i]) !== false ){ $html = str_replace("{Created-Year}",$this->getCreated($years[$i]),$html); break; } }
			}
			return $html;
		}
		
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
/*		public function init($id,$cd,$ud,$rid,$kid,$code,$def){
			DBObj::init($id,"Relations",$cd,$ud);
			$this->setRID($rid);
			$this->setKID($kid);
			$this->setCode($code);
			$this->setDefinition($def);
		}*/
		public function initMysql($row){ 
			DBObj::initMysql($row);
			if(isset($row['RID'])){ $this->setRID($row['RID']); }
			if(isset($row['KID'])){ $this->setKID($row['KID']); }
			if(isset($row['Code'])){ $this->setCode($row['Code']); }
			if(isset($row['Definition'])){ $this->setDefinition($row['Definition']); }
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
			/*$this->mysqlEsc($con);*/
			$sql = "SELECT * FROM `Relationships` WHERE `ID`=\"".$this->getID()."\"";
			return mysqli_query($con,$sql);
		}
		protected function db_insert($con){
			$this->mysqlEsc($con);
			$time = time();
			$sql = "INSERT INTO `Relations` (`ID`,`RID`,`KID`,`Created`,`Updated`) VALUES (NULL,\"".$this->getRID()."\",\"".$this->getKID()."\",\"".$time."\",\"".$time."\")";
			error_log("SQL Relation->Insert: ".$sql);
			$res = mysqli_query($con,$sql);
			if($res){ 
				$this->setID(mysqli_insert_id($con)); 
				$this->setCreated($time);
				$this->setUpdated($time);
			}else{
				error_log("MYSQL ERROR: " . mysqli_error($con));
			}
			return $res;
		}
		protected function db_update($con){
			//$this->mysqlEsc($con);
			$time = time();
			$sql = "UPDATE `Relations` SET `RID`=\"".$this->getRID()."\",`KID`=\"".$this->getKID()."\",`Updated`=\"".$time."\" WHERE `ID`=\"".$this->getID()."\"";
			error_log("SQL Relation->Update: ".$sql);
			$res = mysqli_query($con,$sql);
			if($res){ $this->setUpdated($time); }
			return $res;
		}
		protected function db_delete($con){
			//$this->mysqlEsc($con);
			$sql = "DELETE FROM `Relations` WHERE `ID`=".$this->getID();
			return mysqli_query($con,$sql);
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
			$nRels = new DBOList();
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
		public function toArray(){
			$a = array();
			$node = DLList::getFirstNode();
			while($node != NULL){ $a[] = $node->readNode()->toArray(); $node = $node->getNext(); }
			return $a;
		}
	}
	
	include("contact_info.php");
	include("contact.php");
	include("content.php");
	include("setting.php");
?>
