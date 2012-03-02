<?php
/**
 * Pets controller
 * @property DashboardComponent $Dashboard Dashboard component
 */
class PetsController extends AppController{
    public $name = 'Pets';
    public $components = array('Image','Auth','Dashboard');

    public function index(){
        $this->redirect('/pets/PetsInfo');
    }
    
    public function PetsInfo(){
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';

        $u_id = $this->Auth->getUser();
        //for the listing process in header
        $p_id = $this->Dashboard->getListingId();
        /*$this->loadModel('Place');
        $place = $this->Place->findByid($p_id);*/
        $place = $this->Dashboard->getData();
        $cDetails = json_decode($place['Place']['c_details']);
        if(is_array($place['Pet']) && count($place['Pet']) > 0){
            if(!in_array('Pets', $cDetails)){
                                $cDetails[] = 'Pets';
                                $place['Place']['completeness'] = count($cDetails)*20;
                                $cDetails = json_encode($cDetails);
                                $place['Place']['c_details'] = $cDetails;
                                $this->loadModel('Place');
                                $this->Place->save($place['Place']);
                                $this->Dashboard->cleanUp();
                        }
        }
        $this->set('completeness', $place['Place']['completeness']);
        $this->set('cDetails', json_decode($place['Place']['c_details']));
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
        $profile_pic = $this->Pet->query('SELECT profiles.photo_path FROM profiles WHERE profiles.id_user = '.$u_id);
        $path = $profile_pic[0]['profiles']['photo_path'];
        $this->set('pic', $path);
        $this->set('owner_name', $this->Session->read('User.first_name').' '.$this->Session->read('User.last_name'));
        $this->set('u_id', $u_id);

        $all_dogs = $this->Pet->findAllByid_user($u_id);
        $this->set('all_dogs', $all_dogs);
        $this->loadModel('Profile');
        $this->data = $this->Profile->findByid_user($u_id);
        $this->set('u_facebook', $this->data['Profile']['facebook']);
        $this->set('u_twitter', $this->data['Profile']['twitter']);
        $this->set('u_linkedin', $this->data['Profile']['linkedin']);
    }

