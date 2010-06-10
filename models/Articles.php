<?php

	class Articles extends Model{
		public $title;
		public $date;
		public $tags;
		public $body;
		
		function __toString(){
			return "$this->title";
		}
		
		public function order_by(){
			return $this->date;
		}
	}