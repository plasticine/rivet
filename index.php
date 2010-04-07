<?php
	require_once('rivet/Rivet.php');
	
	// Homepage
	$rivet->route('%^/$%', function($request){
		echo 'Welcome to the rivet homepage!';
	});
	
	// Hello thar! :D
	$rivet->route('%^/hello-world/$%', function($request){
		echo 'Hello World';
	}, $name='name-test');
	
	// URL params & Template usage!
	$rivet->route('%^/work/([\w-]+)/([\d]+)/$%', function($request, $foo, $bar){
		return new Template('param_test.html', array(
			'foo' => $foo,
			'bar' => $bar,
			'test_variable' => array('a', 'b', 'c', 'd')
		));
	}, $name='params');
	
	// Redirects
	$rivet->route('%^/foo/$%', function($request){
		return redirect('/bar');
	}, $name='redirect-test');
	$rivet->route('%^/bar$%', function($request){
		echo "This is bar! You have been redirected from foo!<br>";
	}, $name='redirect-test');
	
	
	
	echo $rivet->dispatch();
	
	/*
	echo "<hr>";
	echo "<strong>headers</strong>";
	echo "<pre>";
	print_r(headers_list());
	echo "</pre>";
	
	echo "<hr>";
	echo "<strong>server</strong>";
	echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";
	
	echo "<hr>";
	echo "<strong>routes</strong>";
	echo "<pre>";
	print_r($rivet::$routes);
	echo "</pre>";
	*/