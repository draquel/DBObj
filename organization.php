<?php
	class Organization extends Root{
		private $id;
		private $name;
		private $contacts;
		private $relations;
		private $settings;
	
		public __construct(){
			$this->id = NULL;
			$this->name = NULL;
			$this->contacts = new DLList();
			$this->relations = new DLList();
			$this->settings = array();
		}
		public init($id,$name){
			$this->id = $id;
			$this->name = $name;
		}
		public initMysql($row){}
		public initDB($id,$con){ }

		public updateContacts($con){ }
		public updateRelations($con){ }
		public updateSettings($con){ }

		public contactSearch(){ }

		private setID($id){ $this->id = $id; }
		private setName($n){ $this->name = $n; }
		private setContacts($c){ $this->contacts = $c; }
		private setRelations($r){ $this->relations = $r; }
		private setSettings($s){ $this->settings = $s; }

		private getID(){ return $this->id; }
		private getName(){ return $this->name; }
		private getContacts { return $this->contacts; }
		private getRelations(){ return $this->relations; }
		private getSettings(){ return $this->settings; }
	}

	class BG_Org extends Organization{
		private $donations;

		public __construct(){
			parent::__construct();
			$this->donations = new DLList();
		}
		
		private setDonations($d){ $this->donations = $d; }
		private getDonations(){ return $this->donations; }
	}
?>
