<?php
require_once("content.class.php");
require_once("relationship.class.php");

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

	public function getCategories(){ $rels = Root::getRelationships(); return $rels['Category']->getRels(); }
	public function getGalleries(){ $rels = Root::getRelationships(); return $rels['Gallery']->getRels(); }
	protected function getURI(){ return (string)$this->uri; }
	protected function getType(){ return (string)$this->type; }
	protected function setURI($u){ (string)$this->uri = $u; }
	protected function setType($t){ (string)$this->type = $t; }
	public function setCategories($con){ Root::setRelation("Media","Category",$con); }
	public function setGalleries($con){ Root::setRelation("Media","Gallery",$con); }
}

?>