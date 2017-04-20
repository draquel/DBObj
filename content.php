<?php
	class Content extends Root{
		protected $title;
		protected $desciption;
		protected $keywords;
		protected $author;
		protected $active;
		
		public function __construct($id,$t){
			Root::__construct($id,$t);	
			$this->title = NULL;
			$this->description = NULL;
			$this->keywords = array();
			$this->author = NULL;
			$this->active = NULL;
		}
		public function dbRead($con){
			if(Root::dbRead($con)){
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
			if(isset($row['Active'])){ $this->setActive($row['Active']); }
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDesciption();
			$a['Keywords'] = $this->getKeywords();
			$a['Author'] = $this->getAuthor();
			$a['Active'] = $this->getActive();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = Root::view($html,$ds);
			$html = str_replace("{Title}",$this->getTitle(),$html);
			$html = str_replace("{Description}",$this->getDesciption(),$html);
			$html = str_replace("{Keywords}",implode(", ",$this->getKeywords()),$html);
			$html = str_replace("{Author}",$this->getAuthor(),$html);
			$html = str_replace("{Active}",$this->getActive(),$html);
			return $html;
		}
		
		public function getTitle(){ return (string)$this->title; }
		public function getDesciption(){ return (string)$this->description; }
		public function getKeywords(){ return (array)$this->keywords; }
		public function getActive(){ return (int)$this->active; }
		protected function getAuthor(){ return (string)$this->author; }
		
		protected function setAuthor($a){ (string)$this->author = $a; }
		protected function setTitle($t){ (string)$this->title = $t; }
		protected function setDescription($d){ (string)$this->description = $d; }
		protected function setKeywords($k){ (array)$this->keywords = $k; }
		protected function setActive($a){ $this->active = (int)$a; }
	}
	
	abstract class Collection extends Content{
		protected $pageSize;
		protected $content;
		protected $_ctable;
		protected $_contRels;
		
		public function __construct($id,$table,$ctype,$crels){
			Content::__construct($id,$table);
			$this->pageSize = 0;
			$this->content = new DBOList();
			$this->_ctable = $ctype;
			$this->_contRels = $crels;
		}
		abstract protected function processMYSQL($data);
		
		public function initMysql($row){ 
			Content::initMysql($row);
			$this->setPageSize($row['PageSize']);
		}
		public function toArray(){
			$a = Content::toArray();
			$a['PageSize'] = $this->getPageSize();
			$a['_contRels'] = $this->getContRels();
			if($this->getContent()->size() != 0){
				$a['Content'] =  array();
				$g = $this->getContent()->getFirstNode();
				for($i = 0; $i < $this->getContent()->size(); $i += 1){
					$ar = $g->readNode()->toArray();
					$a['Content'][$i] = $ar;
					$g = $g->getNext();
				}
			}
			return $a;
		}
		
		public function getPage($con,$num,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'";	}
			$sql .= "WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getContentPage($con,$def,$inactive = false){
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
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data); }
			else{ error_log("SQL Collection->getContentPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }

		}
		public function getArchivePage($con,$num,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE d.Created >= ".strtotime($def."01 00:00:00")." AND d.Created <= ".strtotime($def.date('t',strtotime($def."01"))." 00:00:00")." AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getArchivePage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getRelPage($con,$num,$crel,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; if($crels[$i] == $crel){ $n = $c; } $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE r".$n.".Definition='".$def."' AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getRelPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function getAuthorPage($con,$num,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$da = explode(" ",$def);
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.Author=(SELECT DBO_ID FROM Users WHERE First ='".$da[0]."' AND Last = '".$da[1]."') AND c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
			$data = mysqli_query($con,$sql);
			if($data){ return $this->processMYSQL($data);}
			else{ error_log("SQL Collection->getAuthorPage: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
		}
		public function rssGenFeed($con,$domain,$path,$cpath,$inactive = false){
			$crels = array();
			foreach($this->_contRels as $key => $val){ $crels[] = $key; }
			$sql = "SELECT c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			if(!$inactive){	$sql = str_replace("WHERE","WHERE c.Active=1 AND",$sql); }
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
		
		public function getContent(){ return $this->content; }
		public function getContRels(){ return $this->_contRels; }
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
		
		protected function setContent($con){
			$sql = "SELECT d.*, c.*, concat(u.First,' ',u.Last) as `_Signature`";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= ", group_concat(distinct concat(r".$c.".ID,':',r".$c.".RID,':',r".$c.".KID,':',r".$c.".Key,':',r".$c.".Code,':',r".$c.".Definition) separator ';') AS `".$crels[$i]."`"; }
			$sql .= " FROM DBObj d INNER JOIN ".$this->_ctable." c ON d.ID = c.DBO_ID LEFT JOIN Users u ON c.Author = u.DBO_ID LEFT JOIN Relationships r ON d.ID = r.RID AND r.Key = 'Parent'";
			for($i = 0; $i < count($crels); $i++){ $c = $i+1; $sql .= " LEFT JOIN Relationships r".$c." ON d.ID = r".$c.".RID AND r".$c.".Key = '".$crels[$i]."'"; }
			$sql .= " WHERE c.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY d.ID ORDER BY d.Created DESC";
			$data = mysqli_query($con,$sql);
			if($data){ $this->content = $this->processMYSQL($data); return true; }
			else{ error_log("SQL Collection->setContent: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); return false; }
			
		}
		protected function setContRels($con){
			foreach($this->_contRels as $key => $val){
				$sql = "SELECT distinct k.*, k.ID as KID, 0 as RID FROM `Keys` k left join Relationships r ON k.ID = r.KID WHERE k.`Key` = '".$key."' ORDER BY Definition ASC";
				$res = mysqli_query($con,$sql);
				$this->_contRels[$key] = new DBOList();
				while($row = mysqli_fetch_array($res)){
					$r = new Relation();
					$r->initMysql($row);
					$this->_contRels[$key]->insertLast($r);
				}
			}
		}
		protected function setPageSize($ps){ (int)$this->pageSize = $ps; }
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
		public function getCategories(){
			$rels = Collection::getContRels();
			return $rels['Category'];
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
					$p = new Media(NULL);
					$p->initMysql($row);
					$list->insertLast($p);
				}
				return $list;
			}else{ return false; }
		}
		public function getGalleries(){
			$rels = Collection::getContRels();
			return $rels['Gallery'];
		}
		public function getCategories(){
			$rels = Collection::getContRels();
			return $rels['Category'];
		}
	}
	
	class Site extends Content{
		
	}

	class Media extends Content{
		
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
	
	class Comment extends HTMLDoc{
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
	}
	
	class Post extends HTMLDoc{
		public $_signature;
		protected $coverImage;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Posts");
			Root::setRelationships(array('Parent'=>new Relationship("Blog","Parent"),'Category'=>new Relationship("Post","Category")));
			$this->coverImage = NULL;
		}
		public function dbRead($con){
			if(HTMLDoc::dbRead($con)){
				$sql = "SELECT concat(First,' ',Last) as `Signature` FROM Users WHERE DBO_ID = ".$this->getAuthor();
				$res = mysqli_query($con,$sql);
				$a = mysqli_fetch_array($res);
				$this->setSignature($a['Signature']);
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
			if(isset($row['_Signature'])){ $this->setSignature($row['_Signature']); }
			if(isset($row['CoverImage'])){ $this->setCoverImage($row['CoverImage']); }
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
		public function toArray(){
			$a = HTMLDoc::toArray();
			$a['_Signature'] = $this->getSignature();
			$a['CoverImage'] = $this->getCoverImage();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a",$ss = "http"){
			$html_out = HTMLDoc::view($html[0],$ds);
			$html_out = str_replace("{_Signature}",$this->getSignature(),$html_out);
			if($this->getCoverImage() != "" && $this->getCoverImage() != NULL){ $html_out = str_replace("{CoverImage}",url().$this->getCoverImage(),$html_out);}else{ $html_out = str_replace("{CoverImage}","",$html_out); }
			if(strpos($html[0],'{Category}') !== false && (isset($html[1]) && $html[1] != NULL)){ $html_out = str_replace("{Category}",$this->viewCategories($html[1]),$html_out); }
			return $html_out;
		}
		protected function viewCategories($html,$ds = "F j, Y, g:i a"){ return Root::viewRel("Category",$html,$ds); }
		
		public function getCategories(){ $rels = Root::getRelationships(); return $rels['Category']->getRels(); }
		protected function getCoverImage(){ return (string)$this->coverImage; }
		protected function getSignature(){ return (string)$this->_signature; }
		public function setCategories($con){ Root::setRelation("Post","Category",$con); }
		protected function setSignature($s){ (string)$this->_signature = $s; }
		protected function setCoverImage($i){ (string)$this->coverImage = $i; }
	}
	
	class Page extends HTMLDoc{
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
	}
?>