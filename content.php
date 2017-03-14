<?php
	class Content extends Root{
		protected $title;
		protected $desciption;
		protected $keywords;
		protected $active;
		
		public function __construct($id,$t){
			Root::__construct($id,$t);	
			$this->title = NULL;
			$this->description = NULL;
			$this->keywords = array();
			$this->active = NULL;
		}
/*		public function init($id,$t,$d,$h,$cd,$ud){
			Root::init($id,"Content",$cd,$ud);
			$this->setTitle($t);
			$this->setDescription($d);
			$this->setHidden($h);
		}*/
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
			if(isset($row['Active'])){ $this->setActive($row['Active']); }
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDesciption();
			$a['Keywords'] = $this->getKeywords();
			$a['Active'] = $this->getActive();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = Root::view($html,$ds);
			$html = str_replace("{Title}",$this->getTitle(),$html);
			$html = str_replace("{Description}",$this->getDesciption(),$html);
			$html = str_replace("{Keywords}",implode(", ",$this->getKeywords()),$html);
			$html = str_replace("{Active}",$this->getActive(),$html);
			return $html;
		}
		
		public function getTitle(){ return (string)$this->title; }
		public function getDesciption(){ return (string)$this->description; }
		public function getKeywords(){ return (array)$this->keywords; }
		public function getActive(){ return (int)$this->active; }
		
		protected function setTitle($t){ (string)$this->title = $t; }
		protected function setDescription($d){ (string)$this->description = $d; }
		protected function setKeywords($k){ (array)$this->keywords = $k; }
		protected function setActive($a){ $this->active = (int)$a; }
	}
	
	class Blog extends Content{
		protected $pageSize;
		protected $posts;
		protected $categories;
		
		public function __construct($id){
			Content::__construct($id,"Blogs");	
			$this->posts = new DBOList();
			$this->categories = new DBOList();
			$this->pageSize = 0;
		}
/*		public function init($id,$t,$d,$h,$p,$c,$ps,$cd,$ud){
			Content::init($id,$t,$d,$h,$cd,$ud);
			$this->setPosts($p);
			$this->setCategories($c);
			$this->setPageSize($ps);
		}*/
		public function initMysql($row){ 
			Content::initMysql($row);
			$this->setPageSize($row['PageSize']);
		}
		public function toArray(){
			$a = Content::toArray();
			if($this->getPosts()->size() != 0){
				$a['Posts'] =  array();
				$g = $this->getPosts()->getFirstNode();
				for($i = 0; $i < $this->getPosts()->size(); $i += 1){
					$ar = $g->readNode()->toArray();
					$a['Posts'][$i] = $ar;
					$g = $g->getNext();
				}
			}
			if($this->getCategories()->size() != 0){
				$a['Categories'] =  array();
				$g = $this->getCategories()->getFirstNode();
				for($i = 0; $i < $this->getCategories()->size(); $i += 1){
					$ar = $g->readNode()->toArray();
					$a['Categories'][$i] = $ar;
					$g = $g->getNext();
				}
			}
			$a['PageSize'] = $this->getPageSize();
			return $a;
		}
		
		public function getPage($num,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$pgSize); $end = $num*$pgSize; }else{ $start = 1; $end = $pgSize; }
			while($post != NULL){
				$p = $post->readNode();
				$pa = $p->toArray();
				if($pa['Active'] == 1 || $inactive){
					if($i >= $start && $i <= $end){
						$page->insertLast($p);
					}elseif($i > $end){ break; }
					$i++;
				}
				$post = $post->getNext();
			}
			return $page;
		}
		public function getPageLive($con,$num,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			//error_log("SQL Blog->getPage: ".$sql);
			$res = mysqli_query($con,$sql);
			//if(!$res){error_log("SQL Blog->getPage ERROR: ".mysqli_error($con));}
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$page->insertLast($p);
			}
			return $page;
		}
		public function getPostPageLive($con,$def,$inactive = false){
			$page = new DLList();
			$sql = "(SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND p.ID > ".$def." GROUP BY p.ID ORDER BY p.Created ASC LIMIT 1) UNION ALL (SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND p.ID=".$def." GROUP BY p.ID) UNION ALL (SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' AND p.ID < ".$def." GROUP BY p.ID ORDER BY p.Created DESC LIMIT 1)";
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			//error_log("SQL Blog->getPostPage: ".$sql);
			$res = mysqli_query($con,$sql);
			//if(!$res){error_log("SQL Blog->getPostPage ERROR: ".mysqli_error($con));}
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$page->insertLast($p);
			}
			return $page;
		}
		public function getArchivePage($num,$def,$pgSize = NULL){
			$page = new DLList();
			$archive = $this->getPosts()->getArchive();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$posts = $archive[$def];
			if($num > 1){ $start = ($num-1)*$pgSize; $end = $num*$pgSize-1; }else{ $start = 0; $end = $pgSize-1; }
			$i = 0;
			foreach($posts as $post){
				$p = $post->toArray();
				if(date("Ym",$p['Created']) == $def && $p['Active'] == 1){
					if($i >= $start && $i <= $end){
						$page->insertLast($post);	
					}elseif($i > $this->getPosts()->size() || $i > $end){ break; }
					$i++;
				}
			}
			return $page;
		}
		public function getArchivePageLive($con,$num,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.Created >= ".strtotime($def."01 00:00:00")." AND p.Created <= ".strtotime($def.date('t',strtotime($def."01"))." 00:00:00")." AND p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			//error_log("SQL Blog->getArchivePage: ".$sql);
			$res = mysqli_query($con,$sql);
			//if(!$res){error_log("SQL Blog->getArchivePage ERROR: ".mysqli_error($con));}
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$page->insertLast($p);
			}
			return $page;
		}
		public function getCategoryPage($num,$def,$pgSize = NULL){
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$pgSize); $end = $num*$pgSize; }else{ $start = 1; $end = $pgSize; }
			while($post != NULL){
				$hasCat = false;
				$p = $post->readNode()->toArray();
				if(count($p['Rels']['Category']) > 0){ foreach($p['Rels']['Category'] as $v){ if($v['Definition'] == $def){ $hasCat = true; break; } } }
				if($hasCat && $p['Active'] == 1){
					if($i >= $start && $i <= $end){
						$p = $post->readNode();	
						$page->insertLast($p);	
					}elseif($i > $this->getPosts()->size() || $i > $end){ break; }
					$i++;
				}
				$post = $post->getNext();
			}
			return $page;
		}
		public function getCategoryPageLive($con,$num,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$cat = $this->getCategories()->getFirstNode();
			while($cat != NULL){ 
				$c = $cat->readNode()->toArray();
				if($c['Definition'] == $def){ break; }else{ $c = NULL; }
				$cat = $cat->getNext();
			}
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE r2.KID=".$c['KID']." AND p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			//error_log("SQL Blog->getCategoryPage: ".$sql);
			$res = mysqli_query($con,$sql);
			//if(!$res){error_log("SQL Blog->getCategoryPage ERROR: ".mysqli_error($con));}
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$page->insertLast($p);
			}
			return $page;
		}
		public function getAuthorPage($num,$def,$users,$pgSize = NULL){
			$author = $users->getFirstNode();
			while($author != NULL){
				$a = $author->readNode()->toArray();
				if($a['First']." ".$a['Last'] == $def){ break; }
				$author = $author->getNext();
			}
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$pgSize); $end = $num*$pgSize; }else{ $start = 1; $end = $pgSize; }
			while($post != NULL){
				$byAuth = false;
				$p = $post->readNode()->toArray();
				if($a['ID'] == $p['Author'] && $p['Active'] == 1){ $byAuth = true; }				
				if($byAuth){
					if($i >= $start && $i <= $end){
						$p = $post->readNode();	
						$page->insertLast($p);	
					}elseif($i > $this->getPosts()->size() || $i > $end){ break; }
					$i++;
				}
				$post = $post->getNext();
			}
			return $page;
		}
		public function getAuthorPageLive($con,$num,$def,$pgSize = NULL,$inactive = false){
			$page = new DLList();
			if($pgSize == NULL){ $pgSize = $this->getPageSize(); }
			$start = ($num-1)*$pgSize;
			$da = explode(" ",$def);
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.Author=(SELECT ID FROM Users WHERE First ='".$da[0]."' AND Last = '".$da[1]."') AND p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC LIMIT ".$start.",".$pgSize;
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			//error_log("SQL Blog->getAuthorPage: ".$sql);
			$res = mysqli_query($con,$sql);
			//if(!$res){error_log("SQL Blog->getAuthorPage ERROR: ".mysqli_error($con));}
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$page->insertLast($p);
			}
			return $page;
		}
		public function load($con,$p = true,$c = true){
			if($p){ $this->setPosts($con); }
			if($c){ $this->setCategories($con); }
		}
		public function rssGenFeed($domain,$path){
			$out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
            <rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
                <channel>
                    <title>".$this->getTitle()."</title>
                    <link>".$domain."</link>
                    <description>".$this->getDesciption()."</description>
                    <atom:link href=\"".$domain.$path."\" rel=\"self\" type=\"application/rss+xml\" />";
                $post = $_SESSION['Blog']->getPosts()->getFirstNode();
                while($post != NULL){
                    $a = $post->readNode()->toArray();
                    $out .= "
                    <item>
                        <title>".$a['Title']."</title>
                        <link>".$domain."/blog/p/".$a['ID']."</link>
                        <guid>".$domain."/blog/p/".$a['ID']."</guid>
                        <description>".$a['Description']."</description>
                    </item>";
                    $post = $post->getNext();
                }
                $out .= "</channel>
            </rss>";
			return $out;
		}
		public function rssGenFeedLive($con,$domain,$path,$inactive = false){
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC";
			if(!$inactive){	$sql = str_replace("WHERE","WHERE p.Active=1 AND",$sql); }
			$res = mysqli_query($con,$sql);
			
			$out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
            <rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
                <channel>
                    <title>".$this->getTitle()."</title>
                    <link>".$domain."</link>
                    <description>".$this->getDesciption()."</description>
                    <atom:link href=\"".$domain.$path."\" rel=\"self\" type=\"application/rss+xml\" />";
                $post = $_SESSION['Blog']->getPosts()->getFirstNode();
                while($row = mysqli_fetch_array($res)){
                    $out .= "
                    <item>
                        <title>".$row['Title']."</title>
                        <link>".$domain."/blog/p/".$row['ID']."</link>
                        <guid>".$domain."/blog/p/".$row['ID']."</guid>
                        <description>".$row['Description']."</description>
                    </item>";
                }
                $out .= "</channel>
            </rss>";
			return $out;
		}
		public function getPost($con,$id){
			$p = new Post($id);
			$p->dbRead($con);
			return $p;
		}
		public function getPosts(){ return $this->posts; }
		/*public function getUsers(){ return $this->users; }*/
		public function getCategories(){ return $this->categories; }
		public function getPageSize(){ return (int)$this->pageSize; }
		
		protected function setPosts($con){
			$sql = "SELECT p.*, concat(u.First,' ',u.Last) as `_Signature`, group_concat(distinct concat(r2.ID,':',r2.RID,':',r2.KID,':',r2.Key,':',r2.Code,':',r2.Definition) separator ';') AS `Categories` FROM Posts p LEFT JOIN Users u ON p.Author = u.ID LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'Parent' LEFT JOIN Relationships r2 ON p.ID = r2.RID AND r2.Key = 'Category' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' GROUP BY p.ID ORDER BY p.Created DESC";
			$res = mysqli_query($con,$sql);
			$this->posts = new DBOList();
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$this->posts->insertLast($p);
			}	
		}
		protected function setCategories($con){
			$sql = "SELECT distinct k.*, k.ID as KID, 0 as RID FROM `Keys` k left join Relationships r ON k.ID = r.KID WHERE k.`Key` = 'Category' ORDER BY Definition ASC";
			$res = mysqli_query($con,$sql);
			$this->categories = new DBOList();
			while($row = mysqli_fetch_array($res)){
				$r = new Relation();
				$r->initMysql($row);
				$this->categories->insertLast($r);
			}
		}
		protected function setPageSize($ps){ (int)$this->pageSize = $ps; }
	}
	
	class Site extends Content{
		
	}
	
	class HTMLDoc extends Content{
		protected $author;
		protected $html;
		
		public function __construct($id,$t){
			Content::__construct($id,$t);	
			$this->author = NULL;
			$this->html = NULL;
		}
/*		public function init($id,$a,$t,$d,$html,$h,$cd,$ud){
			Content::init($id,$t,$d,$h,$cd,$ud);
			$this->setAuthor($a);
			$this->setHTML($html);
		}*/
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
			if(isset($row['Author'])){ $this->setAuthor($row['Author']); }
			if(isset($row['HTML'])){ $this->setHTML($row['HTML']); }
		}
		public function toArray(){
			$a = Content::toArray();
			$a['Author'] = $this->getAuthor();
			$a['HTML'] = $this->getHTML();
			return $a;
		}
		public function view($html,$ds = "F j, Y, g:i a"){
			$html = Content::view($html,$ds);
			$html = str_replace("{Author}",$this->getAuthor(),$html);
			$html = str_replace("{HTML}",$this->getHTML(),$html);
			return $html;
		}
		
		protected function getAuthor(){ return (string)$this->author; }
		protected function getHTML(){ return (string)$this->html; }
		
		protected function setAuthor($a){ (string)$this->author = $a; }
		protected function setHTML($h){ (string)$this->html = $h; }
	}
	
	class Comment extends HTMLDoc{
		protected $post_id;
		protected $approved;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Comments");
			$this->uri = NULL;
		}
/*		public function init($id,$a,$t,$d,$html,$h,$pid,$ap,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setPostID($pid);
			$this->setApproved($ap);
		}*/
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
/*		public function init($id,$a,$t,$d,$html,$h,$ci,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setCoverImage($ci);
		}*/
		public function dbRead($con){
			if(HTMLDoc::dbRead($con)){
				$sql = "SELECT concat(First,' ',Last) as `Signature` FROM Users WHERE ID = ".$this->getAuthor();
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
			if(isset($row['Categories'])){
				$row['Categories'] = explode(";",$row['Categories']);
				$categories = array();
				foreach($row['Categories'] as $cat){ 
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
/*		public function init($id,$a,$t,$d,$html,$h,$u,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setURI($u);
		}*/
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