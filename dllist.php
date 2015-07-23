<?php
//DOUBLY LINKED LIST
	class DLList{
		private $_firstNode;
		private $_lastNode;
		private $_count;
	 
		public function __construct() {
			$this->_firstNode = NULL;
			$this->_lastNode = NULL;
			$this->_count = 0;
		}
		public function getFirstNode(){	return $this->_firstNode; }
		public function getLastNode(){ return $this->_lastNode; }
		public function isEmpty(){ return ($this->_firstNode == NULL); }
		public function size(){	return $this->_count; }
		public function insertFirst($data){
			$newLink = new Node($data);
	 
			if($this->isEmpty()){
				$this->_lastNode = $newLink;
			}else{
				$this->_firstNode->previous = $newLink;
			}
			$newLink->next = $this->_firstNode;
			$this->_firstNode = $newLink;
			$this->_count++;
		}
		public function insertLast($data){
			$newLink = new Node($data);
			if($this->isEmpty()){
				$this->_firstNode = $newLink;
			} else{
				$this->_lastNode->next = $newLink;
			}
			$newLink->previous = $this->_lastNode;
			$this->_lastNode = $newLink;
			$this->_count++;
		}
		public function insertAfter($key, $data){
			$current = $this->_firstNode;
			while($current->data != $key){
				$current = $current->next;
	 
				if($current == NULL)
					return false;
			}
			$newLink = new Node($data);
			if($current == $this->_lastNode){
				$newLink->next = NULL;
				$this->_lastNode = $newLink;
			} else {
				$newLink->next = $current->next;
				$current->next->previous = $newLink;
			}
			$newLink->previous = $current;
			$current->next = $newLink;
			$this->_count++;
	 
			return true;
		}
		public function swap($id1, $id2){//id = 0 to n 
			$temp = $this->getNodeAt($id1)->readNode();
			$this->getNodeAt($id1)->data = $this->getNodeAt($id2)->readNode();;
			$this->getNodeAt($id2)->data = $temp;
		}
		public function getNodeAt($id){
			$cur = $this->getFirstNode();
			$count = 0;
			while($count < $id && $cur != NULL){
				$cur = $cur->getNext();
				$count++;	
			}
			return $cur;
		}
		public function deleteFirstNode(){
			$temp = $this->_firstNode;
			if($this->_firstNode->next == NULL){
				$this->_lastNode = NULL;
			}else{
				$this->_firstNode->next->previous = NULL;
			}
			$this->_firstNode = $this->_firstNode->next;
			$this->_count--;
			return $temp;
		}
	   	public function deleteLastNode(){
			$temp = $this->_lastNode;
			if($this->_firstNode->next == NULL){
				$this->firtNode = NULL;
			}else{
				$this->_lastNode->previous->next = NULL;
			}
			$this->_lastNode = $this->_lastNode->previous;
			$this->_count--;
			return $temp;
		}
		public function deleteNode($key){
			$current = $this->_firstNode;
			while($current->data != $key){
				$current = $current->next;
				if($current == NULL)
					return null;
			}
			if($current == $this->_firstNode){
				$this->_firstNode = $current->next;
			}else{
				$current->previous->next = $current->next;
			}
			if($current == $this->_lastNode){
				$this->_lastNode = $current->previous;
			}else{
				$current->next->previous = $current->previous;
			}
	 
			$this->_count--;
			return $current;
		}
		public function deleteNodeAt($pos){
			$cur = $this->_firstNode;
			$p = 0;
			while($p != $pos && $cur != NULL){
				$p++;
				$cur = $cur->getNext();
			}
			if($cur != NULL){
				if($cur == $this->_firstNode){$this->_firstNode = $cur->next;}
				else{$cur->previous->next = $cur->next;}
				if($cur== $this->_lastNode){$this->_lastNode = $cur->previous;}
				else{$cur->next->previous = $cur->previous;}
				$this->_count--;	
			}
		}
		public function displayForward(){
			$current = $this->_firstNode;
			while($current != NULL){
				echo $current->readNode() . " ";
				$current = $current->next;
			}
		}
		public function displayBackward(){
			 $current = $this->_lastNode;
			 while($current != NULL){
				echo $current->readNode() . " ";
				$current = $current->previous;
			}
		}
	}
	class Node {
		public $data;
		public $next;
		public $previous;
	 
		function __construct($data){
			$this->data = $data;
		}
		public function readNode(){
			return $this->data;
		}
		public function getNext(){
			return $this->next;
		}
		public function getPrev(){
			return $this->previous;
		}
	}
	//END DOUBLY LINKED LIST
    ?>
