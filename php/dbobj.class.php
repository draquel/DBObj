<?php
require_once("lib.php");

abstract class DBObj{
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
			if($this->getID() == 0){ return false; }else{ return $this->db_delete($con); }
		}else{ return false; }
	}
	protected function db_select($con){
		if($this->getID() != NULL && $this->getID() != 0){
			$sql = "SELECT * FROM DBObj po INNER JOIN ".$this->table." co ON po.ID = co.DBO_ID WHERE po.ID = ".$this->getID();
			$res = mysqli_query($con,$sql);
			if(!$res){ error_log("SQL DBObj->Select: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); error_log("MYSQL Stack Trace: ".debug_print_backtrace()); }
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
		if($this->created == NULL || $this->updated == NULL){ $ctime = time(); $utime = time(); }else{ $ctime = $this->created; $utime = $this->updated; }
		$sql = "INSERT INTO DBObj (`Table`,Created,Updated) VALUES (\"".$this->table."\",".$ctime.",".$utime."); INSERT INTO ".$this->table." (DBO_ID,".$fields.") VALUES (LAST_INSERT_ID(),".$values.")";
		if(mysqli_multi_query($con,$sql)){
			$i = 0;
			do{
				if($i == 0){ $this->setID($con->insert_id); }
				if($res = mysqli_store_result($con)){ mysqli_free_result($result); }
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
				if($res = mysqli_store_result($con)){ mysqli_free_result($result); }
				if($con->more_results() === FALSE){ break; }
			}while(mysqli_next_result($con));
			return true;
		}else{ error_log("SQL DBObj->Update: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false;}	
	}
	protected function db_delete($con){
		$sql = "DELETE FROM ".$this->getTable()." WHERE DBO_ID = ".$this->getID()."; DELETE FROM DBObj WHERE ID = ".$this->getID();
		if(mysqli_multi_query($con,$sql)){
			do{	if($res = mysqli_store_result($con)){ mysqli_free_result($result); } }while(mysqli_next_result($con));
			return true;
		}else{ error_log("SQL DBObj->Delete: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false;}	
	}
	protected function mysqlEsc($con){
		$this->setID(mysqli_escape_string($con,$this->getID()));
		$this->setCreated(mysqli_escape_string($con,$this->getCreated(NULL)));
		$this->setUpdated(mysqli_escape_string($con,$this->getUpdated(NULL)));			
	}
	public function toArray(){ return array("ID"=>$this->getID(),"Created"=>$this->getCreated(NULL),"Updated"=>$this->getUpdated(NULL)); }
	public function view($html,$ds = "F j, Y, g:i a"){
		$html = str_replace("{ID}",$this->getID(),$html);
		$html = str_replace("{Created}",$this->getCreated($ds),$html);
		$html = str_replace("{Updated}",$this->getUpdated($ds),$html);
		if(strpos($html,"{Created-Day}") !== false){
			$days = array('j','d','D','l','N','S','w','z');
			for($i = 0; $i < count($days); $i++){ if(strpos($ds,$days[$i]) !== false ){ $html = str_replace("{Created-Day}",$this->getCreated($days[$i]),$html); break; } }
		}
		if(strpos($html,"{Created-Month}") !== false){
			$months = array('F','m','M','n','t');
			for($i = 0; $i < count($months); $i++){ if(strpos($ds,$months[$i]) !== false ){ $html = str_replace("{Created-Month}",$this->getCreated($months[$i]),$html); break; } }
		}
		if(strpos($html,"{Created-Year}") !== false){
			$years = array('Y','L','o','y');
			for($i = 0; $i < count($years); $i++){ if(strpos($ds,$years[$i]) !== false ){ $html = str_replace("{Created-Year}",$this->getCreated($years[$i]),$html); break; } }
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

?>