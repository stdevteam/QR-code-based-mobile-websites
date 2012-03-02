<?php
/**
 * LinkedInc Component
 *
 * This component is used to get Linkedin info
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Dashboard
 * @property 
 */
class LinkedincComponent extends Object{
    /**
     * The dependency components needed
     * @var array An array of component names
     */
    public $components = array('Session','Auth', 'Linkedin.Linkedin' => array(
                    'key' => 'z7cmrjjmfus5',
                    'secret' => 't3STwNVxdM6ERHHq',
            ),'OauthConsumer');

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
    
    public function getFriends($id){
        http://api.linkedin.com/v1/people/id=12345/connections
        $this->Linkedin->connect(array('action' => 'linkedinRet'));
        $this->Linkedin->authorize(array('action' => 'linkedinSave'));
    
        $response = $this->Linkedin->call('people/~', array(
            'id',
            'picture-url',
            'connections',
            'first-name', 'last-name', 'summary', 'specialties', 'associations', 'honors', 'interests', 'twitter-accounts',
            'positions' => array('title', 'summary', 'start-date', 'end-date', 'is-current', 'company'),
            'educations',
            'certifications',
            'skills' => array('id', 'skill', 'proficiency', 'years'),
            'recommendations-received',
                ));
     var_Dump($response);die;
    }
}
?>