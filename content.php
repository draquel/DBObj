<?php
	class Content extends Root{
		protected $author;
		protected $title;
		protected $desciption;
		protected $html;
		protected $hidden;
		
		public function __construct(){
			Root::__construct();	
			$this->author = NULL;
			$this->title = NULL;
			$this->description = NULL;
			$this->html = NULL;
			$this->hidden = NULL;
		}
		public function init($id,$a,$t,$d,$html,$h,$cd,$ud){
			Root::init($id,$cd,$ud);
			$this->setAuthor($a);
			$this->setTitle($t);
			$this->setDescription($d);
			$this->setHTML($html);
			$this->setHidden($h);
		}
		public function initMysql($row){ 
			Root::initMysql($row);
			$this->setAuthor($row['author']);
			$this->setTitle($row['title']);
			$this->setDescription($row['description']);
			$this->setHTML($row['html']);
			$this->setHidden($row['hidden']);
		}
		public function toArray(){
			$a = Root::toArray();
			$a['Author'] = $this->getAuthor();
			$a['Title'] = $this->getTitle();
			$a['Description'] = $this->getDescription();
			$a['HTML'] = $this->getHTML();
			$a['Hidden'] = $this->getHidden();
			return $a;
		}
		
		protected function getAuthor(){ return $this->author; }
		protected function getTitle(){ return $this->title; }
		protected function getDesciption(){ return $this->description; }
		protected function getHTML(){ return $this->html; }
		protected function getHidden(){ return $this->hidden; }
		
		protected function setAuthor($a){ $this->author = $a; }
		protected function setTitle($t){ $this->title = $t; }
		protected function setDescription($d){ $this->description = $d; }
		protected function setHTML($h){ $this->html = $h; }
		protected function setHidden($h){ $this->hidden = $h; }
	}
	
	class Post extends Content{
		public function __construct(){
			Content::__construct();
			Root::setRelationships(array('Categories'=>new Relationship("Post","Category")));
		}
	}
	
	class Page extends Content{
		protected $uri;
		
		public function __construct(){
			Content::__construct();
			$this->uri = NULL;
		}
		
		public function init($id,$a,$t,$d,$html,$h,$u,$cd,$ud){
			Content::init($id,$a,$t,$d,$html,$h,$cd,$ud);
			$this->setURI($u);
		}
		public function initMysql($row){ 
			Content::initMysql($row);
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
?>