<?php
require_once("content.class.php");

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
	protected function mysqlEsc($con){
		Content::mysqlEsc($con);
		$this->setHTML(mysqli_escape_string($con,$this->getHTML()));
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