    public function AddDog(){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $u_id = $this->Auth->getUser();
        $this->set('u_id', $u_id);
        //check if user already has a dog in the system
        $pet_check = $this->Pet->findByid_user($u_id);
        //check if user is creating a place
        $p_id = $this->Dashboard->checkListing();
        /*$this->loadModel('Place');
        $place = $this->Place->findByid($p_id);*/
        //$place = $this->Dashboard->getData();
        if(!is_null($userDb['Place'])){
            $this->set('completeness', $userDb['Place']['completeness']);
            $this->set('title', $userDb['Place']['title']);
            $this->set('p_id', $p_id);
            /*$this->loadModel('PlacePhoto');
            $photo = $this->PlacePhoto->find('first', array('conditions' => array('PlacePhoto.id_place'=>$p_id, 'PlacePhoto.primary' => 'ye')));
            if(!$photo){
                $photo = $this->PlacePhoto->findByid_place($p_id);
            }*/
            /*$photo = $userDb['PlacePhoto'];
            if(!empty($photo)){
                $this->set('path', $photo['location']);
            }else{
                $this->set('path', 'home.jpg');
            }*/
        }
        if(!empty($this->data)){
            // array of fileds whcih need to be decimal
           $requireds = array(
               'pet_name'
               );
           
           try{
               foreach($this->data['Pet'] as $key => $pet){                    
               if(in_array($key,$requireds) && ($pet==='' || $pet === '0')){
                   throw new Exception('Please fill in all the required fields');
               }
           }
               //update overall progress bar
                    if(!is_null($p_id)){
                        $cDetails = json_decode($userDb['Place']['c_details']);
                        if(!in_array('Pets', $cDetails)){
                                $cDetails[] = 'Pets';
                                $userDb['Place']['completeness'] = count($cDetails)*20;
                                $cDetails = json_encode($cDetails);
                                $userDb['Place']['c_details'] = $cDetails;
                                $this->Place->save($userDb['Place']);
                        }
                    }
                    $imagePath = WWW_ROOT.SYSTEM_PATH_W."dogs/";
                    $thumbPath = WWW_ROOT.SYSTEM_PATH_W."dogs/thumbs/";
                    $allowed = array('jpg','jpeg','gif','png');
                    if(isset($this->data['Pet']['image']) && $this->data['Pet']['image']['error'] == UPLOAD_ERR_OK){
                        $name = explode('.', $this->data['Pet']['image']['name']);
                        $extension = end($name);
                        if(in_array(strtolower($extension),$allowed)){
                            if(!(array_key_exists('id',$this->data['Pet']) && $this->data['Pet']['id'] != '' )){
                                    $tempVar = $this->data['Pet']['image'];
                                    $this->data['Pet']['image'] = '';
                                    $this->Pet->save($this->data);
                                    $this->data['Pet']['id'] = $this->Pet->id;
                                    $this->data['Pet']['image'] = $tempVar;                                    
                            }
                            $image = $this->data['Pet']['id'].".".$extension;
                            if(file_exists($imagePath.$image) && $image!=='labrador_img.gif'){
                                unlink($imagePath.$image);
                            }
                            move_uploaded_file($this->data['Pet']['image']['tmp_name'], $imagePath.$image);
                            $this->data['Pet']['image'] = $image;
                            //checking for images size
                                $sizes = getimagesize($imagePath.$image);
                                if($sizes[0] > 1000 || $sizes[1] > 1000){
                                    $this->Image->set_paths($imagePath, $imagePath);
                                    $this->Image->width = 1200;
                                    $this->Image->height = 1200;
                                    $thumb = $this->Image->thumb($imagePath.$image); 
                                    rename($thumb, $imagePath.$image);
                                }
                                //generating thumbnail
                                $this->Image->set_paths($imagePath, $thumbPath); 
                                $this->Image->width = 300;
                                $this->Image->height = 200;
                                $thumb = $this->Image->thumb($imagePath.$image);
                                rename($thumb, $thumbPath.$image);
                        }else{
                            $this->Session->write('Note.error',"Invalid image");
                        }
                }else{
                    unset($this->data['Pet']['image']);
                }
                $this->Session->delete('DogId');
                if( !$this->Session->check('Note.error') ){
                    $this->data['Pet']['id_user'] = $u_id;
                    $this->Pet->save($this->data); 
                    $petId = $this->Pet->id;
                    
                    //updating listing modified field
                    $this->Dashboard->updateListing();
                    //clean up session dashboard entery
                    $this->Dashboard->cleanUp();
                    $this->Session->write('Note.ok',"Pet info successfully saved");
                }
           
           }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
            }
               $pet_new = $this->Pet->findByid($petId);
               $this->set('petImage',$pet_new['Pet']['image']); 
        }else{   
            $dogId = $this->Session->read('DogId');
            $this->data = $this->Pet->findByid($dogId);
            $this->set('petImage',$this->data['Pet']['image']); 
        }       
    }

    public function AboutMyDogs(){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $u_id = $this->Auth->getUser();
        $profile_pic = $this->Pet->query('SELECT profiles.photo_path FROM profiles WHERE profiles.id_user = '.$u_id);
        $path = $profile_pic[0]['profiles']['photo_path'];
        $this->set('pic', $path);
        $this->set('owner_name', $this->Session->read('User.first_name').' '.$this->Session->read('User.last_name'));
        $this->set('u_id', $u_id);
        $all_dogs = $this->Pet->findAllByid_user($u_id);
        $this->set('all_dogs', $all_dogs);
        $this->loadModel('Profile');
        $this->data = $this->Profile->findByid_user($u_id);
        $this->set('u_facebook', $this->data['Profile']['facebook']);
        $this->set('u_twitter', $this->data['Profile']['twitter']);
        $this->set('u_linkedin', $this->data['Profile']['linkedin']);
        $this->loadModel('Booking');
        $bookings = $this->Booking->findBookingDataForUser($u_id);
        $this->set('bookings',$bookings);
        $this->loadModel('PlacePhoto');
        // this need to be discussed, for now catching first listing to show as "My home"
        $place = $this->PlacePhoto->find('first',array('id_user' => $u_id));
        $this->set('place',$place);
    }

    //delete dog, link in About My Dogs
    public function delete($id = null){
        $u_id = $this->Auth->getUser();
        $this->Pet->delete($id);
        $this->Dashboard->cleanUp();
        $this->redirect('/pets/AboutMyDogs');
    }

    public function AddNewDog(){
        $this->Session->delete('Place.id');
        $this->Session->delete('DogId');
        $this->redirect('/pets/AddDog');
    }

    public function AddNewDog2(){
        $this->Session->delete('DogId');
        $this->redirect('/pets/AddDog');
    }

    //edit dog, link in About My Dogs
    public function edit($id = null){
        $this->Session->write('DogId', $id);
        $this->redirect('/pets/AddDog');
    }

    public function previous(){
        $this->layout = 'User_Master_Page';
    }

    public function upcoming(){
        $this->layout = 'User_Master_Page';
    }
    
    // if user already have added dogs, completeness is added and he is redirected to next step
    public function OLDsaveDogs(){
        
        $u_id = $this->Auth->getUser();
        if(isset($this->data['Pet']['noDogs']) && $this->data['Pet']['noDogs'] == '1'){
            $noDogs = true;
        }else{
            $noDogs = false;
        }
        $dogs = $this->Pet->findAllByid_user($u_id);
        if($dogs || $noDogs){
            if($this->Session->check('Place.id')){
                    $p_id = $this->Session->read('Place.id');
                    $this->loadModel('Place');
                    $place = $this->Place->findByid($p_id);
                    $cDetails = json_decode($place['Place']['c_details']);
                    if(!in_array('Pets', $cDetails)){
                            $cDetails[] = 'Pets';
                            $place['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $place['Place']['c_details'] = $cDetails;
                            $this->Place->save($place);

                            //clean up session dashboard entery
                            $this->Dashboard->cleanUp();
                    }
                    $this->redirect('/profiles/verifications/');    
                }else{
                    $this->redirect('/places/ListPlace/');
                }
        }else{
            $this->set('error', "You don't have saved dog profile. Add one now or go to next step");
            $this->redirect('/pets/PetsInfo');
        }

    }
    
    public function saveDogs(){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);    
        //check if user is creating a place
        $p_id = '';
        $p_id = $this->Dashboard->getListingId();        
        $u_id = $this->Auth->getUser();
        if(isset($this->data['Pet']['noDogs']) && $this->data['Pet']['noDogs'] == '1'){
            $noDogs = true;
        }else{
            $noDogs = false;
        }
        //var_dump($noDogs);die;
        if(!empty($this->data) && $noDogs == false ){           
           // array of fileds whcih need to be decimal
            $requireds = array(
                'pet_name'
                );
            $req_count=count($requireds); 
            try{
                foreach($this->data['Pet'] as $key => $pet){                    
                    if(in_array($key,$requireds) && ($pet==='' || $pet === '0')){                   
                        $req_count=$req_count-1;
                    }              
                }           
                if($req_count !== 0 && $req_count!==count($requireds)){
                    throw new Exception('Please fill in all the required fields');
                }
                if($req_count===count($requireds)){
                    //update overall progress bar             
                    //$cDetails = json_decode($userDb['Place']['c_details']);
                    /*if(!in_array('Pets', $cDetails)){
                        $cDetails[] = 'Pets';
                        $userDb['Place']['completeness'] = count($cDetails)*20;
                        $cDetails = json_encode($cDetails);
                        $userDb['Place']['c_details'] = $cDetails;
                        $this->Place->save($userDb['Place']);
                        
                        //clean up session dashboard entery
                        $this->Dashboard->cleanUp();
                    }*/
                
                    $imagePath = WWW_ROOT.SYSTEM_PATH_W."img/dogs/";
                    $thumbPath = WWW_ROOT.SYSTEM_PATH_W."dogs/thumbs/";
                    $allowed = array('jpg','jpeg','gif','png');
                    if(isset($this->data['Pet']['image']) && $this->data['Pet']['image']['error'] == UPLOAD_ERR_OK){
                        $name = explode('.', $this->data['Pet']['image']['name']);
                        $extension = end($name);
                        if(in_array(strtolower($extension),$allowed)){
                            if(!(array_key_exists('id',$this->data['Pet']) && $this->data['Pet']['id'] != '' )){
                                $tempVar = $this->data['Pet']['image'];
                                $this->data['Pet']['image'] = '';
                                $this->Pet->save($this->data);
                                $this->data['Pet']['id'] = $this->Pet->id;
                                $this->data['Pet']['image'] = $tempVar;
                            }
                            $image = $this->data['Pet']['id'].".".$extension;
                            if(file_exists($imagePath.$image) && $image!=='labrador_img.gif'){
                                unlink($imagePath.$image);
                            }
                            move_uploaded_file($this->data['Pet']['image']['tmp_name'], $imagePath.$image);
                            $this->data['Pet']['image'] = $image;
                            //checking for images size
                            $sizes = getimagesize($imagePath.$image);
                            if($sizes[0] > 1000 || $sizes[1] > 1000){
                                $this->Image->set_paths($imagePath, $imagePath);
                                $this->Image->width = 1200;
                                $this->Image->height = 1200;
                                $thumb = $this->Image->thumb($imagePath.$image); 
                                rename($thumb, $imagePath.$image);
                            }
                            //generating thumbnail
                            $this->Image->set_paths($imagePath, $thumbPath); 
                            $this->Image->width = 300;
                            $this->Image->height = 200;
                            $thumb = $this->Image->thumb($imagePath.$image);
                            rename($thumb, $thumbPath.$image);
                        }else{
                            $this->Session->write('Note.error',"Invalid image");
                        }
                    }else{
                        unset($this->data['Pet']['image']);
                    }
                    $this->Session->delete('DogId');
                    if( !$this->Session->check('Note.error') ){
                        $this->data['Pet']['id_user'] = $u_id;
                        $this->Pet->save($this->data);
                        
                        //updating listing modified field
                        $this->Dashboard->updateListing();                        
                        //clean up session dashboard entery
                        $this->Dashboard->cleanUp();
                        $this->Session->write('Note.ok',"Pet info successfully saved");
                    }                    
                }
                $dogs = $this->Pet->findAllByid_user($u_id);
                    if($dogs){                                     
                        $this->loadModel('Place');
                        //$place = $this->Place->findByid($p_id);
                        $cDetails = json_decode($userDb['Place']['c_details']);
                        if(!in_array('Pets', $cDetails)){
                            $cDetails[] = 'Pets';
                            $userDb['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $userDb['Place']['c_details'] = $cDetails;
                            $this->Place->save($userDb['Place']);

                            //clean up session dashboard entery
                            $this->Dashboard->cleanUp();
                        }
                        $this->redirect('/profiles/verifications/');    
                        
                    }else{
                        $this->Session->write('Note.error', "You don't have saved dog profile. Add one now or go to next step");
                        $this->redirect('/pets/PetsInfo');
                    }
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
                $this->redirect('/pets/PetsInfo');
            }               
        }else{ 
            if($noDogs == true){
                 $this->loadModel('Place');
                        //$place = $this->Place->findByid($p_id);
                        $cDetails = json_decode($userDb['Place']['c_details']);
                        if(!in_array('Pets', $cDetails)){
                            $cDetails[] = 'Pets';
                            $userDb['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $userDb['Place']['c_details'] = $cDetails;
                            $this->Place->save($userDb['Place']);

                            //clean up session dashboard entery
                            $this->Dashboard->cleanUp();
                        }
                        $this->redirect('/profiles/verifications/');  
            }
            if(is_array($userDb['Pet']) && count($userDb['Pet']) > 0){
                $this->redirect('/profiles/verifications/');
            }else{
                $this->Session->write('Note.error',"Please fill in details or check I don't have any dogs box");
                $this->redirect('/pets/PetsInfo');
            }
        }  
    }
    
    public function correctImages($dogId){
        $images = $this->Pet->query("SELECT * from pets WHERE id ='".$dogId."' AND picture != '' LIMIT 1");
        $imagePath = WWW_ROOT.SYSTEM_PATH_W."dogs/";
        $thumbPath = WWW_ROOT.SYSTEM_PATH_W."dogs/thumbs/";
        if(count($images) > 0){
            $old = $images[0]['pets']['picture'];
            $new = str_ireplace('/appp/webroot'.SYSTEM_PATH.'dogs/', '', $old);
            $id = $images[0]['pets']['id'];
            //$images[0]['pets']['image'] = $new;
            //$images[0]['pets']['picture'] = '';
            //rename($imagePath.$old,$imagePath.$new);
            //checking size 
            $sizes = getimagesize($imagePath.$new);
            if($sizes[0] > 1000 || $sizes[1] > 1000){
                $this->Image->set_paths($imagePath, $imagePath);
                $this->Image->width = 1200;
                $this->Image->height = 1200;
                $thumb = $this->Image->thumb($imagePath.$new); 
                rename($thumb, $imagePath.$new);
                echo "main image resized"."<br />";
            }else{
                echo "main image is normal"."<br />";
            }
            //generating thumbs
            $this->Image->set_paths($imagePath, $thumbPath); 
            $this->Image->width = 300;
            $this->Image->height = 200;
            $thumb = $this->Image->thumb($imagePath.$new); 
            rename($thumb, $thumbPath.$new);
            $this->Pet->query("UPDATE pets SET image = '".$new."' , picture = '' WHERE id = ".$id);
            die("done".$id);
        }else{
            die("Everything processed");
        }
    }
    
    public function OLDAddDog(){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $u_id = $this->Auth->getUser();
        $this->set('u_id', $u_id);
        //check if user already has a dog in the system
        $pet_check = $this->Pet->findByid_user($u_id);
        //check if user is creating a place
        $p_id = '';
        $p_id = $this->Dashboard->checkListing();
        /*$this->loadModel('Place');
        $place = $this->Place->findByid($p_id);*/
        $place = $this->Dashboard->getData();
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
                $this->set('path', 'home.jpg');
        }
        $this->set('p_id', $p_id);
       if(!empty($this->data)){
           // array of fileds whcih need to be decimal
           $requireds = array(
               'pet_name','breed','gender','age','pet_size','Spay_Neuter',
               'Vaccinations','dog_friendly','other_pets'
               );
           
           try{
               foreach($this->data['Pet'] as $key => $pet){                    
               if(in_array($key,$requireds) && ($pet==='' || $pet === '0')){                   
                   throw new Exception('Please fill in all the required fields');
               }
           }
               //update overall progress bar
                if($p_id){
                    $cDetails = json_decode($place['Place']['c_details']);
                    if(!in_array('Pets', $cDetails)){
                            $cDetails[] = 'Pets';
                            $place['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $place['Place']['c_details'] = $cDetails;
                            $this->Place->save($place);

                            //clean up session dashboard entery
                            $this->Dashboard->cleanUp();
                        }
                }
                    $imagePath = WWW_ROOT.SYSTEM_PATH_W."dogs/";
                    $thumbPath = WWW_ROOT.SYSTEM_PATH_W."dogs/thumbs/";
                    $allowed = array('jpg','jpeg','gif','png');
                    if(isset($this->data['Pet']['image']) && $this->data['Pet']['image']['error'] == UPLOAD_ERR_OK){
                        $name = explode('.', $this->data['Pet']['image']['name']);
                        $extension = end($name);
                        if(in_array(strtolower($extension),$allowed)){
                            if(!(array_key_exists('id',$this->data['Pet']) && $this->data['Pet']['id'] != '' )){
                                    $tempVar = $this->data['Pet']['image'];
                                    $this->data['Pet']['image'] = '';
                                    $this->Pet->save($this->data);
                                    $this->data['Pet']['id'] = $this->Pet->id;
                                    $this->data['Pet']['image'] = $tempVar;
                            }
                            $image = $this->data['Pet']['id'].".".$extension;
                            if(file_exists($imagePath.$image) && $image!=='labrador_img.gif'){
                                unlink($imagePath.$image);
                            }
                            move_uploaded_file($this->data['Pet']['image']['tmp_name'], $imagePath.$image);
                            $this->data['Pet']['image'] = $image;
                            //checking for images size
                                $sizes = getimagesize($imagePath.$image);
                                if($sizes[0] > 1000 || $sizes[1] > 1000){
                                    $this->Image->set_paths($imagePath, $imagePath);
                                    $this->Image->width = 1200;
                                    $this->Image->height = 1200;
                                    $thumb = $this->Image->thumb($imagePath.$image); 
                                    rename($thumb, $imagePath.$image);
                                }
                                //generating thumbnail
                                $this->Image->set_paths($imagePath, $thumbPath); 
                                $this->Image->width = 300;
                                $this->Image->height = 200;
                                $thumb = $this->Image->thumb($imagePath.$image);
                                rename($thumb, $thumbPath.$image);
                        }else{
                            $this->Session->write('Note.error',"Invalid image");
                        }
                }else{
                    unset($this->data['Pet']['image']);
                }
                $this->Session->delete('DogId');
                if( !$this->Session->check('Note.error') ){
                    $this->data['Pet']['id_user'] = $u_id;
                    $this->Pet->save($this->data);

                    //clean up session dashboard entery
                    $this->Dashboard->cleanUp();
                    $this->Session->write('Note.ok',"Pet info successfully saved");
                    if($this->Session->check('Place.id')){
                            $this->redirect('/pets/edit/'.$this->data['Pet']['id']);
                    }else{
                            $this->redirect('/pets/AboutMyDogs');
                    }
                }
           
           }catch(Exception $error){
                $message = $error->getMessage();
                echo '<SCRIPT>alert(\''.$message.'!\');</SCRIPT>';
            }
                
        }else{   
            $dogId = $this->Session->read('DogId');
            $this->data = $this->Pet->findByid($dogId);
        }       
    }
}
?>