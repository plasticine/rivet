<?php
	
	function notfound() {
		$body = new Template('404.html');
		return new Response($body, '404');
	}

	function redirect($url='', $named_route='') {
		$redirect = 'http://'.$_SERVER['HTTP_HOST'].$url;
		return new Response('body', '301', array(
			'Location' => $redirect
		));
	}

	function error() {
		# code...
	}