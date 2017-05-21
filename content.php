<?php
	class Content extends Root{
		protected $title;
		protected $desciption;
		protected $keywords;
		protected $author;
		public $_signature;
		protected $active;
		
		public function __construct($id,$t){
			Root::__construct($id,$t);	
			$this->title = NULL;
			$this->description = NULL;
			$this->keywords = array();
			$this->author = NULL;
			$this->_signature = NULL;
			$this->active = NULL;
		}
		public function dbRead($con){
			if(Root::dbRead($con)){
				$sql = "SELECT concat(First,' ',Last) as `Signature` FROM Users WHERE DBO_ID = ".$this->getAuthor();
				$res = mysqli_query($con,$sql);
				if(!$res){ error_log("SQL Content->dbRead: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); }
				else{
					$a = mysqli_fetch_array($res);
					$this->setSignature($a['Signature']);
				}
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(Root::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function dbDelete($con){
			if(Root::dbDelete($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			Root::initMysql($row);
			if(isset($row['Title'])){ $this->setTitle($row['Title']); }
			if(isset($row['Description'])){ $this->setDescription($row['Description']); }
			if(isset($row['Keywords'])){ $this->setKeywords(explode(",",$row['Keywords'])); }
			if(isset($row['Author'])){ $this->setAuthor($row['Author']); }
			if(isset($row['_Signature'])){ $this->setSignature($row['_Signature']); }
			if(isset($row['Active'])){ $this->setActive($row['Active']); }
		}
		protected function mysqlEsc($con){
			Root::mysqlEsc($con);
			$this->setTitle(mysqli_escape_string($con,$this->getTitle()));
			$this->setDescription(mysqli_escape_string($con,$this->getDesciption()));
			// NEEDS ADJUSTMENT FOR ARRAY $this->setKeywords(mysqli_escape_string($con,$this->getKeywords()));
			$this->setAuthor(mysqli_escape_string($con,$this->getAuthor()));
			$this->setActive(mysqli_escape_string($con,$this->getActive()));
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDesciption();
			$a['Keywords'] = $this->getKeywords();
			$a['Author'] = $this->getAuthor();
			$a['_Signature'] = $this->getSignature();
			$a['Active'] = $this->getActive();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = Root::view($html,$ds);
			$html = str_replace("{Title}",$this->getTitle(),$html);
			$html = str_replace("{Description}",$this->getDesciption(),$html);
			$html = str_replace("{Keywords}",implode(", ",$this->getKeywords()),$html);
			$html = str_replace("{Author}",$this->getAuthor(),$html);
			$html = str_replace("{_Signature}",$this->getSignature(),$html);
			$html = str_replace("{Active}",$this->getActive(),$html);
			return $html;
		}
		
		public function getTitle(){ return (string)$this->title; }
		public function getDesciption(){ return (string)$this->description; }
		public function getKeywords(){ return (array)$this->keywords; }
		public function getActive(){ return (int)$this->active; }
		protected function getAuthor(){ return (int)$this->author; }
		protected function getSignature(){ return (string)$this->_signature; }
		
		protected function setAuthor($a){ (int)$this->author = $a; }
		protected function setSignature($s){ $this->_signature = (string)$s; }
		protected function setTitle($t){ (string)$this->title = $t; }
		protected function setDescription($d){ (string)$this->description = $d; }
		protected function setKeywords($k){ (array)$this->keywords = $k; }
		protected function setActive($a){ $this->active = (int)$a; }
	}
	
	abstract class Collection extends Content{
		protected $pageSize;
		protected $_content;
		protected $_ctable;
		protected $_contRels;
		
		public function __construct($id,$table,$ctype,$crels){
			Content::__construct($id,$table);
			$this->pageSize = 0;
			$this->_content = new DBOList();
			$this->_ctable = $ctype;
			$this->_contRels = $crels;
		}
		abstract protected function processMYSQL($data);
		
		public function initMysql($row){ 
			Content::initMysql($row);
			$this->setPageSize($row['PageSize']);
		}
		protected function mysqlEsc($con){
			Content::mysqlEsc($con);
			$this->setPageSize(mysqli_escape_string($con,$this->getPageSize()));
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
		
		public function getPage($con,$num,$pgSize = NULL,$inactive = false,$cWhere = NULL){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL && $pgSize != 0){ $pgSize = $this->getPageSize(); }
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'";	}
			$sql .= "WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if($pgSize != 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getContentPage($con,$def,$inactive = false,$cWhere = NULL){
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
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data); }
			else{ error_log("SQL Collection->getContentPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }

		}
		public function getArchivePage($con,$num,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL && $pgSize != 0){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE d.Created >= ".strtotime($def."01 00:00:00")." AND d.Created <= ".strtotime($def.date('t',strtotime($def."01"))." 00:00:00")." AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if($pgSize != 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getArchivePage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getRelPage($con,$num,$crel,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL && $pgSize != 0){ $pgSize = $this->getPageSize(); }
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; if($crels[$i] == $crel){ $n = $c; } $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE r".$n.".Definition=\"".$def."\" AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if($pgSize != 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getRelPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getAuthorPage($con,$num,$def,$pgSize = NULL,$inactive = false,$cWhere = NULL){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL && $pgSize != 0){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$da = explode(" ",$def);
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.Author=(SELECT DBO_ID FROM Users WHERE First ='".$da[0]."' AND Last = '".$da[1]."') AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if($pgSize != 0){ $start = ($num-1)*$pgSize; $sql .= " LIMIT ".$start.",".$pgSize; }
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getAuthorPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function rssGenFeed($con,$domain,$path,$cpath,$inactive = false,$cWhere = NULL){
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			$sql = "SELECT c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if(!$data){ error_log("SQL Collection->rssGenFeed: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
			else{ $contList = $this->processMYSQL($data);
				$out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
            	<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
					<channel>
						<title>".$this->getTitle()."</title>
						<link>".$domain."</link>
						<description>".$this->getDesciption()."</description>
						<atom:link href=\"".$domain.$path."\" rel=\"self\" type=\"application/rss+xml\" />";
				$contItem = $contList->getFirstNode();
				while($contItem != NULL){
					$row = $contItem->readNode()->toArray();
					$out .= "
						<item>
							<title>".$row['Title']."</title>
							<link>".$domain.$cpath.$row['ID']."</link>
							<guid>".$domain.$cpath.$row['ID']."</guid>
							<description>".$row['Description']."</description>
						</item>";
					$contItem = $contItem->getNext();
				}
                $out .= "</channel>
				</rss>";
				return $out;		 
			}
		}
		public function load($con,$c = true,$r = true){
			if($c){ $this->setContent($con); }
			if($r){ $this->setContRels($con); }
		}
		
		public function getContent(){ return $this->_content; }
		public function getContRels(){ return $this->_contRels; }
		public function getCTable(){ return $this->_ctable; }
		public function getPageSize(){ return (int)$this->pageSize; }
		public function getArchiveDates($con){
			$sql = "SELECT distinct from_unixtime(d.Created, '%Y%m') AS 'Month' FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID WHERE c.PID = ".$this->getID();
			$dates = array();
			$res = mysqli_query($con,$sql);
			if(!$res){ error_log("SQL Collection->getArchiveDates: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
			else{
				while($row = mysqli_fetch_array($res)){	$dates[] = $row['Month']; }
				return $dates;
			}
		}
		
		protected function setContent($con,$inactive = false,$cWhere = NULL){
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			if($cWhere != NULL){ $sql = str_replace("WHERE",$cWhere." AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ $this->_content = $this->processMYSQL($data); return true; }
			else{ error_log("SQL Collection->setContent: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
			
		}
		protected function setContRels($con){
			foreach($this->_contRels as $key => $val){
				$sql = "SELECT distinct k.*, k.ID as KID, 0 as RID FROM `Keys` k left join Relationships r ON k.ID = r.KID WHERE k.`Key` = '".$key."' AND k.`Code` = '".rtrim($this->_ctable,"s")."' ORDER BY Definition ASC";
				$res = mysqli_query($con,$sql);
				$this->_contRels[$key] = new DBOList();
				while($row = mysqli_fetch_array($res)){
					$r = new Relation();
					$r->initMysql($row);
					$this->_contRels[$key]->insertLast($r);
				}
			}
		}
		protected function setCTable($t){ $this->_ctable = (string)$t; }
		protected function setPageSize($ps){ $this->pageSize = (int)$ps; }
	}
	
	class Blog extends Collection{
		public function __construct($id){
			Collection::__construct($id,"Blogs","Posts",array("Category"=>new DBOList()));
		}
		protected function processMYSQL($data){
			if($data){
				$list = new DBOList();
				while($row = mysqli_fetch_array($data)){
					$p = new Post(NULL);
					$p->initMysql($row);
					$list->insertLast($p);
				}
				return $list;
			}else{ return false; }
		}
		protected function mysqlEsc($con){
			Collection::mysqlEsc($con);
			//mysqlEsc Content Obj **Not Sure if needed**
		}
		public function getCategories(){
			$rels = Collection::getContRels();
			return $rels['Category'];
		}
		public function getPage($con,$num,$pgSize = NULL,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::getPage($con,$num,$pgSize,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::getPage($con,$num,$pgSize,$inactive); }
		}
		public function getContentPage($con,$def,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::getContentPage($con,$def,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::getContentPage($con,$def,$inactive); }
		}
		public function getArchivePage($con,$num,$def,$pgSize = NULL,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::getArchivePage($con,$num,$def,$pgSize,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::getArchivePage($con,$num,$def,$pgSize,$inactive); }
		}
		public function getRelPage($con,$num,$crel,$def,$pgSize = NULL,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::getRelPage($con,$num,$crel,$def,$pgSize,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::getRelPage($con,$num,$crel,$def,$pgSize,$inactive); }
		}
		public function getAuthorPage($con,$num,$def,$pgSize = NULL,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::getAuthorPage($con,$num,$def,$pgSize,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::getAuthorPage($con,$num,$def,$pgSize,$inactive); }
		}
		public function rssGenFeed($con,$domain,$path,$cpath,$inactive = false,$unpublished = false){
			if(!$unpublished){ return Collection::rssGenFeed($con,$domain,$path,$cpath,$inactive,"WHERE c.Published <= ".time()); }
			else{ return Collection::rssGenFeed($con,$domain,$path,$cpath,$inactive); }
		}
	}

	class MediaLibrary extends Collection{
		public function __construct($id){
			Collection::__construct($id,"MediaLibrarys","Media",array("Gallery"=>new DBOList(),"Category"=>new DBOList()));
		}
		protected function processMYSQL($data){
			if($data){
				$list = new DBOList();
				while($row = mysqli_fetch_array($data)){
					$m = new Media(NULL);
					$m->initMysql($row);
					$list->insertLast($m);
				}
				return $list;
			}else{ return false; }
		}
		protected function mysqlEsc($con){
			Collection::mysqlEsc($con);
			//mysqlEsc Content Obj **Not Sure if needed**
		}
		public function getGalleries(){
			$rels = Collection::getContRels();
			return $rels['Gallery'];
		}
		public function getCategories(){
			$rels = Collection::getContRels();
			return $rels['Category'];
		}
		public function genCarousel($con,$crel = NULL,$def = NULL){
			if($crel == NULL && $def == NULL){ $page = Collection::getPage($con,NULL,0); }
			else{ $page = Collection::getRelPage($con,NULL,$crel,$def,0); }
			$html = "<div id=\"carousel-example-generic\" class=\"carousel slide\" data-ride=\"carousel\">";
			$size = $page->size();
			if($size <= 5){
				$html .= "<!-- Indicators --><ol class=\"carousel-indicators\">";
				for($i = 0; $i < $size; $i++){ $html .= "<li data-target=\"#carousel-example-generic\" data-slide-to=\"".$i."\" ".($i == 0 ? "class=\"active\"" : "")."></li>"; }
				$html .= "</ol>";
			}
			$html .= "<!-- Wrapper for slides --><div class=\"carousel-inner\" role=\"listbox\">";
			$media = $page->getFirstNode();
			$first = true;
			while($media != NULL){
				$m = $media->readNode()->toArray();
				$html .= "<div class=\"item ".($first ? "active" : "")."\">
					  <img src=\"".$m['URI']."\" alt=\"".$m['Title']."\">
					  <div class=\"carousel-caption\">".$m['Description']."</div>
					</div>";
				$first = false;
				$media = $media->getNext();
			}
			$html .= "</div>
				<!-- Controls -->
				<a class=\"left carousel-control\" href=\"#carousel-example-generic\" role=\"button\" data-slide=\"prev\">
					<span class=\"glyphicon glyphicon-chevron-left\" aria-hidden=\"true\"></span>
					<span class=\"sr-only\">Previous</span>
				</a>
				<a class=\"right carousel-control\" href=\"#carousel-example-generic\" role=\"button\" data-slide=\"next\">
					<span class=\"glyphicon glyphicon-chevron-right\" aria-hidden=\"true\"></span>
					<span class=\"sr-only\">Next</span>
				</a>
			</div>";
			return $html;
		}
	}
	
/*	class Site extends Content{
		
	}*/

	class Media extends Content{
		protected $uri;
		protected $type;
		
		public function __construct($id){
			Content::__construct($id,"Media");
			Root::setRelationships(array('Parent'=>new Relationship("MediaLibrary","Parent"),'Gallery'=>new Relationship("Media","Gallery"),'Category'=>new Relationship("Media","Category")));
			$this->uri = NULL;
			$this->type = NULL;
		}
		public function dbRead($con){
			if(Content::dbRead($con)){
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(Content::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function dbDelete($con){
			if(Content::dbDelete($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			Content::initMysql($row);
			if(isset($row['URI'])){ $this->setURI($row['URI']); }
			if(isset($row['Type'])){ $this->setType($row['Type']); }
			if(isset($row['Category'])){
				$row['Category'] = explode(";",$row['Category']);
				$categories = array();
				foreach($row['Category'] as $cat){ 
					$a = explode(":",$cat);
					for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} } 
					$categories[] = array("ID"=>$a[0],"Created"=>NULL,"Updated"=>NULL,"RID"=>$a[1],"KID"=>$a[2],"Key"=>$a[3],"Code"=>$a[4],"Definition"=>$a[5]); 
				}
				$relations = $this->getRelationships();
				$relations['Category']->initMysql($categories);
				$this->setRelationships($relations);
			}
			if(isset($row['Gallery'])){
				$row['Gallery'] = explode(";",$row['Gallery']);
				$galleries = array();
				foreach($row['Gallery'] as $gal){ 
					$a = explode(":",$gal);
					for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} } 
					$galleries[] = array("ID"=>$a[0],"Created"=>NULL,"Updated"=>NULL,"RID"=>$a[1],"KID"=>$a[2],"Key"=>$a[3],"Code"=>$a[4],"Definition"=>$a[5]); 
				}
				$relations = $this->getRelationships();
				$relations['Gallery']->initMysql($galleries);
				$this->setRelationships($relations);
			}
		}
		protected function mysqlEsc($con){
			Content::mysqlEsc($con);
			$this->setURI(mysqli_escape_string($con,$this->getURI()));
			$this->setType(mysqli_escape_string($con,$this->getType()));
		}
		public function toArray(){
			$a = Content::toArray();
			$a['URI'] = $this->getURI();
			$a['Type'] = $this->getType();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html_out = Content::view($html[0],$ds);
			$html_out = str_replace("{URI}",$this->getURI(),$html_out);
			$html_out = str_replace("{Type}",$this->getType(),$html_out);
			if(strpos($html[0],'{Category}') !== false && (isset($html[1]) && $html[1] != NULL)){ $html_out = str_replace("{Category}",$this->viewCategories($html[1]),$html_out); }
			if(strpos($html[0],'{Gallery}') !== false && (isset($html[2]) && $html[2] != NULL)){ $html_out = str_replace("{Gallery}",$this->viewGalleries($html[2]),$html_out); }
			return $html_out;
		}
		public function viewCategories($html,$ds = "F j, Y, g:i a"){ return Root::viewRel("Category",$html,$ds); }
		protected function viewGalleries($html,$ds = "F j, Y, g:i a"){ return Root::viewRel("Gallery",$html,$ds); }
		protected function getURI(){ return (string)$this->html; }
		protected function getType(){ return (string)$this->type; }
		protected function setURI($u){ (string)$this->html = $u; }
		protected function setType($t){ (string)$this->type = $t; }
	}

	class HTMLDoc extends Content{
		protected $html;
		
		public function __construct($id,$t){
			Content::__construct($id,$t);	
			$this->html = NULL;
		}
		public function dbRead($con){
			if(Content::dbRead($con)){
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(Content::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function dbDelete($con){
			if(Content::dbDelete($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			Content::initMysql($row);
			if(isset($row['HTML'])){ $this->setHTML($row['HTML']); }
		}
		protected function mysqlEsc($con){
			Content::mysqlEsc($con);
			$this->setHTML(mysqli_escape_string($con,$this->getHTML()));
		}
		public function toArray(){
			$a = Content::toArray();
			$a['HTML'] = $this->getHTML();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = Content::view($html,$ds);
			$html = str_replace("{HTML}",$this->getHTML(),$html);
			return $html;
		}
		protected function getHTML(){ return (string)$this->html; }
		protected function setHTML($h){ (string)$this->html = $h; }
	}
	
/*	class Comment extends HTMLDoc{
		protected $post_id;
		protected $approved;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Comments");
			$this->uri = NULL;
		}
		public function dbRead($con){
			if(HTMLDoc::dbRead($con)){
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(HTMLDoc::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			HTMLDoc::initMysql($row);
			$this->setPostID($row['PostID']);
			$this->setApproved($row['Approved']);
		}
		public function toArray(){
			$a = HTMLDoc::toArray();
			$a['PostID'] = $this->getPostID();
			$a['Approved'] = $this->getApproved();
			return $a;
		}
		
		protected function getPostID(){ return $this->post_id; }
		protected function getApproved(){ return $this->approved; }
		
		protected function setPostID($pid){ $this->post_id = $pid; }
		protected function setApproved($a){ $this->approved = $a; }
	}*/
	
	class Post extends HTMLDoc{
		protected $coverImage;
		protected $published;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Posts");
			Root::setRelationships(array('Parent'=>new Relationship("Blog","Parent"),'Category'=>new Relationship("Post","Category")));
			$this->coverImage = NULL;
		}
		public function dbRead($con){
			if(HTMLDoc::dbRead($con)){
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(HTMLDoc::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function dbDelete($con){
			if(HTMLDoc::dbDelete($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			HTMLDoc::initMysql($row);
			if(isset($row['CoverImage'])){ $this->setCoverImage($row['CoverImage']); }
			if(isset($row['Published'])){ $this->setPublished($row['Published']); }
			if(isset($row['Category'])){
				$row['Category'] = explode(";",$row['Category']);
				$categories = array();
				foreach($row['Category'] as $cat){ 
					$a = explode(":",$cat);
					for($j = 0; $j < count($a); $j += 1){ if(!isset($a[$j])){ $a[$j] = NULL;} } 
					$categories[] = array("ID"=>$a[0],"Created"=>NULL,"Updated"=>NULL,"RID"=>$a[1],"KID"=>$a[2],"Key"=>$a[3],"Code"=>$a[4],"Definition"=>$a[5]); 
				}
				$relations = $this->getRelationships();
				$relations['Category']->initMysql($categories);
				$this->setRelationships($relations);
			}
		}
		protected function mysqlEsc($con){
			HTMLDoc::mysqlEsc($con);
			$this->setCoverImage(mysqli_escape_string($con,$this->getCoverImage()));
			$this->setPublished(mysqli_escape_string($con,$this->getPublished(NULL)));
		}
		public function toArray(){
			$a = HTMLDoc::toArray();
			$a['CoverImage'] = $this->getCoverImage();
			$a['Published'] = $this->getPublished(NULL);
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a",$ss = "http"){
			$html_out = HTMLDoc::view($html[0],$ds);
			$html_out = str_replace("{Published}",$this->getPublished($ds),$html_out);
			if($this->getCoverImage() != "" && $this->getCoverImage() != NULL){ $html_out = str_replace("{CoverImage}",url().$this->getCoverImage(),$html_out);}else{ $html_out = str_replace("{CoverImage}","",$html_out); }
			if(strpos($html[0],'{Category}') !== false && (isset($html[1]) && $html[1] != NULL)){ $html_out = str_replace("{Category}",$this->viewCategories($html[1]),$html_out); }
			return $html_out;
		}
		protected function viewCategories($html,$ds = "F j, Y, g:i a"){ return Root::viewRel("Category",$html,$ds); }
		
		public function getCategories(){ $rels = Root::getRelationships(); return $rels['Category']->getRels(); }
		protected function getCoverImage(){ return (string)$this->coverImage; }
		protected function getPublished($ds){ if(isset($ds) && $ds != NULL && $ds != ""){ return (string)date($ds,$this->published); }else{ return (int)$this->published; } }
		public function setCategories($con){ Root::setRelation("Post","Category",$con); }
		protected function setCoverImage($i){ $this->coverImage = (string)$i; }
		protected function setPublished($i){ $this->published = (int)$i; }
	}
	
/*	class Page extends HTMLDoc{
		protected $uri;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Pages");
			$this->uri = NULL;
		}
		public function dbRead($con){
			if(HTMLDoc::dbRead($con)){
				return true;
			}else{ return false; }
		}
		public function dbWrite($con){
			if(HTMLDoc::dbWrite($con)){
				return true;
			}else{ return false; }
		}
		public function initMysql($row){ 
			HTMLDoc::initMysql($row);
			$this->setAuthor($row['uri']);
		}
		public function toArray(){
			$a = Content::toArray();
			$a['URI'] = $this->getURI();
			return $a;
		}
		
		protected function getURI(){ return (string)$this->uri; }
		protected function setURI($u){ (string)$this->uri = $u; }
	}*/
?>