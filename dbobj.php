<?php

	class DBObj{
		private $id;
		private $created;
		private $updated;

		public function __construct(){
			$this->id = NULL;
			$this->created = NULL;
			$this->updated = NULL;
		}
		public function init($id,$c,$u){
			$this->setID($id);
			$this->setCreated($c);
			$this->setUpdated($u);
		}
		public function initMysql($row){ self::init($row['ID'],$row['Created'],$row['Updated']); }
		public function dbRead($con){
                        if(isset($this->id) && $this->getID() != NULL && $this->getID() != 0){ 
                                $res = $this->db_select($con);
                                if($res){ $this->initMysql(mysql_fetch_array($res)); return true; }else{ return $res; }
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

		protected function db_select($con,$sql){ return mysql_query($sql,$con);	}
		protected function db_insert($con,$sql){
			$res = mysql_query($sql,$con);
                        if($res){ $this->setID(mysql_insert_id($con)); }
                        return $res;
		}
		protected function db_update($con,$sql){ return mysql_query($sql,$con);	}
		protected function mysqlEsc(){
			$this->setID(mysql_escape_string($this->getID()));
			$this->setCreated(mysql_escape_string($this->getCreated(NULL)));
			$this->setUpdated(mysql_escape_string($this->getUpdated(NULL)));			
		}
		protected function toArray(){ return array("ID"=>$this->getID(),"Created"=>$this->getCreated("Y-m-d"),"Updated"=>$this->getUpdated("Y-m-d"));}
		protected function getID(){ return (int)$this->id; }
                protected function getCreated($ds){ if(isset($ds) && $ds != NULL){ return (string)date($ds,$this->created); }else{ return (int)$this->created; } }
                protected function getUpdated($ds){ if(isset($ds) && $ds != NULL){ return (string)date($ds,$this->updated); }else{ return (int)$this->updated; } }
                protected function setID($id){ $this->id = (int)$id; }
                protected function setCreated($c){ $this->created = (int)$c; }
                protected function setUpdated($u){ $this->updated = (int)$u; }
	}

	class DBOList extends DLList{
                private $table;
                private $name;
    
                public function __construct(){
                        DLList::__construct();
                        $this->table = NULL;
                        $this->name = NULL;
                }
		
                protected function getTable(){ $this->table; }
                protected function getName(){ $this->name; }

                protected function setTable($t){ $this->table = $t; }
                protected function setName($n){ $this->name = $n; }
        }

	class Root extends DBObj{
                private $relationships;

                public function __construct(){
                        DBObj::__construct();
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
			}else{ return false;}
		}
		protected function toArray(){
			$p = DBObj::toArray();
			$p['Rels'] = array();
			$a = $this->getRelationships();
			foreach($a as $k => $v){ $p['Rels'][$k] = $v->toArray(); }
			return $p;
		}
                protected function getRelationships(){ return $this->relationships; }
                protected function getRelation($key){ return $this->relationships[$key]; }
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
                                DBObj::__construct();
				$this->rid = NULL;
				$this->kid = NULL;
                                $this->code = NULL;
                                $this->definition = NULL;
                        }
                        public function init($id,$cd,$ud,$rid,$kid,$code,$def){
                                DBObj::init($id,$cd,$ud);
				$this->setRID($rid);
				$this->setKID($kid);
                                $this->setCode($code);
                                $this->setDefinition($def);
                        }
                        public function initMysql($row){ $this->init($row['ID'],$row['Created'],$row['Updated'],$row['RID'],$row['KID'],$row['Code'],$row['Definition']); }
                        public function toArray(){
				$p = DBObj::toArray();
				$p['RID'] = $this->getRID();
				$p['KID'] = $this->getKID();
				$p['Code'] = $this->getCode();
				$p['Definition'] = $this->getDefinition();
				return $p;
			}
			protected function db_select($con){
				$this->mysqlEsc();
	                        $sql = "SELECT * FROM `Relationships` WHERE `ID`=\"".$this->getID()."\"";
	                        return mysql_query($sql,$con);
			}
			protected function db_insert($con){
				$this->mysqlEsc();
				$time = time();
	                        $sql = "INSERT INTO `Relations` (`ID`,`RID`,`KID`,`Created`,`Updated`) VALUES (NULL,\"".$this->getRID()."\",\"".$this->getKID()."\",\"".$time."\",\"".$time."\")";
	                      	$res = mysql_query($sql,$con);
	                        if($res){ 
					$this->setID(mysql_insert_id($con)); 
					$this->setCreated($time);
					$this->setUpdated($time);
				}
	                        return $res;
			}
			protected function db_update($con){
				$this->mysqlEsc();
				$time = time();
	                        $sql = "UPDATE `Relations` SET `RID`=\"".$this->getRID()."\",`KID`=\"".$this->getKID()."\",`Updated`=\"".$time."\" WHERE `ID`=\"".$this->getID()."\"";
	                        $res = mysql_query($sql,$con);
				if($res){ $this->setUpdated($time); }
				return $res;
			}
			protected function db_delete($con){
				$this->mysqlEsc();
				$sql = "DELETE FROM `Relations` WHERE `ID`=".$this->getID();
				return mysql_query($sql,$con);
			}
                        protected function mysqlEsc(){
				DBObj::mysqlEsc();
                                $this->setKID(mysql_escape_string($this->getKID()));
                                $this->setRID(mysql_escape_string($this->getRID()));
                                $this->setCode(mysql_escape_string($this->getCode()));
                                $this->setDefinition(mysql_escape_string($this->getDefinition()));
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
                public function setRels($con,$id){
			$this->mysqlEsc();
                        $this->relations = new DLList();
                        $sql = "SELECT * FROM Relationships WHERE `Key` = '".$this->getKey()."' AND `RID`=".mysql_escape_string($id);
                        $res = mysql_query($sql,$con);
                        while($row = mysql_fetch_array($res)){
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
				array_push($a,$r);
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
                private function mysqlEsc(){
                        $this->setRoot(mysql_escape_string($this->getRoot()));
                        $this->setKey(mysql_escape_string($this->getKey()));
                }
                private function setRoot($r){ $this->root = ucfirst($r); }
                private function setKey($k){ $this->key = ucfirst($k); }
                private function getRoot(){ return (string)$this->root; }
                private function getKey(){ return (string)$this->key; }
        }

?>
