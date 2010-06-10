<?php

	class Tags extends Model{
		public $name = string;
		
		function __toString(){
			return "$this->name";
		}
	}