<?php
/**
 * Picture Processing Component
 *
 * This component is used to upload pictures from listing process
 *
 * @package       dogvacay
 * @subpackage    dogvacay.Pictures
 * @property 
 */
class ImageProcessComponent extends Object{
    /**
     * The dependency components needed
     * @var array An array of component names
     */
    public $components = array('Session','Auth', 'Image', 'UploadHandler', 'FileUploader' => array(
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
    
    /**
     *performing image process
     * @param array $params 
     *      :action
     *      :path 
     *      :ajax - true if call is from ajax
     *      :thumb - true to generate thumb for pic 
     *      :upload - true to upload picture 
     *      :image -(when upload=false is nessesary)
     *      :move - true to move file
     *      :photoId - id to generate image name
     * @return array  
     */      
    public function processImage($params) {
        $u_id = $this->Auth->getUser();
         
        $action = $params['action'];
        $path = (isset($params['path']))?$params['path']:'';
        $ajax = (isset($params['ajax']))?$params['ajax']:false;
        $thumb = (isset($params['thumb']))?$params['thumb']:false;
        $upload = (isset($params['upload']))?$params['upload']:false;
        $image = (isset($params['image']))?$params['image']:false;
        $move = (isset($params['move']))?$params['move']:false;
        $photoId = (isset($params['photoId']))?$params['photoId']:null;
        
        $actions = array('profiles', 'dogs', 'places', 'slider');              
        $active = '';
        foreach($actions as $key => $item){
            if($item == $action){
                $active = $key;
            }
        }
        if($active === ''){ 
            return false;
        }
        $imagePath = WWW_ROOT.SYSTEM_PATH_W.$actions[$active]."/";
        $thumbPath = WWW_ROOT.SYSTEM_PATH_W.$actions[$active]."/thumbs/"; 
        if($move){
            move_uploaded_file($path['tmp_name'], $imagePath.$path['name']);
            $path = $path['name'];
        }
       
        if($upload){
            $result = $this->UploadHandler->upload($action, $path, $ajax, $photoId);
            if(isset($result['errors'])){
                return $result;
            }
            $image = $result['image'];                 
        }
        $sizes = getimagesize($imagePath . $image);
        if ($sizes[0] > 1200 || $sizes[1] > 1200) {
            $this->_resize(array(1200,1200), $imagePath, $imagePath,$image);
        }
        if($thumb){
            $this->_resize(array(300,200), $imagePath, $thumbPath, $image);
        }
        if($result){
            return $result;
        }else{
            return TRUE;
        }             
    }
    
    /**
     * Resizing given picture with given sizes
     * 
     * @param string imagePath 
     * @param array size[0] width, size[1] height 
     * @param string image
     * @access private
     */
    private function _resize($size = array(), $imagePath='', $thumbPath='' ,$image=''){
        $this->Image->set_paths($imagePath, $thumbPath);
        $this->Image->width = $size[0];
        $this->Image->height = $size[1];
        $thumb = $this->Image->thumb($imagePath . $image);
        rename($thumb, $thumbPath . $image);        
        
    }
    
    
}
?>