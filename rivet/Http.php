<?php
	
	function notfound() {
		return new Response(Template::render('404.html'), '404');
	}

	function redirect($url) {
		$redirect = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$url;
		if(substr($url, 0, 1) == '/')
		    $redirect = 'http://'.$_SERVER['HTTP_HOST'].$url;
		
		return new Response('', '301', array(
			'Location' => $redirect
		));
	}

	function error() {
		$body = new Template('500.html');
		return new Response($body, '500');
	}