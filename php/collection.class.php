<?php
require_once("content.class.php");
require_once("dllist.class.php");

abstract class Collection extends Content{
	protected $pageSize;
	protected $_content;
	protected $_ctable;
	protected $_contRels;

	public function __construct($id,$table,$ctype,$crels){
		Content::__construct($id,$table);
		$this->pageSize = 0;
		$this->_content = new DLList();
		$this->_ctable = $ctype;
		$this->_contRels = $crels;
	}
	abstract protected function processMYSQL($data);

	public function init($row){ 
		Content::init($row);
		$this->setPageSize($row['PageSize']);
	}
	protected function mysqlEsc($pdo){
		Content::mysqlEsc($pdo);
		$this->setPageSize(mysqli_escape_string($pdo,$this->getPageSize()));
	}
	public function toArray(){
		$a = Content::toArray();
		$a['PageSize'] = $this->getPageSize();
		$a['_contRels'] = $this->getContRels();
		$a['_ctable'] = $this->getCTable();
		if($this->getContent()->size() != 0){
			$a['_Content'] =  array();
			$g = $this->getContent()->getFirstNode();
			for($i = 0; $i < $this->getContent()->size(); $i++){
				$ar = $g->readNode()->toArray();
				$a['_Content'][$i] = $ar;
				$g = $g->getNext();
			}
		}
		return $a;
	}
	public function getPage($pdo,$num,$pgSize = NULL,$inactive = false,$cWhere = NULL){
		$page = new DLList();
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		if($pgSize === NULL && $pgSize !== 0){ $pgSize = $this->getPageSize(); }
		$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'";	}
		$sql .= "WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if($pgSize !== 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);
	}
	public function getContentPage($pdo,$def,$inactive = false,$cWhere = NULL){
		$page = new DLList();
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		$sql = "(SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= "FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND d.ID > ".$def." GROUP BY d.ID ORDER BY d.Created ASC LIMIT 1) UNION ALL (SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= "FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; } 
		$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND d.ID=".$def." GROUP BY d.ID) UNION ALL (SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= "FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND d.ID < ".$def." GROUP BY d.ID ORDER BY d.Created DESC LIMIT 1)";
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getContentPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);

	}
	public function getArchivePage($pdo,$num,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
		$page = new DLList();
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		if($pgSize === NULL && $pgSize !== 0){ $pgSize = $this->getPageSize(); }
		$start = ($num-1)*$pgSize;
		$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE d.Created >= ".strtotime($def."01 00:00:00")." AND d.Created <= ".strtotime($def.date('t',strtotime($def."01"))." 00:00:00")." AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if($pgSize !== 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getArchivePage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);
	}
	public function getRelPage($pdo,$num,$crel,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
		$page = new DLList();
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		if($pgSize === NULL && $pgSize !== 0){ $pgSize = $this->getPageSize(); }
		$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; if($crels[$i] == $crel){ $n = $c; } $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE r".$n.".Definition=\"".$def."\" AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if($pgSize !== 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getRelPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);
	}
	public function getAuthorPage($pdo,$num,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
		$page = new DLList();
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		if($pgSize === NULL && $pgSize !== 0){ $pgSize = $this->getPageSize(); }
		$start = ($num-1)*$pgSize;
		$da = explode(" ",$def);
		$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE c.Author=(SELECT DBO_ID FROM Users WHERE First ='".$da[0]."' AND Last = '".$da[1]."') AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if($pgSize !== 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getAuthorPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);
	}
	public function rssGenFeed($pdo,$domain,$path,$cpath,$inactive = false,$cWhere = NULL){
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		$sql = "SELECT c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		
		$pdotList = $this->processMYSQL($data);
		$out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
		<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
			<channel>
				<title>".$this->getTitle()."</title>
				<link>".$domain."</link>
				<description>".$this->getDesciption()."</description>
				<atom:link href=\"".$domain.$path."\" rel=\"self\" type=\"application/rss+xml\" />";
		$pdotItem = $pdotList->getFirstNode();
		while($pdotItem != NULL){
			$row = $pdotItem->readNode()->toArray();
			$out .= "
				<item>
					<title>".$row['Title']."</title>
					<link>".$domain.$cpath.$row['ID']."</link>
					<guid>".$domain.$cpath.$row['ID']."</guid>
					<description>".$row['Description']."</description>
				</item>";
			$pdotItem = $pdotItem->getNext();
		}
		$out .= "</channel>
		</rss>";
		return $out;		 
		
	}
	public function load($pdo,$c = true,$r = true){
		if($c){ $this->setContent($pdo); }
		if($r){ $this->setContRels($pdo); }
	}

	public function getContent(){ return $this->_content; }
	public function getContRels(){ return $this->_contRels; }
	public function getCTable(){ return $this->_ctable; }
	public function getPageSize(){ return (int)$this->pageSize; }
	public function getArchiveDates($pdo){
		$sql = "SELECT distinct from_unixtime(d.Created, '%Y%m') AS 'Month' FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID WHERE c.PID = ".$this->getID();
		$dates = array();
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getArchiveDates: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){ $dates[] = $row['Month']; }
		return $dates;
	}

	protected function setContent($pdo,$inactive = false,$cWhere = NULL){
		$crels = array();
		foreach($this->_contRels as $key => $val){ $crels[] = $key; }
		$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
		$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
		for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
		$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
		if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
		if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
		try{ $stmt = $pdo->query($sql); }
		catch(PDOException $e){	error_log("SQL Collection->getAuthorPage: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
		return $this->processMYSQL($stmt);

	}
	protected function setContRels($pdo){
		foreach($this->_contRels as $key => $val){
			$sql = "SELECT distinct k.*, k.ID as KID, 0 as RID FROM `Keys` k left join Relationships r ON k.ID = r.KID WHERE k.`Key` = '".$key."' AND k.`Code` = '".rtrim($this->_ctable,"s")."' ORDER BY Definition ASC";
			try{ $stmt = $pdo->query($sql); }
			catch(PDOException $e){	error_log("SQL Collection->getArchiveDates: ".$sql); error_log("SQL ERROR: ".$e->getMessage()); error_log("SQL Stack Trace: ".debug_print_backtrace());	return false; }
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$r = new Relation();
				$r->init($row);
				$this->_contRels[$key]->insertLast($r);
			}
		}
	}
	protected function setCTable($t){ $this->_ctable = (string)$t; }
	protected function setPageSize($ps){ $this->pageSize = (int)$ps; }
}

?>