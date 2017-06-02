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
	public function init($row){ if(isset($row['ID'])){ $this->setID($row['ID']); } if(isset($row['Created'])){ $this->setCreated($row['Created']); } if(isset($row['Updated'])){ $this->setUpdated($row['Updated']); } }
	public function dbRead($pdo){
		if(isset($this->id) && $this->getID() != NULL && $this->getID() != 0){ 
			$res = $this->db_select($pdo);
			if($res){ $this->init($res->fetch(PDO::FETCH_ASSOC)); return true; }else{ return $res; }
		}else{ return false; }
	}
	public function dbWrite($pdo){
		if(isset($this->id)){
			if($this->getID() == 0){ return $this->db_insert($pdo); }else{ return $this->db_update($pdo); }
		}else{ return false; }
	}
	public function dbDelete($pdo){
		if(isset($this->id)){
			if($this->getID() == 0){ return false; }else{ return $this->db_delete($pdo); }
		}else{ return false; }
	}
	protected function db_select($pdo){
		$sql = "SELECT * FROM DBObj po INNER JOIN ".$this->getTable()." co ON po.ID = co.DBO_ID WHERE po.ID = :ID";
		try{ $stmt = $pdo->prepare($sql); $stmt->execute(['ID'=>$this->getID()]); }
		catch(PDOException $e){	error_log("SQL DBObj->Select: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		return $stmt;
	}
	protected function db_insert($pdo){
		$a = $this->toArray(); $fields = ""; $val_str = ""; $values = array();
		foreach($a as $key => $val){
			if(substr($key,0,1) == "_" || $key == "Rels" || $key == "ID" || $key == "Table" || $key == "Created" || $key == "Updated" || gettype($value) == "object"){ continue; }
			if($fields != ""){ $fields .= ","; }
			$fields .= $key;
			if($val_str != ""){ $val_str .= ","; }
			$val_str .= ":".ucfirst($key);
			if($values != ""){ $values .= ","; }
			if(gettype($val) == "array"){ $values[ucfirst($key)] = implode(",",$val); }else{ $values[ucfirst($key)] = $val; }
		}
		if($this->created == NULL || $this->updated == NULL){ $values['Created'] = time(); $values['Updated'] = time(); }else{ $values['Created'] = $this->created; $values['Updated'] = $this->updated; }
		$sql = "INSERT INTO DBObj (`Table`,Created,Updated) VALUES (\"".$this->table."\",:Created,:Updated); INSERT INTO ".$this->table." (DBO_ID,".$fields.") VALUES (LAST_INSERT_ID(),".$val_str.")";
		try{ $stmt = $pdo->prepare($sql)->execute($values);	$this->setID($pdo->lastInsertId()); }
		catch(PDOException $e){ error_log("SQL DBObj->Insert: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		return $stmt;
	}
	protected function db_update($pdo){ 
		$a = $this->toArray();
		$sql = "UPDATE DBObj SET Updated = ".time()." WHERE ID = :ID; UPDATE ".$this->getTable()." SET ";
		$i = 0;
		$values = array("ID"=>$this->getID());
		foreach($a as $key => $val){
			$i++;
			if(substr($key,0,1) == "_" || $key == "Rels" || $key == "ID" || $key == "Table" || $key == "Created" || $key == "Updated" || gettype($val) == "object"){ continue; }
			$sql .= ucfirst($key)." = :".ucfirst($key);
			if(gettype($val) == "array"){ $values[ucfirst($key)] = implode(",",$val); }else{ $values[ucfirst($key)] = $val; }
			if($i != count($a)){ $sql .= ","; }
		}
		$sql .= " WHERE DBO_ID = :ID;";
		try{ $stmt = $pdo->prepare($sql); $stmt->execute($values);	}
		catch(PDOException $e){	error_log("SQL DBObj->Update: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		return $stmt;
	}
	protected function db_delete($pdo){
		$sql = "DELETE FROM ".$this->getTable()." WHERE DBO_ID=:ID; DELETE FROM DBObj WHERE ID=:ID";
		try{ $stmt = $pdo->prepare($sql); $stmt->execute(["ID"=>$this->getID()]); }
		catch(PDOException $e){	error_log("SQL DBObj->Delete: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace()); return false; }
		return $stmt;
	}
	protected function mysqlEsc($pdo){
		$this->setID(mysqli_escape_string($pdo,$this->getID()));
		$this->setCreated(mysqli_escape_string($pdo,$this->getCreated(NULL)));
		$this->setUpdated(mysqli_escape_string($pdo,$this->getUpdated(NULL)));			
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