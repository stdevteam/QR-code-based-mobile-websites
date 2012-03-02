<?php
/**
 * Dashboard Component
 *
 * This component is used to generate user related info for logged in user dahsboard
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Dashboard
 * @property 
 */
class DashboardComponent extends Object{
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

    /**
     * Startup component
     *
     * @param object $controller Instantiating controller
     * @access public
     */
    public function startup(&$controller) {}
    /**
     * check for user related info and return values , or if not exist , genearate them
     */
    public function getData(){
        if(!$this->Session->check('Dashboard')){
            $this->generateDashboard();
        }
        return $this->Session->read('Dashboard');
    }
    
    public function generateDashboard(){
        $uId = $this->Auth->getUser();
        
        $this->controller->loadModel('Place');
        $this->controller->loadModel('PlacePhoto');
        $this->controller->loadModel('Pet');
        $this->controller->loadModel('Profile');
        $this->controller->loadModel('Recommendation');
        $this->controller->loadModel('User');
        $this->controller->loadModel('Service');
        
        $userData = array();
        
        /*$recommendation = $this->controller->Recommendation->All($uId);
        $userData['Recommendation']=$recommendation;*/
        $rContent = $this->controller->Recommendation->AllForUser($uId);
        $userData['Recommendation']['count'] = count($rContent);
        $userData['Recommendation']['content'] = $rContent;
        
        $place = $this->controller->Place->findByid_user($uId);
        $userData['Place'] = $place['Place'];
        
        $user = $this->controller->User->findByid($uId);
        $userData['User'] = $user['User'];
        
        $service = $this->controller->Service->findByuser_id($uId);
        $userData['Service'] = $service['Service'];
        
        $placePhotos = $this->controller->PlacePhoto->getPrimaryPhoto($uId,$place['Place']['id']);
        if(is_array($placePhotos) && count($placePhotos) > 0){
            $userData['PlacePhoto'] = $placePhotos[0]['place_photos'];
        }else{
            $userData['PlacePhoto'] = NULL;
        }
        $allPlacePhotos = $this->controller->PlacePhoto->getAllPhotos($uId,$place['Place']['id']);
        $userData['allPlacePhotos'] = $allPlacePhotos;
        
        $profile = $this->controller->Profile->findByid_user($uId);
        $userData['Profile'] = $profile['Profile'];
        
        $pets = $this->controller->Pet->getAllDogs($uId);
        $userData['Pet'] = $pets;
        
        $this->Session->write('Dashboard',$userData);
    }
    public function cleanUp(){
        $this->Session->delete('Dashboard');
    }
    
    public function getListingId(){
        $data = $this->getData();
        if(is_null($data['Place'])){
            $this->Session->write('Note.error','At first please tell us about you');
            $this->controller->redirect('/newprofiles/');
        }
        return $data['Place']['id'];
    }
    public function checkListing(){
        $data = $this->getData();
        if(is_null($data['Place'])){
            return NULL;
        }
        return $data['Place']['id'];
    }
    
    public function updateListing(){
        $data = $this->getData();
        if(!is_null($data['Place'])){
            $data['Place']['modified'] = gmdate("Y-m-d H:i:s");       
            //$data['Place']['id'] = $data['Place']['id'];
            $this->controller->loadModel('Place');
            $this->controller->Place->save($data['Place']);       
        }
    }    
}
