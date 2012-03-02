<?php
/**
 * Facebook Component
 *
 * This component is used to get facebook info
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Dashboard
 * @property 
 */
class FacebookComponent extends Object{
    /**
     * The dependency components needed
     * @var array An array of component names
     */
    public $components = array('Session','Auth');

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
        $this->controller->loadmodel('Profile');
        $user = $this->controller->Profile->findByid_user($id);
        $return = array();
        if($user){
            if(isset($user['Profile']['facebook']) && $user['Profile']['facebook'] != ''){
                App::import('Vendor', 'facebook', array('file' => 'facebook/facebook.php'));
                $facebook = new Facebook(array(
                            'appId' => Configure::read("FB_APP_ID"),
                            'secret' => Configure::read("FB_APP_SECRET"),
                    ));
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    $userFacebook = $facebook->api('/'.$user['Profile']['facebook']);
                    $friends = $facebook->api('/'.$user['Profile']['facebook'].'/friends');
                } catch (FacebookApiException $e) {
                    error_log($e);
                    $userFacebook = null;                    
                }
                if($userFacebook){
                    $return['facebook']['count'] = count($friends['data']);
                    if(count($friends['data']) > 0){
                        $limit = (count($friends['data'])>10)?10:count($friends['data']);
                        for($i=0; $i<$limit; $i++){
                            $return['facebook']['friends'][$i] = $friends['data'][$i];
                        }
                    }
                    $return['facebook']['data'] = $userFacebook;
                }else{
                    $return['facebook'] = null;
                }
            }else{
                $return['facebook'] = null;
            }
        }
        return $return;
    }
}
?>