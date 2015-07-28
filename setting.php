<?php

	class Setting{
		private $id;
		private $title;
		private $value;

		public function __construct(){
			$this->id = NULL;
			$this->title = NULL;
			$this->value = NULL;
		}
		public function init($id,$title,$value){ 
			$this->setID($id);
			$this->setTitle($title);
			$this->setValue($value);
		}
		public function initMysql($row){ $this->init($row['ID'],$row['Title'],$row['Value']); }
		public function getArray(){ return array('ID'=>$this->getID(),'Title'=>$this->getTitle(),'Value'=>$this->getValue()); }

		private function mysqlEsc(){
			$this->setID(mysql_escape_string($this->getID()));
                        $this->setTitle(mysql_escape_string($this->getTitle()));
                        $this->setValue(mysql_escape_string($this->getValue()));
		}
		private function setID($id){ $this->id = $id;}
		private function setTitle($t){ $this->title = $t; }
		private function setValue($v){ $this->value = $v; }

		private function getID(){ return $this->id; }
		public function getTitle(){ return $this->title; }
		public function getValue(){ return $this->value; }
	}

?>
