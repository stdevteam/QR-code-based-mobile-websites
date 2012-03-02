<?php	
class PlacePhotosController extends AppController{
    public $name = 'PlacePhotos';
    public $components = array('Image','Auth','Dashboard','FileUploader');
    /**
     * An array of valid image extensions
     * @var array
     */
    protected $_allowedExtensions = array('jpg','jpeg','gif','png','JPG','JPEG','GIF','PNG');
    /**
     * A path to places photos folder relative to webroot
     * @var string
     */
    protected $_placePhotosFolder = 'places/';

    public function index(){
        $this->redirect('/placePhotos/upload/');
    }
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->_placePhotosFolder = SYSTEM_PATH_W.$this->_placePhotosFolder;
    }
    
    public function upload(){
     
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $includes = '<link href="/files/uploadify/uploadify.css" type="text/css" rel="stylesheet" />'.PHP_EOL.	
        '<link href="/css/movingboxes.css" media="screen" rel="stylesheet">'.PHP_EOL.	
        '<!--[if lt IE 9]>'.PHP_EOL.	
        '<link href="/css/movingboxes-ie.css" rel="stylesheet" media="screen" />'.PHP_EOL.	
        '<![endif]-->'.PHP_EOL.	
        '<script type="text/javascript" src="/files/uploadify/jquery-1.4.2.min.js"></script>'.PHP_EOL.						
        '<script type="text/javascript" src="/files/uploadify/swfobject.js"></script>'.PHP_EOL.						
        '<script type="text/javascript" src="/files/uploadify/jquery.uploadify.v2.1.4.js"></script>'.PHP_EOL.	
        '<script src="/js/jquery.movingboxes.min.js"></script>'.PHP_EOL.				
        '<script type="text/javascript" src="/js/places_multiupload.js"></script>';
        $this->set('includes', $includes);
        $u_id = $this->Auth->getUser();			
        $p_id = $this->Dashboard->getListingId();
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $this->set('cDetails',  json_decode($userDb['Place']['c_details']));
        $this->data = $this->PlacePhoto->find('all',  array('conditions' => array('id_place' => $p_id)));
        $detail = $this->PlacePhoto->query(
                'SELECT places.id, places.title FROM places WHERE places.id = '.$p_id
        );
        $this->set('v_property_id', $p_id);
        $this->set('v_title', $detail[0]['places']['title']);
        $this->loadModel('Place');
        $place = $this->Place->findByid($p_id);
        $this->set('completeness', $place['Place']['completeness']);
        $this->set('title', $place['Place']['title']);
        $this->loadModel('PlacePhoto');
        $photo = $this->PlacePhoto->find('first', array('conditions' => array('PlacePhoto.id_place'=>$p_id, 'PlacePhoto.primary' => 'ye')));
        if(!$photo){
                $photo = $this->PlacePhoto->findByid_place($p_id);
        }
        if(!empty($photo)){
                $this->set('path', $photo['PlacePhoto']['location']);
        }else{
                $this->set('path', false);
        }
        $this->set('p_id', $p_id);
        $photo_list = '';
        if(!empty($this->data)){				
                $deck = count($this->data);				
                $photo_list = '<h1 class="profile_heading seablue">Photos Uploaded:</h1><br/>';	
                $photo_list .= ' <div id="wrapper"><div id="slider-two">';
                for($i=0; $i<$deck; $i++){					
                        $tmp = $this->data[$i]['PlacePhoto'];					
                        $path = SYSTEM_PATH.'thumbs/'.$tmp['location'];					
                        $id   = $tmp['id'];	
                        $photo_list .= '<div>';
                        $photo_list .= '<img src=\''.$path.'\' width="300" height="250" /><br/>';
                        $photo_list .= '
                            <div style="width:140px; text-align:left; font-size:11px; margin-bottom: 10px;" class="pink">
                                <img style="margin-bottom:-2px;" alt="" src="/images/change-image.png" />
                                <a href="/place_photos/setPrimary/'.$id.'" class="pinklink">Set as Primary</a>
                                <br />
                                <img style="margin-bottom:-2px;" alt="" src="/images/delete-icon.png" />
                                <a href="/place_photos/DeletePhoto/'.$id.'" class="pinklink">Delete</a>
                            </div>';
                        $photo_list .= '</div>';
                }	
                $photo_list .= '</div></div>';
                $this->set('photo_list', $photo_list);			
        }					
    }
    
    public function SaveUpload(){
        Configure::write('debug', 2);
        
        if(!empty($_FILES)){
            $tempFile = $_FILES['Filedata']['tmp_name'];
            //$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/'; // old way
            $targetPath = WWW_ROOT.$this->_placePhotosFolder;
            $targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
            //$fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
            //$fileTypes  = str_replace(';','|',$fileTypes);
            //$typesArray = split('\|',$fileTypes);
            $typesArray = $this->_allowedExtensions;
            $fileParts  = pathinfo($_FILES['Filedata']['name']);
            if(in_array($fileParts['extension'],$typesArray)){
                // Uncomment the following line if you want to make the directory if it doesn't exist
                // mkdir(str_replace('//','/',$targetPath), 0755, true);

                if(move_uploaded_file($tempFile,$targetFile)){
                    echo str_replace(WWW_ROOT,FULL_BASE_URL . '/',$targetFile);
                }else{
                    echo 'Unable to upload please try again later';
                }
            }else{
                echo 'Invalid file type.';
            }
        }
    }

    public function ProcessUpload($filename = null){
        $this->layout = 'User_Master_Page';
        $u_id = '';			
        $p_id = '';
        $u_id = $this->Auth->getUser();
        $p_id = $this->Dashboard->getListingId();
        
       // $u_ = $this->Auth->getUser();
        /*
        if($this->Session->read('User.id') == null)	{				
                $this->redirect('/users/login');			
        }else {				
                $u_id = $this->Session->read('User.id');				
                $p_id = $this->Session->read('Place.id');			
        }						
        */
        $photoPath = WWW_ROOT.$this->_placePhotosFolder;
        
        if($filename){
            $files = split('~', $filename);
            $allowed = $this->_allowedExtensions;
            for($i=0; $i<count($files)-1; $i++){
                $this->PlacePhoto->create();					
                $extension = substr(strrchr($files[$i], '.'), 1);
                if(file_exists(WWW_ROOT.SYSTEM_PATH_W.'places/'.$files[$i]) || 1 == 1){

                    if(in_array(strtolower($extension), $allowed)){
                      
                        $this->data['PlacePhoto']['id_place'] = $p_id;					
                        $this->data['PlacePhoto']['id_user'] = $u_id;					
                        $this->data['PlacePhoto']['photo_alt'] = $files[$i];
                        $this->PlacePhoto->save($this->data);
                        $pf_id = $this->PlacePhotos->id;
                        $this->FileUploader->upload($pf_id);
                        $this->data['PlacePhoto']['location'] = $this->PlacePhoto->id.'.'.$extension;                                            
                        $this->PlacePhoto->save($this->data);
                       
                        //updating listing modified field
                        $this->Dashboard->updateListing();
                        //clean up session dashboard entery
                        $this->Dashboard->cleanUp();
                        rename($photoPath.$files[$i], $photoPath.$this->PlacePhoto->id.'.'.$extension);

                        //checking for images size
                        $sizes = getimagesize($photoPath.$this->PlacePhoto->id.'.'.$extension);
                        if($sizes[0] > 1000 || $sizes[1] > 1000){
                            $this->Image->set_paths($photoPath, $photoPath);
                            $this->Image->width = 1200;
                            $this->Image->height = 1200;
                            $thumb = $this->Image->thumb($photoPath.$this->PlacePhoto->id.'.'.$extension);
                            rename($thumb, $photoPath.$this->PlacePhoto->id.'.'.$extension);

                        }

                        //generating thumbnail
                        $this->Image->set_paths($photoPath, $photoPath);
                        $this->Image->width = 300;
                        $this->Image->height = 200;
                        $thumb = $this->Image->thumb($photoPath.$this->PlacePhoto->id.'.'.$extension); 
                        rename($thumb, $photoPath.'thumbs/'.$this->PlacePhoto->id.'.'.$extension);

                    }else{

                        $this->Session->write('Note.error', "All files need to be images, some of them not saved");
                        $path = $photoPath.$files[$i];
                        unset($path);
                    }   
                }else{
                    $this->Session->write('Note.error', "We have an error while processing one of your images");
                    $path = $photoPath.$files[$i];
                    unlink($path);
                }
            }
            if(!$this->Session->check('Note.error')){
                $this->Session->write('Note.ok',"All images uploaded successfully");
            }
        }
        //check if user is creating a place
        $p_id = $this->Dashboard->getListingId();
        //update overall progress bar
        $this->loadModel('Place');
        $place = $this->Place->findByid($p_id);
        $cDetails = $place['Place']['c_details'];
        $cDetails = json_decode($cDetails);
        if(!in_array('uploadPhoto', $cDetails)){
                $cDetails[] = 'uploadPhoto';
                $place['Place']['completeness'] = count($cDetails)*20;
                $cDetails = json_encode($cDetails);
                $place['Place']['c_details'] = $cDetails;
                $this->Place->save($place);
        }
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/place_photos/Upload');		
    }

    public function setPrimary($id = null){
        $p_id = $this->Session->read('Place.id');
        //remove previous primary photo
        $this->PlacePhoto->query('UPDATE place_photos SET place_photos.primary = \'no\' WHERE place_photos.id_place = '.$p_id.';');
        //set new primary photo
        $this->PlacePhoto->query('UPDATE place_photos SET place_photos.primary = \'yes\' WHERE place_photos.id = '.$id.';');
        
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/place_photos/Upload');
    }

    public function DeletePhoto($id = null){
        $this->layout = 'User_Master_Page';
        $u_id = '';
        $p_id = '';
        if($this->Session->read('User.id') == null)	{
                $this->redirect('/users/login');
        }else{
                $u_id = $this->Session->read('User.id');
                $p_id = $this->Session->read('Place.id');
        }
        $this->PlacePhoto->delete($id);
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/place_photos/Upload');
    }
