<?php
/**
 * Picture upload Component
 *
 * This component is used to upload pictures from listing process
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Listing
 * @property 
 */
class UploadHandlerComponent extends Object{
    /**
     * The dependency components needed
     * @var array An array of component names
     */
    public $components = array('Session','Auth', 'Image', 'ImageProcess', 'Dashboard','FileUploader' => array(
                'ext' => array('jpg', 'jpeg', 'gif', 'png')
            ));

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
    
    public function upload($action, $filepath, $isAjax ,$photoId = null ){
        $actions = array('profiles', 'dogs', 'places', 'slider');
        $u_id = $this->Auth->getUser();
        $info['u_id'] = $u_id;
        $info['ajax'] = $isAjax;
        $info['photoId'] = $photoId;
        foreach($actions as $key => $item){
            if($item == $action){
                $active = $key;
            }
        }
        
        if($active == 0){
            $return = $this->_processProfile($filepath,$info);
        }elseif($active == 1){
            $return = $this->_processPet($filepath,$info);
        }elseif($active == 2){
            $return = $this->_processPlace($filepath,$info);
        }
        return $return;
    }
    
    private function  _processProfile($filepath, $info = array()){      
        if(isset($info['u_id'])){
            $u_id = $info['u_id'];
        }
        $ajax = $info['ajax'];
        $imagePath = WWW_ROOT.SYSTEM_PATH_W."profiles/";
        $thumbPath = WWW_ROOT.SYSTEM_PATH_W."profiles/thumbs/";
        
        if($ajax){
            $return = $this->FileUploader->upload($imagePath);
            $return['image'] = $return['filename'];
        }else{
            $this->controller->loadmodel('Profile');
            $profile = $this->controller->Profile->findByid_user($u_id);          

            $extension = substr(strrchr($filepath, '.'), 1);
            $filename = $profile['Profile']['photo_path'];

            if(file_exists($imagePath.$filename) && $filename !== 'default_avatar.png'){
                unlink($imagePath.$filename);
            }

            $image = $u_id .'.'. $extension;
            @rename($imagePath . $filepath, $imagePath . $image);
            
            $return ['image'] = $image;

        }
        return $return;        
    } 
    
    /**
     *
     * @param type $filepath
     * @param type $info 
     */
    private function _processPet($filepath, $info = array()){
        
        $imagePath = WWW_ROOT.SYSTEM_PATH_W.'dogs/';
        $thumbPath = WWW_ROOT.SYSTEM_PATH_W.'dogs/thumbs/';
        
        $allowed = array('jpg','jpeg','gif','png');
        
        if(isset($info['u_id'])){
            $u_id = $info['u_id'];
        }
        if($info['photoId']){
            $photoId = $info['photoId'];
        }
        $isAjax = $info['ajax']; 
        if($isAjax){
            $result = $this->FileUploader->upload($imagePath);
            $result['image'] = $result['filename'];
        }else{             
            $name = explode('.', $filepath);
            $extension = end($name);

            if(in_array(strtolower($extension),$allowed)){
                $image = $photoId.".".$extension;
                if(file_exists($imagePath.$image)){
                    unlink($imagePath.$image);
                }
                rename($imagePath.$filepath, $imagePath.$image);
                $result['image'] = $image;                    
            }else{
                if(file_exists($imagePath.$filepath)){
                    unlink($imagePath.$filepath);
                }
                $result['errors'] = 'Invalid Image';
            }                                       
        }
        
        return $result;
        
        
        /*if($ajax){
                $result = $this->FileUploader->upload($imagePath);
                
                if($result['success']){
                    $filename = $result['filename'];                      
                    $extension = explode('.', $filename);
                    $extension = end($extension);
                    $result['extension'] = $extension;
                    $image = $photoId.$extension;
                }
            }else{
                $result = $this->UploadHandler->upload($action, $path, $ajax);
                $image = $result;            
            }*/
    }
    
    private function _processPlace($filepath, $info = array()){
        if(isset($info['u_id'])){
            $u_id = $info['u_id'];
        }
        $isAjax = $info['ajax'];        
        $photoId = $info['photoId'];        
        
        $photoPath = WWW_ROOT.SYSTEM_PATH_W.'places/';
        
        $result = $this->FileUploader->upload($photoPath);
        if($result['success']){
            $filename = $result['filename'];
            //$location = $result['location'];            
            $extension = explode('.', $filename);
            $extension = end($extension);
            $result['image'] = $photoId.'.'.$extension;
            rename($photoPath.$filename, $photoPath.$result['image']);
        }
        
    
        //output the response
        $result['photoId'] = $photoId;
        //result['image']
        //$result['filename'] = $this->PlacePhoto->id.'.'.$extension;
        return $result;
    }    
}
?>