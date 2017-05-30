<?php
require_once("collection.class.php");
require_once("dbolist.class.php");
require_once("post.class.php");

class Blog extends Collection{
	public function __construct($id){
		Collection::__construct($id,"Blogs","Posts",array("Category"=>new DBOList()));
	}
	protected function processMYSQL($data){
		if($data){
			$list = new DLList();
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

?>