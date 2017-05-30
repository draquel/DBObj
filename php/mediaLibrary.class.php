<?php
require_once("collection.class.php");
require_once("dllist.class.php");
require_once("media.class.php");

class MediaLibrary extends Collection{
	public function __construct($id){
		Collection::__construct($id,"MediaLibrarys","Media",array("Gallery"=>new DLList(),"Category"=>new DLList()));
	}
	protected function processMYSQL($data){
		if($data){
			$list = new DLList();
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

?>