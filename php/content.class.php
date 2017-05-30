<?php
require_once("root.class.php");

class Content extends Root{
	protected $title;
	protected $desciption;
	protected $keywords;
	protected $author;
	public $_signature;
	protected $active;

	public function __construct($id,$t){
		Root::__construct($id,$t);	
		$this->title = NULL;
		$this->description = NULL;
		$this->keywords = array();
		$this->author = NULL;
		$this->_signature = NULL;
		$this->active = NULL;
	}
	public function dbRead($con){
		if(Root::dbRead($con)){
			$sql = "SELECT concat(First,' ',Last) as `Signature` FROM Users WHERE DBO_ID = ".$this->getAuthor();
			$res = mysqli_query($con,$sql);
			if(!$res){ error_log("SQL Content->dbRead: ".$sql); error_log("MYSQL ERROR: ".mysqli_error($con)); }
			else{
				$a = mysqli_fetch_array($res);
				$this->setSignature($a['Signature']);
			}
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
		if(isset($row['Author'])){ $this->setAuthor($row['Author']); }
		if(isset($row['_Signature'])){ $this->setSignature($row['_Signature']); }
		if(isset($row['Active'])){ $this->setActive($row['Active']); }
	}
	protected function mysqlEsc($con){
		Root::mysqlEsc($con);
		$this->setTitle(mysqli_escape_string($con,$this->getTitle()));
		$this->setDescription(mysqli_escape_string($con,$this->getDesciption()));
		// NEEDS ADJUSTMENT FOR ARRAY $this->setKeywords(mysqli_escape_string($con,$this->getKeywords()));
		$this->setAuthor(mysqli_escape_string($con,$this->getAuthor()));
		$this->setActive(mysqli_escape_string($con,$this->getActive()));
	}
	public function toArray(){
		$a = Root::toArray();
		$a['Title'] = $this->getTitle();
		$a['Description'] = $this->getDesciption();
		$a['Keywords'] = $this->getKeywords();
		$a['Author'] = $this->getAuthor();
		$a['_Signature'] = $this->getSignature();
		$a['Active'] = $this->getActive();
		return $a;
	}
	public function view($html,$ds = "F j, Y, g:i a"){
		$html = Root::view($html,$ds);
		$html = str_replace("{Title}",$this->getTitle(),$html);
		$html = str_replace("{Description}",$this->getDesciption(),$html);
		$html = str_replace("{Keywords}",implode(", ",$this->getKeywords()),$html);
		$html = str_replace("{Author}",$this->getAuthor(),$html);
		$html = str_replace("{_Signature}",$this->getSignature(),$html);
		$html = str_replace("{Active}",$this->getActive(),$html);
		return $html;
	}

	public function getTitle(){ return (string)$this->title; }
	public function getDesciption(){ return (string)$this->description; }
	public function getKeywords(){ return (array)$this->keywords; }
	public function getActive(){ return (int)$this->active; }
	protected function getAuthor(){ return (int)$this->author; }
	protected function getSignature(){ return (string)$this->_signature; }

	protected function setAuthor($a){ $this->author = (int)$a; }
	protected function setSignature($s){ $this->_signature = (string)$s; }
	protected function setTitle($t){ $this->title = (string)$t; }
	protected function setDescription($d){ $this->description = (string)$d; }
	protected function setKeywords($k){ $this->keywords = (array)$k; }
	protected function setActive($a){ $this->active = (int)$a; }
}

?>