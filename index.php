<?php
	require_once('rivet/Rivet.php');
	
	
	// Homepage
	$rivet->route('^/$', function($r){
		return new Template('homepage.html');
	}, $name='home');
	
	// Hello thar! :D
	$rivet->route('^/hello-world/?$', function($r){
		echo 'Hello World<br><br>';
	}, $name='hello');
	
	// URL params & Template usage!
	$rivet->route('^/work/([\w-]+)/([\d]+)/?$', function($r, $foo, $bar){
		return new Template('param_test.html', array(
			'foo' => $foo,
			'bar' => $bar,
			'test_variable' => array('a', 'b', 'c', 'd')
		));
	}, $name='work');
	
	// Redirects
	$rivet->route('^/foo/?$', function($r){
		return redirect($r->getRoute('redirect-end')->reverse());
	}, $name='redirect');
	
	$rivet->route('^/bar/?$', function($r){
		return new Template('redirect_bar.html');
	}, $name='redirect-end');
	
	// 404 Page!
	$rivet->route('^/404/?$', function($r){
		return notfound();
	}, $name='404');
	
	
	
	//========================================================
	echo $rivet->dispatch();