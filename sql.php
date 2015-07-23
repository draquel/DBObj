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
				$res = mysql_query("SELECT * FROM sites",$db->con("Webjynx"));
				while($row = mysql_fetch_array($res)){echo "Site : ". $row['sid']. " :: ". $row['name'] . "<br />";	}
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
				$this->con['$db'] = mysql_connect($this->server,$this->user,$this->pass);
				return mysql_select_db($db, $this->con['$db']);
			}
			private function getCon($db){
				if(!$this->isConnected($db)){return FALSE;}
				else{return $this->con['$db'];}
			}
			private function isConnected($db){
				if(!$this->con['$db']){return FALSE;}
				else{return TRUE;}	
			}
			private function isInitialized(){
				if($this->server == NULL || $this->user == NULL || $this->pass == NULL){return FALSE;}
				else{return TRUE;}
			}
		//Public Functions
			//constructors
			public function __construct(){
				$this->user = NULL;
				$this->pass = NULL;
				$this->server = NULL;
				$this->con = NULL;
			}
			//Returns True if all values are set False if not.
			public function init($s,$u,$p){
				$this->setUser($u);
				$this->setPass($p);
				$this->setServer($s);
				if(!$this->isInitialized()){return FALSE;}else{return TRUE;}
			}
			//Returns True is the connection is successful False if not.
			public function connect($db){
				if($this->isInitialized()){return $this->setCon($db);}else{return FALSE;}	
			}
			//Returns the requested Resource object or False if invalid selection.
			public function con($db){if($this->isConnected($db)){return $this->getCon($db);}else{if($this->connect($db)){return $this->getCon($db);}else{return $this->isConnected($db);}}}
			//Disconnects the requested resouce object
			public function disconnect($db){
				if($this->getCon($db)){
					return mysql_close($this->getCon($db));
				}else{ return FALSE; }
			}
		}
?>