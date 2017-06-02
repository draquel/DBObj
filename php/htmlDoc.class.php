<?php
require_once("content.class.php");

class HTMLDoc extends Content{
	protected $html;

	public function __construct($id,$t){
		Content::__construct($id,$t);	
		$this->html = NULL;
	}
	public function dbRead($pdo){
		if(Content::dbRead($pdo)){
			return true;
		}else{ return false; }
	}
	public function dbWrite($pdo){
		if(Content::dbWrite($pdo)){
			return true;
		}else{ return false; }
	}
	public function dbDelete($pdo){
		if(Content::dbDelete($pdo)){
			return true;
		}else{ return false; }
	}
	public function init($row){ 
		Content::init($row);
		if(isset($row['HTML'])){ $this->setHTML($row['HTML']); }
	}
	protected function mysqlEsc($pdo){
		Content::mysqlEsc($pdo);
		$this->setHTML(mysqli_escape_string($pdo,$this->getHTML()));
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

?>