/* one time function , needed to create thumbnails for old images , when thumbs functionality added first time.
   public function generateThumbs(){
        $images = $this->PlacePhoto->query("SELECT * from place_photos WHERE `location` LIKE '%app/webroot/files%' LIMIT 1");
        if(count($images) > 0){
            $old = $images[0]['place_photos']['location'];
            $new = str_ireplace('/app/webroot'.SYSTEM_PATH.'places/', '', $old);
            $images[0]['place_photos']['location'] = $new;
            $id = $images[0]['place_photos']['id'];
            $this->Image->set_paths(WWW_ROOT.SYSTEM_PATH_W.'places/', WWW_ROOT.SYSTEM_PATH_W.'places/thumbs/'); 
            $this->Image->width = 300;
            $this->Image->height = 200;
            $thumb = $this->Image->thumb(CAKE_CORE_INCLUDE_PATH.$old); 
            rename($thumb, WWW_ROOT.SYSTEM_PATH_W.'places/thumbs/'.$new);
            $this->PlacePhoto->query("UPDATE place_photos SET location = '".$new."' WHERE id = ".$id);
            die("done");
        }else{
            die("Everything processed");
        }
    }
*/
    
/*  one time function needed to resize large images to normal size , used for images uploaded before image resizing functionality  
    public function correctSizes($id){
        $images = $this->PlacePhoto->query("SELECT * from place_photos WHERE id = '".$id."' LIMIT 1");
        if(count($images) == 0){
            die("invalid id");
        }
        $old = $images[0]['place_photos']['location'];
        $id = $images[0]['place_photos']['id'];
        $sizes = getimagesize(WWW_ROOT.SYSTEM_PATH_W.'places/'.$old);
        if($sizes[0] > 1000 || $sizes[1] > 1000){
            $this->Image->set_paths(WWW_ROOT.SYSTEM_PATH_W.'places/', WWW_ROOT.SYSTEM_PATH_W.'places/');
            $this->Image->width = 1200;
            $this->Image->height = 1200;
            $thumb = $this->Image->thumb(WWW_ROOT.SYSTEM_PATH_W.'places/'.$old); 
            rename($thumb, WWW_ROOT.SYSTEM_PATH_W.'places/'.$old);
            die("done");
        }else{
            die("normal image");
        }
    }
  */
}
?>
