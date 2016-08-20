<?php
	class Content extends Root{
		protected $title;
		protected $desciption;
		protected $active;
		
		public function __construct($id,$t){
			Root::__construct($id,$t);	
			$this->title = NULL;
			$this->description = NULL;
			$this->active = NULL;
		}
/*		public function init($id,$t,$d,$h,$cd,$ud){
			Root::init($id,"Content",$cd,$ud);
			$this->setTitle($t);
			$this->setDescription($d);
			$this->setHidden($h);
		}*/
		public function initMysql($row){ 
			Root::initMysql($row);
			$this->setTitle($row['Title']);
			$this->setDescription($row['Description']);
			$this->setActive($row['Active']);
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDesciption();
			$a['Active'] = $this->getActive();
			return $a;
		}
		
		public function getTitle(){ return (string)$this->title; }
		public function getDesciption(){ return (string)$this->description; }
		public function getActive(){ return (int)$this->active; }
		
		protected function setTitle($t){ (string)$this->title = $t; }
		protected function setDescription($d){ (string)$this->description = $d; }
		protected function setActive($a){ $this->active = (int)$a; }
	}
	
	class Blog extends Content{
		protected $pageSize;
		protected $posts;
		protected $users;
		protected $categories;
		
		public function __construct($id){
			Content::__construct($id,"Blogs");	
			$this->posts = new DBOList("Posts");
			$this->users = new DBOList("Users");
			$this->categories = new DLList();
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
		
		public function getPage($num){
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$this->getPageSize()); $end = $num*$this->getPageSize(); }else{ $start = 1; $end = $this->getPageSize(); }
			while($post != NULL){
				if($i >= $start && $i <= $end){
					$p = $post->readNode();	
					$page->insertLast($p);	
				}elseif($i > $this->getPosts()->size() || $i > $end){ break; }
				$i++;
				$post = $post->getNext();
			}
			return $page;
		}
		public function getArchivePage($num,$def){
			$page = new DLList();
			$archive = $this->getPosts()->getArchive();
			$posts = $archive[$def];
			if($num > 1){ $start = ($num-1)*$this->getPageSize(); $end = $num*$this->getPageSize()-1; }else{ $start = 0; $end = $this->getPageSize()-1; }
			for($i = 0; $i < count($posts); $i++){
				$p = $posts[$i]->toArray();
				if(date("Ym",$p['Created']) == $def){
					if($i >= $start && $i <= $end){
						$page->insertLast($posts[$i]);	
					}elseif($i > $this->getPosts()->size() || $i > $end){ break; }
				}
			}
			return $page;
		}
		public function getCategoryPage($num,$def){
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$this->getPageSize()); $end = $num*$this->getPageSize(); }else{ $start = 1; $end = $this->getPageSize(); }
			while($post != NULL){
				$hasCat = false;
				$p = $post->readNode()->toArray();
				if(count($p['Rels']['Category']) > 0){ foreach($p['Rels']['Category'] as $v){ if($v['Definition'] == $def){ $hasCat = true; break; } } }
				if($hasCat){
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
		public function getAuthorPage($num,$def){
			$author = $this->getUsers()->getFirstNode();
			while($author != NULL){
				$a = $author->readNode()->toArray();
				if($a['First']." ".$a['Last'] == $def){ break; }
				$author = $author->getNext();
			}
			$page = new DLList();
			$post = $this->getPosts()->getFirstNode();
			$i = 1;
			if($num > 1){ $start = 1 + (($num-1)*$this->getPageSize()); $end = $num*$this->getPageSize(); }else{ $start = 1; $end = $this->getPageSize(); }
			while($post != NULL){
				$byAuth = false;
				$p = $post->readNode()->toArray();
				if($a['ID'] == $p['Author']){ $byAuth = true; }				
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
		public function load($con){
			$this->setPosts($con);
			$this->setCategories($con);
			$this->setUsers($con);
		}
		public function getPosts(){ return $this->posts; }
		public function getUsers(){ return $this->users; }
		public function getCategories(){ return $this->categories; }
		public function getPageSize(){ return (int)$this->pageSize; }
		
		protected function setPosts($con){
			$sql = "SELECT p.* FROM Posts p LEFT JOIN Relationships r ON p.ID = r.RID AND r.Key = 'PostParent' WHERE p.PID=".$this->getID()." AND r.Code = '".rtrim($this->getTable(),"s")."' ORDER BY p.Created DESC";
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$p = new Post(NULL);
				$p->initMysql($row);
				$p->setCategories($con);
				$this->posts->insertLast($p);
			}	
		}
		protected function setUsers($con){
			$sql = "SELECT * FROM Users ORDER BY Created DESC";
			$res = mysqli_query($con,$sql);
			while($row = mysqli_fetch_array($res)){
				$u = new User(NULL);
				$u->initMysql($row);
				$u->setContactInfo($con);
				$this->users->insertLast($u);
			}	
		}
		protected function setCategories($con){
			$sql = "SELECT *, k.ID as KID, 0 as RID FROM `Keys` k WHERE k.`Key` = 'Category'";
			$res = mysqli_query($con,$sql);
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
		public function initMysql($row){ 
			Content::initMysql($row);
			$this->setAuthor($row['Author']);
			$this->setHTML($row['HTML']);
		}
		public function toArray(){
			$a = Content::toArray();
			$a['Author'] = $this->getAuthor();
			$a['HTML'] = $this->getHTML();
			return $a;
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
		protected $coverImage;
		
		public function __construct($id){
			HTMLDoc::__construct($id,"Posts");
			Root::setRelationships(array('Category'=>new Relationship("Post","Category")));
			$this->coverImage = NULL;
		}
/*		public function init($id,$a,$t,$d,$html,$h,$ci,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setCoverImage($ci);
		}*/
		public function initMysql($row){ 
			HTMLDoc::initMysql($row);
			$this->setCoverImage($row['CoverImage']);
		}
		public function toArray(){
			$a = HTMLDoc::toArray();
			$a['CoverImage'] = $this->getCoverImage();
			return $a;
		}
		protected function getCategories(){ $rels = Root::getRelationships(); return $rels['Category']->getRels(); }
		protected function getCoverImage(){ return (string)$this->coverImage; }
		public function setCategories($con){ Root::setRelation("Post","Category",$con); }
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