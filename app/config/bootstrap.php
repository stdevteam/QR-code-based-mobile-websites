<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
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
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
Configure::write("FB_APP_ID", '307097772660523');//your app id
Configure::write("FB_APP_SECRET", 'c4711d8e399c61f345d822a41089dbfc');//your app secrete
/*Configure::write("APP_URL", 'http://apps.facebook.com/my-app/');//you app url like this http://apps.facebook.com/my-app/
Configure::write("SITE_URL", 'http://your site url/my-app/');
*/
Configure::write("dogVacayFee", '15');//
if(0 < Configure::read('debug')){
    Configure::write('CHECKOUT', true);
    
    //Payment credentials for beta
    Configure::write('AUTHORIZE_LOGIN_ID', '9dK7pX7q');
    Configure::write('AUTHORIZE_TRANSACTION_KEY', '5624HvJaUn53LwAj');
    
    Configure::write('PAYPAL_USERNAME', 'xxx');
    Configure::write('PAYPAL_PASSWORD', 'xxx');
    Configure::write('PAYPAL_KEY', 'xxx_xxx');
    
    Configure::write('PAYMENT_LIVE', false);
}else{
    //Payment credentials for live
    Configure::write('AUTHORIZE_LOGIN_ID', '123');
    Configure::write('AUTHORIZE_TRANSACTION_KEY', 'xxx');
    
    Configure::write('PAYPAL_USERNAME', 'xxx');
    Configure::write('PAYPAL_PASSWORD', 'xxx');
    Configure::write('PAYPAL_KEY', 'xxx_xxx');
    
    Configure::write('PAYMENT_LIVE', true);
}
