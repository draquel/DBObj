<?php
require_once("htmlDoc.class.php");
require_once("relationship.class.php");

class Post extends HTMLDoc{
	protected $coverImage;
	protected $published;

	public function __construct($id){
		HTMLDoc::__construct($id,"Posts");
		Root::setRelationships(array('Parent'=>new Relationship("Blog","Parent"),'Category'=>new Relationship("Post","Category")));
		$this->coverImage = NULL;
	}
	public function dbRead($pdo){
		if(HTMLDoc::dbRead($pdo)){
			return true;
		}else{ return false; }
	}
	public function dbWrite($pdo){
		if(HTMLDoc::dbWrite($pdo)){
			return true;
		}else{ return false; }
	}
	public function dbDelete($pdo){
		if(HTMLDoc::dbDelete($pdo)){
			return true;
		}else{ return false; }
	}
	public function init($row){ 
		HTMLDoc::init($row);
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
			$relations['Category']->init($categories);
			$this->setRelationships($relations);
		}
	}
	protected function mysqlEsc($pdo){
		HTMLDoc::mysqlEsc($pdo);
		$this->setCoverImage(mysqli_escape_string($pdo,$this->getCoverImage()));
		$this->setPublished(mysqli_escape_string($pdo,$this->getPublished(NULL)));
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
	public function setCategories($pdo){ Root::setRelation("Post","Category",$pdo); }
	protected function setCoverImage($i){ $this->coverImage = (string)$i; }
	protected function setPublished($i){ $this->published = (int)$i; }
}

?>