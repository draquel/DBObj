<?php
	/*
		AUTHOR:: Daniel Raquel (draquel@webjynx.com)
		NOTES::
			MYSQL Connection Class
				This class manages mysql database connections. Each class object can control multiple connections to a single server.
		
		EXAMPLE::
		
			$db = new Sql();
			$db->init("localhost","root","PASSWORD");
			
			if(!$db->connect("Webjynx")){
				echo "CONNECTION FAILURE <br />";
			}else{
				echo "CONNECTED <br /> Querying Database.";
				$res = mysqli_query("SELECT * FROM sites",$db->con("Webjynx"));
				while($row = mysqli_fetch_array($res)){echo "Site : ". $row['sid']. " :: ". $row['name'] . "<br />";	}
			}		
	*/
		class Sql{
			private $user;
			private $pass;
			private $server;
			private $con;
			
		//Private Functions
			private function setUser($u){$this->user = $u;}
			private function setPass($p){$this->pass = $p;}
			private function setServer($s){$this->server = $s;}
			private function setCon($db){
				$this->con['$db'] = new MySQLi($this->server,$this->user,$this->pass,$db);
				return $this->con['$db'];
			}
			private function getCon($db){
				if(!$this->isConnected($db)){ return FALSE; }
				else{ return $this->con['$db']; }
			}
			private function isConnected($db){
				if(!$this->con['$db']){return FALSE;}
				else{ return TRUE; }	
			}
			private function isInitialized(){
				if($this->server == NULL || $this->user == NULL || $this->pass == NULL){return FALSE;}
				else{ return TRUE; }
			}
		//Public Functions
			public function __construct(){
				$this->user = NULL;
				$this->pass = NULL;
				$this->server = NULL;
				$this->con = NULL;
			}
			public function init($s,$u,$p){
				$this->setUser($u);
				$this->setPass($p);
				$this->setServer($s);
				if(!$this->isInitialized()){return FALSE;}else{return TRUE;}
			}
			public function connect($db){
				if($this->isInitialized()){return $this->setCon($db);}else{return FALSE;}	
			}
			public function con($db){
				if($this->isConnected($db)){ return $this->getCon($db); }
				else{ 
					if($this->connect($db)){ return $this->getCon($db); }
					else{ return $this->isConnected($db); }
				}
			}
			public function disconnect($db){
				if($this->getCon($db)){	return mysqli_close($this->getCon($db)); }
				else{ return FALSE; }
			}
		}
?>