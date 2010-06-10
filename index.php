<?php
	require_once('rivet/Rivet.php');
	
	Routes::create(
		array(
			// Homepage
			new Route('/', 'home', function($request){
				return Template::render('home/index.html');
			}),
			
			// About Page
			new Route('/about', 'about', function($request){
				return Template::render('about/index.html');
			}),
			
			// Contact Page
			new Route('/contact', 'contact', function($request){
			    $form = new Form(array(
			        new TextField('name', array('required' => true), array('placeholder' => 'Your Name')),
			        new EmailField('email', array('required' => true), array('placeholder' => 'Your Email')),
			        new URLField('address', array('required' => false), array('placeholder' => 'http://')),
			        new TextField('subject', array('required' => true), array('placeholder' => 'Subject')),
			        new TextBoxField('message', array('required' => true)),
			    ));
				
				if( $request['method'] == 'post' )
				    $form = $form($request['post']);
				    if($form->is_valid()){
				        // process form...
				        // mail('justin@pixelbloom.com', $form['subject'], $form['message']);
				        var_dump($form['subject']);
				    }
				    
				
				return Template::render('contact/index.html', array( 'form' => $form ));
			}),
			
			// Journal
			new Route('/journal/{?int:page}', 'journal', function($request, $page=0){
				return Template::render('journal/index.html', array( 
					'articles' => DB::getInstance('Articles')->order_by('date', 'desc')->all()
				));
			}),
			new Route('/journal/{slug:article}', 'article', function($request, $article){
			    $article = DB::getInstance('Articles')->where('slug', $article)->exec();
				return Template::render('journal/article.html', array(
					'article' => $article[0],
					'tags' => DB::getInstance('Tags')->all()
				));
			}),
			new Route('/journal/{slug:tag}', 'tag', function($request, $tag){
				return Template::render('journal/tag.html', array( 'tag' => $tag ));
			}),
			
			// 404 Page!
			new Route('/404', '404', function($request){
				return notfound();
			}),
		)
	);
	
	echo Rivet::dispatch();