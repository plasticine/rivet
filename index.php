<?php
	require_once('rivet/Rivet.php');
	
	Routes::create(
		array(
			// Homepage
			new Route('^/$', 'home', function(){
				$users = DB::getInstance('User');
				$all_users = $users->order_by('age', 'DESC')->all();
				$filtered_users = $users->where('first_name', 'Justin')->_or()->where('last_name', 'Jennings')->order_by('age')->exec();
				
				return Template::render('homepage.html', array(
					'foo'	=> 'bar',
					'filtered_users'	=>	$filtered_users,
					'all_users'	=>	$all_users
				));
			}),
			
			// Hello thar! :D
			new Route('^/hello-world/?$', 'hello', function(){
				return Template::render('hello-world.html', array('foo' => 'bar'));
			}),
			
			// URL params & Template usage!
			new Route('^/work/([\w-]+)/([\d]+)/?$', 'work', function($foo, $bar){
				return Template::render('param_test.html', array(
					'foo' => $foo,
					'bar' => $bar,
					'test_variable' => array('a', 'b', 'c', 'd')
				));
			}),
			
			// Redirects
			new Route('^/foo/?$', 'redirect-start', function(){
				return redirect(reverse('redirect-end'));
			}),
			new Route('^/bar/?$', 'redirect-end', function(){
				return Template::render('redirect_bar.html');
			}),
			
			
			// 404 Page!
			new Route('^/404/?$', '404', function(){
				return notfound();
			}),
		)
	);
	
	echo Rivet::dispatch();