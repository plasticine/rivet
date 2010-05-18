<?php
	
	function notfound() {
		$body = new Template('404.html');
		return new Response($body, '404');
	}

	function redirect($url) {
		$redirect = 'http://'.$_SERVER['HTTP_HOST'].$url;
		return new Response('', '301', array(
			'Location' => $redirect
		));
	}

	function error() {
		$body = new Template('500.html');
		return new Response($body, '500');
	}