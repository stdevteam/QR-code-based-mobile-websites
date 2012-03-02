<?php
/**
 * Dashboard Component
 *
 * This component is used to generate user related info for logged in user dahsboard
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Auth
 * @property 
 */
class AuthComponent extends Object{
    /**
     * The dependency components needed
     * @var array An array of component names
     */
    public $components = array('Session');

    /**
     * Initialize component
     *
     * @param object $controller Instantiating controller
     * @access public
     */
    public function initialize(&$controller, $settings = array()) {
            $this->controller =& $controller;
            $this->_set($settings);
    }

    /**
     * Startup component
     *
     * @param object $controller Instantiating controller
     * @access public
     */
    public function startup(&$controller){}
    /**
     * check for user related info and return values , or if not exist , genearate them
     */
    public function getUser(){
        if(!$this->Session->check('User.id')){
            $this->Session->write('Redirect.url',$this->controller->here);                      
            $this->controller->redirect('/users/login/');
        }else{
            return $this->Session->read('User.id');
        }
    }
    /**
     * If user is logged in returns its id, otherwse redirects to $url or to current url
     * @param string $url [optional] The url to set as back url if user is not logged in, defaults to current url
     * @return int The id of the user or void as not logged in user will be redirected
     */
    public function getUserOrRedirect($url = null){
        $url = ((is_null($url))? $this->controller->here : $url);
        
        if(!$this->Session->check('User.id')){
            $this->Session->write('Redirect.url', $url);
            $this->controller->redirect('/users/login/');
        }else{
            return (int)$this->Session->read('User.id');
        }
    }
    /**
     * Returns the ID of the user if logged in user found, null otherwise
     * @return int|null The user id or null of no logged in user found
     */
    public function checkUser(){
        if(!$this->Session->check('User.id')){
            return NULL;
        }else{
            return (int)$this->Session->read('User.id');
        }
    }
    public function getManager(){
        if(!$this->Session->check('Manager.id')){
            $this->Session->write('Redirect.url',$this->controller->here);
            $this->controller->redirect('/managers/login/');
        }else{
            return $this->Session->read('Manager.id');
        }
    }
    public function checkManager(){
        if(!$this->Session->check('Manager.id')){
            return NULL;
        }else{
            return $this->Session->read('Manager.id');
        }
    }
}