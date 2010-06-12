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
			
			// Mini Database Test
			new Route('/contacts', 'contacts', function($request){
			    $form = new Form(array(
			        new TextField('name', array('required' => true), array('placeholder' => 'Contact Name')),
			        new EmailField('email', array('required' => true), array('placeholder' => 'Contact Email')),
			        new URLField('address', array('required' => false), array('placeholder' => 'http://')),
			    ));
			    
			    if( $request['method'] == 'post' ){
			        $form = $form($request['post']);
			        if($form->is_valid()){
    			        $contact = DB::model('Contacts', array(
    			            'name' => $form['name'],
    			            'email' => $form['email'],
    			            'url' => $form['address'],
    			            'date' => time()
    			        ));
    			        $contact->save();
    			    }
			    }
			    
			    return Template::render('contacts/index.html', array(
			        'form' => $form,
			        'contacts' => DB::model('Contacts')->order_by('date', 'desc')->all()
			    ));
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
				
				$test = '"hello world..." My AMPS are really good-lookin\' & my type is clean!';
				
				if( $request['method'] == 'post' ){
				    echo $request['post']['message']."<br>";
				    $form = $form($request['post']);
				    if($form->is_valid()){
				        echo $form['message']."<br>";
				    }
				}
				
				return Template::render('contact/index.html', array( 'form' => $form, 'test' => $test ));
			}),
			
			// Journal
			new Route('/journal/{?int:page}', 'journal', function($request, $page=0){
				return Template::render('journal/index.html', array( 
					'articles' => DB::model('Articles')->order_by('date', 'desc')->all()
				));
			}),
			new Route('/journal/{slug:article}', 'article', function($request, $article){
			    $article = DB::model('Articles')->where('slug', $article)->exec();
				return Template::render('journal/article.html', array(
					'article' => $article[0],
					'tags' => DB::model('Tag')->all()
				));
			}),
			new Route('/journal/{slug:tag}', 'tag', function($request, $tag){
				return Template::render('journal/tag.html', array( 'tag' => $tag ));
			}),
			
			// Redirects
			new Route('/foo', 'foo', function($request){
				return redirect(reverse('bar'));
			}),
			new Route('/bar', 'bar', function($request){
				return 'You were redirected from Foo!';
			}),
			
			// Error pages!
			new Route('/404', '404', function($request){
				return notfound();
			}),
			new Route('/500', '500', function($request){
				return error();
			}),
		)
	);
	
	echo Rivet::dispatch();