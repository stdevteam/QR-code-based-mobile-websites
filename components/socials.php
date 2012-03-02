<?php
/**
 * Social networks Component
 *
 * This component is used to get social networks info
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Dashboard
 * @property 
 */
class SocialsComponent extends Object{
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
                $return['facebook'] = $this->_processFacebook($user['Profile']['facebook']);
            }else{
                $return['facebook'] = null;
            }
            if(isset($user['Profile']['twitter']) && $user['Profile']['twitter'] != ''){
                $return['twitter'] = $this->_processTwitter($user['Profile']['twitter']);
            }else{
                $return['twitter'] = null;
            }
            if(isset($user['Profile']['linkedin']) && $user['Profile']['linkedin'] != ''){
                $return['linkedin'] = $this->_processLinkedin($user['Profile']['linkedin']);
            }else{
                $return['linkedin'] = null;
            }
        }
        return $return;
    }
    
    protected function _processFacebook($id){
        App::import('Vendor', 'facebook', array('file' => 'facebook/facebook.php'));
        $facebook = new Facebook(array(
                    'appId' => Configure::read("FB_APP_ID"),
                    'secret' => Configure::read("FB_APP_SECRET"),
            ));
        try {
            // Proceed knowing you have a logged in user who's authenticated.
            $userFacebook = $facebook->api('/'.$id);
            $friends = $facebook->api('/'.$id.'/friends');
        } catch (FacebookApiException $e) {
            error_log($e);
            $userFacebook = null;                    
        }
        if($userFacebook){
            $return['count'] = count($friends['data']);
            if(count($friends['data']) > 0){
                $start = (count($friends['data'])>10)?(rand(10, count($friends['data']))-10):0;
                $limit = (count($friends['data'])>10)?10:count($friends['data']);
                $limit = $limit + $start;
                for($i=$start; $i<$limit; $i++){
                    $return['friends'][$i] = $friends['data'][$i];
                }
            }
            $return['data'] = $userFacebook;
        }else{
            $return = null;
        }
        return $return;
    }
    
    protected function _processTwitter($id){
        $url = "http://api.twitter.com/1/users/lookup.json?user_id=".$id;
        $data = file_get_contents($url);
        if($data == false){
            return null;
        }
        $data = json_decode($data);
        if($data == false){
            return null;
        }
        if(!isset($data[0]->followers_count)){
            return null;;
        }
        $followersCount = $data[0]->followers_count;
        if(!is_numeric($followersCount)){
            return null;
        }
        $return['followersCount'] = $followersCount;
        return $return;
    }
    
    protected function _processLinkedin($id){
        $return = $id;
        return $return;
    }
}
?>