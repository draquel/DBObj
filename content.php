<?php
	class Content extends Root{
		protected $title;
		protected $desciption;
		protected $hidden;
		
		public function __construct(){
			Root::__construct();	
			$this->title = NULL;
			$this->description = NULL;
			$this->hidden = NULL;
		}
		public function init($id,$t,$d,$h,$cd,$ud){
			Root::init($id,$cd,$ud);
			$this->setTitle($t);
			$this->setDescription($d);
			$this->setHidden($h);
		}
		public function initMysql($row){ 
			Root::initMysql($row);
			$this->setTitle($row['Title']);
			$this->setDescription($row['Description']);
			$this->setHidden($row['Hidden']);
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDescription();
			$a['Hidden'] = $this->getHidden();
			return $a;
		}
		
		protected function getTitle(){ return $this->title; }
		protected function getDesciption(){ return $this->description; }
		protected function getHTML(){ return $this->html; }
		protected function getHidden(){ return $this->hidden; }
		
		protected function setAuthor($a){ $this->author = $a; }
		protected function setTitle($t){ $this->title = $t; }
		protected function setDescription($d){ $this->description = $d; }
		protected function setHidden($h){ $this->hidden = $h; }
	}
	
	class Blog extends Content{
		protected $posts;
		protected $categories;
		protected $pageSize;
		
		public function __construct(){
			Root::__construct();	
			$this->posts = new DBOList();
			$this->categorites = new DBOList();
			$this->pageSize = 0;
		}
		public function init($id,$t,$d,$h,$p,$c,$ps,$cd,$ud){
			Content::init($id,$t,$d,$h,$cd,$ud);
			$this->setPosts($p);
			$this->setCategories($c);
			$this->setPageSize($ps);
		}
		public function initMysql($row){ 
			Content::initMysql($row);
			$this->setPosts($row['Posts']);
			$this->setCategories($row['Categories']);
			$this->setPageSize($row['PageSize']);
		}
		public function toArray(){
			$a = Content::toArray();
			$a['Posts'] =  array();
			$g = $this->getPosts()->getFirstNode();
			for($i = 0; $i < $this->getPosts()->size(); $i += 1){
				$ar = $g->readNode()->toArray();
				$a['Posts'][$i] = $ar;
				$g = $g->getNext();
			}
			$a['Categories'] =  array();
			$g = $this->getCategories()->getFirstNode();
			for($i = 0; $i < $this->getCategories()->size(); $i += 1){
				$ar = $g->readNode()->toArray();
				$a['Categories'][$i] = $ar;
				$g = $g->getNext();
			}
			$a['PageSize'] = $this->getPageSize();
			return $a;
		}
		
		protected function getPosts(){ return $this->posts; }
		protected function getCategories(){ return $this->categories; }
		protected function getPageSize(){ return $this->page_size; }
		
		protected function setPosts($p){ $this->posts = $p; }
		protected function setCategories($c){ $this->categories = $c; }
		protected function setPageSize($ps){ $this->pageSize = $ps; }
	}
	
	class Site extends Content{
		
	}
	
	class HTMLDoc extends Content{
		protected $author;
		protected $html;
		
		public function __construct(){
			Content::__construct();	
			$this->author = NULL;
			$this->html = NULL;
		}
		public function init($id,$a,$t,$d,$html,$h,$cd,$ud){
			Content::init($id,$t,$d,$h,$cd,$ud);
			$this->setAuthor($a);
			$this->setHTML($html);
		}
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
		
		protected function getAuthor(){ return $this->author; }
		protected function getHTML(){ return $this->html; }
		
		protected function setAuthor($a){ $this->author = $a; }
		protected function setHTML($h){ $this->html = $h; }
	}
	
	class Comment extends HTMLDoc{
		protected $post_id;
		protected $approved;
		
		public function __construct(){
			HTMLDoc::__construct();
			$this->uri = NULL;
		}
		public function init($id,$a,$t,$d,$html,$h,$pid,$ap,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setPostID($pid);
			$this->setApproved($ap);
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
		protected $coverImage;
		
		public function __construct(){
			HTMLDoc::__construct();
			Root::setRelationships(array('Categories'=>new Relationship("Post","Category")));
			$this->coverImage = NULL;
		}
		public function init($id,$a,$t,$d,$html,$h,$ci,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setCoverImage($ci);
		}
		public function initMysql($row){ 
			HTMLDoc::initMysql($row);
			$this->setCoverImage($row['CoverImage']);
		}
		public function toArray(){
			$a = HTMLDoc::toArray();
			$a['CoverImage'] = $this->getCoverImage();
			return $a;
		}
		
		protected function getCoverImage(){ return $this->coverImage; }
		protected function setCoverImage($i){ $this->coverImage = $i; }
	}
	
	class Page extends HTMLDoc{
		protected $uri;
		
		public function __construct(){
			HTMLDoc::__construct();
			$this->uri = NULL;
		}
		public function init($id,$a,$t,$d,$html,$h,$u,$cd,$ud){
			HTMLDoc::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setURI($u);
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
		
		protected function getURI(){ return $this->uri; }
		protected function setURI($u){ $this->uri = $u; }
	}
?>