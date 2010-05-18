<?php

	class User extends Model{
		public $first_name = string;
		public $last_name = string;
		public $email = string;
		public $age = int;
		
		function __toString(){
			return "$this->first_name $this->last_name";
		}
	}