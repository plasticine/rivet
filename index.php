<?php
	require_once('rivet/Rivet.php');
	
	Routes::create(
		array(
			// Homepage
			new Route('^/$', 'home', function(){
				return Template::render('homepage.html', array(
					'foo' => 'bar'
				));
			}),
			
			// Hello thar! :D
			new Route('^/database-test/?(page([\d]+))?/?$', 'db-test', function($page=0){
			    if( ! $page )
			        return redirect('page1/');
			    
			    $page = substr($page, 4);
			    $per_page = 10;
			    $num_users = DB::getInstance('User')->count();
			    $num_pages = range(1, ceil($num_users / $per_page));
			    
			    $users = DB::getInstance('User')->limit(($page-1)*$per_page, $per_page)->all();
				return Template::render('database.html', array(
				    'users' => $users,
				    'page' => $page,
				    'num_pages' => $num_pages
				));
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