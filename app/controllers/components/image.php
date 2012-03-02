<?php  
/* 
 * component to create thumbnails by phpThumb 
 *  
 * @author Sebastian Bechtel <kontakt@sebastian-bechtel.info> 
 * @varsion 1.0 
 * @package default 
 */  
class ImageComponent extends Object { 
    /* 
     * @var array 
     * @access private 
     *  
     * array with allowed mime types 
     */ 
    private $allowed_mime_types = array( 
        'image/jpeg', 
        'image/pjpeg', 
        'image/png', 
        'image/gif' 
    ); 
     
    /* 
     * @var array 
     * @access private 
     *  
     * array with allowed file extensions 
     */ 
    private $allowed_extensions = array( 
        'jpg', 
        'jpeg', 
        'png', 
        'gif' 
    ); 
     
    /* 
     * @var string 
     * @access private 
     *  
     * save paths for thumbnail and upload image 
     */ 
    private $save_paths = array( 
        'upload' => '', 
        'thumb' => '' 
    ); 
     
    /* 
     * @var string 
     * @access private 
     *  
     * path to file 
     */ 
    private $file_path = null; 
     
    /* 
     * @var int 
     * @access public 
     *  
     * thumbnail width 
     */ 
    public $width = 100; 
     
    /* 
     * @var int 
     * @access public 
     *  
     * thumbnail height 
     */ 
    public $height = 100; 
     
    /* 
     * @var mixed 
     * @access private 
     *  
     * zoom crop 
     */ 
    private $zoom_crop = 0; 
     
    /* 
     * @var pointer 
     * @access private 
     *  
     * object pointer for controller 
     */ 
    private $controller = null; 
     
    /* 
     * @var array 
     * @access public 
     *  
     * array with error messages 
     */ 
    private $errorMsg = array(); 
     
    /* 
     * @access public 
     * @param object pointer &$controller 
     *  
     * init component with controller pointer 
     */ 
    public function startup(&$controller) { 
        $this->controller = &$controller; 
    } 
     
    /* 
     * @access public 
     * @param string $upload_path 
     * @param string $thumb_path 
     *  
     * set paths for upload and thumb 
     */ 
    public function set_paths($upload_path, $thumb_path) {         
        if(!empty($upload_path) AND is_writable($upload_path) 
            AND !empty($thumb_path) AND is_writable($thumb_path)) 
                $this->save_paths = array( 
                    'upload' => $upload_path, 
                    'thumb' => $thumb_path  
                ); 
        else return false; 
    } 
     
    /* 
     * @access public 
     * @param mixed $zoom_crop 
     * @return boulean success 
     *  
     * set zoom crop for ThumbPHP 
     */ 
    public function set_zoom_crop($zoom_crop) { 
        if(empty($zoom_crop) OR $zoom_crop === '') return false; 
         
        /* 
         * allowed zoom crop parameter 
         * from actual readme.txt 
         */ 
        static $allowed_zoom_crop_param = array( 
            'T', 
            'B', 
            'L', 
            'R', 
            'TL', 
            'TR', 
            'BL', 
            'BR' 
        ); 
         
        if($zoom_crop === 1 OR $zoom_crop === 'C') $this->zoom_crop = 1; 
        elseif(extension_loaded('magickwand') 
            AND in_array($zoom_crop, $allowed_zoom_crop_param)) $this->zoom_crop = $zoom_crop; 
        else return false; 
         
        return true; 
    } 
     
    /* 
     * @access public 
     * @param string $field 
     * @return mixed destintion or false 
     *  
     * upload image from $this->controller->data array and return success 
     * writes upload path into file_path of component 
     */ 
    public function upload_image($field) { 
        if(empty($field) OR $field === '') return false; 
         
        // get Model and field 
        $exploded = explode('.', $field); 
        if(count($exploded) !== 2) return false; 
         
        list($model, $value) = $exploded; 
         
        // Image data had been send? 
        if(array_key_exists($model, $this->controller->data) 
            AND array_key_exists($value, $this->controller->data[$model]) 
            AND is_array($this->controller->data[$model][$value])) { 
                // get pointer for lighter code 
                $file = &$this->controller->data[$model][$value]; 
                 
                // does php get any upload errors? 
                if(array_key_exists('error', $file) AND $file['error'] === 0) { 
                    /* 
                     * is the size OK? 
                     * (bigger then 0 and smaller then 'upload_max_filesize' in php.ini 
                     */ 
                    if($file['size'] === 0 
                        OR (string)(ceil((int)$file['size']/1000000) . 'M') > ini_get('upload_max_filesize'))  
                            return  false; 
                    // mimetype ok? 
                    elseif(!in_array($file['type'], $this->allowed_mime_types))  
                        return false; 
                    else { 
                        // get extension 
                        $exploded = explode('.', $file['name']); 
                        $extension = end($exploded); 
                         
                        // extension allowed? 
                        if(in_array($extension, $this->allowed_extensions)) { 
                            // generate extension 
                            $destination = $this->save_paths['upload'] .  
                                md5(microtime()) . '.' . $extension; 
                             
                            // move file from temp to upload directory 
                            move_uploaded_file($file['tmp_name'], $destination); 
                             
                            // all OK? 
                            if(file_exists($destination)) { 
                                // write destination to internal file_path variable and return success 
                                $this->file_path = $destination; 
                                return $destination; 
                            } 
                        } 
                        return false; 
                    } 
                } else return false; 
            } 
        return false; 
    } 
     
    /* 
     * @access public 
     * @return mixed thumb destination or false 
     *  
     * wrapper function for $this->thumb() 
     * uses $this->file_name from upload function as parameter 
     */ 
    public function thumb_uploaded_file() { 
        // run thumb generation method with internal filepath variable 
        return $this->thumb($this->file_path); 
    } 
     
    /* 
     * @access public 
     * @param string $file 
     * @return mixed thumb destination or false 
     *  
     * generates an thumbnail from source 
     * write the result to a file 
     */ 
    public function thumb($file) { 
        if(empty($file) 
        OR !file_exists($file)) return false; 
         
        /* 
         * load phpThumb from vendors directory 
         * and get a new instance 
         */ 
        App::import('Vendor', 'phpThumb', array( 
            'file' => 'phpThumb' . DS . 'phpthumb.class.php' 
        )); 
        $phpThumb = new phpThumb(); 
         
        // configure phpThumb for it's thumbnail generation 
        $phpThumb->setSourceFilename($file); 
        $phpThumb->setParameter('w', $this->width); 
        $phpThumb->setParameter('h', $this->height); 
        $phpThumb->setParameter('zc', $this->zoom_crop); 
         
        /* 
         * generate thumbnail 
         * and render to file 
         */ 
        $pathinfo = pathinfo($file); 
        $destination = $this->save_paths['thumb'] .  
            md5($pathinfo['filename'] . $this->width . $this->height . $this->zoom_crop) . 
            '.' . $pathinfo['extension']; 
         
        /* 
         * if their is an older version of the thumbnail 
         * (same source, width, height, zoom-crop), 
         * then delete 
         */ 
        if(file_exists($destination)) 
            unlink($destination); 
             
        if($phpThumb->generateThumbnail() 
            AND $phpThumb->RenderToFile($destination)) 
                return $destination; 
        // something goes wrong 
        return false; 
    } 
} 
?>