<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */

	//Router::connect('/', array('controller' => 'contents', 'action' => 'home'));
	Router::connect('/', array('controller' => 'users', 'action' => 'login'));
	Router::connect('/offer', array('controller' => 'contents', 'action' => 'home'));

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
	
	Router::connect('/places/', array('controller' => 'places','action' => 'search'));
	Router::connect('/places/page::page', array('controller' => 'places', 'action' => 'search'),  array('page' => '[0-9]+'));
	Router::connect('/:controller/:id', array('action' => 'index'),  array('id' => '[0-9]+'));
	
	Router::connect('/:controller/:id/:preview', array('action' => 'index'), array('pass' => array('id', 'preview'), 'id' => '[0-9]+'));
	
	Router::connect('/HowItWorks', array('controller' => 'places', 'action' => 'HowItWorks'));
	Router::connect('/BenefitsAndSafety', array('controller' => 'places', 'action' => 'BenefitsAndSafety'));
	Router::connect('/WhyHost', array('controller' => 'places', 'action' => 'WhyHost'));
	
    Router::connect('/free/*', array('controller' => 'landings', 'action' => 'index'));

?>
