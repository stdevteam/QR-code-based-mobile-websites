<?php
/**
 * Profiles controller
 * @property AuthComponent $Auth Authentication component
 * @property DashboardComponent $Dashboard Dashboard data collector
 */
class ProfilesController extends AppController {

    public $name = 'Profiles';
    public $components = array(
            'Image', 'FileHandler', 'OauthConsumer', 
            'Linkedin.Linkedin' => array(
                    'key' => 'z7cmrjjmfus5',
                    'secret' => 't3STwNVxdM6ERHHq',
            ),
            'Auth','Dashboard');

    //main public function of profile creation, will decide if the profile is being edited (and save changes to it),
    //or reviewed
    public function index(){
        $u_id = $this->Auth->getUser();
        $str_debug = '';
        $f_name = $this->Session->read('User.first_name');
        $l_name = $this->Session->read('User.last_name');
        $str_debug = $u_id;
        //set in view
        $full_name = $f_name . ' ' . $l_name;
        $this->set('title_for_layout', 'Dogvacay | ' . $full_name . ' Profile.');
        $this->set('str_debug', $str_debug);
        $this->set('full_name', $full_name);
        $this->layout = 'profile_edit';
        //if not exist insert profile
        $profile_id = $this->Profile->findByid_user($u_id);
        if (!$profile_id) {
            $this->data['Profile']['id_user'] = $u_id;
            $this->Profile->save($this->data);
            
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->data = $this->Profile->findByid_user($u_id);
            $this->redirect('/messages/inbox/');
        } else {
            //if exists redirect to profile view
            //or save edited profile
            $this->Profile->save($this->data);
            
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->redirect('/messages/inbox/');
        }
    }

//end profiles/index
    //view the profile for a certain user id
    public function view() {
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        //load session data.
        $u_id = $this->Auth->getUser();
        $str_debug = '';
        $f_name = $this->Session->read('User.first_name');
        $l_name = $this->Session->read('User.last_name');
        $full_name = $f_name . ' ' . $l_name;
        $this->set('title_for_layout', 'DogVacay.com | ' . $full_name . ' Profile.');
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $this->set('str_debug', $str_debug);
        $this->set('full_name', $full_name);
        //if main fields empty redirect to login or unauthorized page
        $new_messages_count = $this->Profile->query('SELECT COUNT(*) As count FROM threads '. 
            'WHERE (threads.from_id = '.$u_id.' OR threads.to_id = '.$u_id.') AND threads.unread = '.$u_id);
        //load the data
        $this->set('messages_count', $new_messages_count);
        $this->data = $this->Profile->findByid_user($u_id);
        $this->loadModel('Pet');
        $this->loadModel('Place');
        $pets = $this->Pet->findAllByid_user($u_id);
        $places = $this->Place->primaryPhotoByUserId($u_id);
        if($this->Session->check('rec.sent')){
            $this->Session->delete('rec.sent');
            $this->set('afterRequest',true);
        }
        $this->set('pets',$pets);
        $this->set('places',$places);
        //var_dump($places);die;
        $this->set('u_phone', $this->data['Profile']['phone']);
        $this->set('u_about_me', $this->data['Profile']['about_me']);
        $this->set('u_gender', $this->data['Profile']['gender']);
        $this->set('u_birthday', $this->data['Profile']['birthday']);
        $this->set('u_full_address', $this->data['Profile']['full_address']);
        $this->set('u_facebook', $this->data['Profile']['facebook']);
        $this->set('u_twitter', $this->data['Profile']['twitter']);
        $this->set('u_linkedin', $this->data['Profile']['linkedin']);
        $this->set('avatar_pic', $this->data['Profile']['photo_path']);
        $this->set('u_id', $u_id);
    }

    public function edit() {
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $includes = '<script type="text/javascript" src="/js/fancybox/jquery-1.4.3.min.js"></script>' . PHP_EOL .
                '<script type="text/javascript" src="/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>' . PHP_EOL .
                '<script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' . PHP_EOL .
                '<link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />' . PHP_EOL .
                '<script type="text/javascript">' . file_get_contents(WWW_ROOT.'/js/fancybox/pop_upload.js') . '</script>';
        $this->set('includes', $includes);

        $u_id = $this->Auth->getUser();
        $datetime = $this->data['User']['created'];
        $datetime = preg_split('/ /', $datetime, 2);
        $date = $datetime[0];
        $this->set('date', $datetime[0]);

        if (!empty($this->data)) {
            $this->data['User']['id'] = $u_id;
            $this->set('uid', $u_id);
            if (array_key_exists('pic', $this->data['Profile']) && $this->data['Profile']['pic']['error'] == UPLOAD_ERR_OK) {
                $pictype = explode("/", $this->data['Profile']['pic']['type']);
                $imagePath = WWW_ROOT.SYSTEM_PATH_W."profiles/";
                $thumbPath = WWW_ROOT.SYSTEM_PATH_W."profiles/thumbs/";
                if ($this->data['Profile']['pic']['size'] > 5242880) {
                    //echo '<SCRIPT>alert("File is too big!");</SCRIPT>';
                    $this->Session->write('Note.error', "File is too big!");
                } elseif ($pictype[0] !== 'image') {
                    //echo '<SCRIPT>alert("File is not picture file!");</SCRIPT>';
                    $this->Session->write('Note.error', "File is not picture file!");
                } else {
                    if ($this->data['Profile']["pic"]["error"] == '0') {
                        $tmp = $this->data['Profile']["pic"]["tmp_name"];
                        $extension = substr(strrchr($this->data['Profile']["pic"]['name'], '.'), 1);
                        $profmod = $this->Profile->findByid_user($u_id);
                        $filename = $profmod['Profile']['photo_path'];
                        if (file_exists($imagePath . $filename) && $filename !== 'default_avatar.png') {
                            unlink($imagePath . $filename);
                        }
                        $image = $u_id . '.' . $extension;
                        $path = $imagePath . $image;
                        move_uploaded_file($tmp, $path);
                        //checking for images size
                        $sizes = getimagesize($imagePath . $image);
                        if ($sizes[0] > 1000 || $sizes[1] > 1000) {
                            $this->Image->set_paths($imagePath, $imagePath);
                            $this->Image->width = 1200;
                            $this->Image->height = 1200;
                            $thumb = $this->Image->thumb($imagePath . $image);
                            rename($thumb, $imagePath . $image);
                        }
                        //generating thumbnail
                        $this->Image->set_paths($imagePath, $thumbPath);
                        $this->Image->width = 300;
                        $this->Image->height = 200;
                        $thumb = $this->Image->thumb($imagePath . $image);
                        rename($thumb, $thumbPath . $image);
                        $this->data['Profile']['photo_path'] = $image;
                    }
                }
            } else {
                unset($this->data['Profile']['pic']);
            }
            //!PROFILE PHOTO UPLOAD ALHORYTHM

            $this->Profile->saveAll($this->data);
            $this->loadModel('User');
            $this->User->save($this->data['User']);
            $this->Session->write('User.first_name', $this->data['User']['first_name']);
            $this->Session->write('User.last_name', $this->data['User']['last_name']);
            $this->data = $this->Profile->findByid_user($u_id);
            $path = $this->data['Profile']['photo_path'];
            if ($path == '') {
                $path = 'default_avatar.png';
            }
            $this->set('path', $path);
            if (!$this->Session->check('Note.error')) {
                
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                
                $this->Session->write('Note.ok', "Profile sucessfully updated");                
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Profile->findByid_user($u_id);
            $path = $this->data['Profile']['photo_path'];
            $this->set('path', $path);
        }
    }
    
    public function create(){
        $u_id = $this->Auth->checkUser();
        if($u_id == null){
            $this->Session->write('Redirect.url','/profiles/create/');
            $this->redirect('/users/add/');
        }
        $userData = $this->Dashboard->getData();
        if(!is_null($userData['Place'])){
            $this->redirect('/places/CreationOverview/');
        }
        $this->layout = 'User_Account_Page';
        $u_id = $this->Auth->getUser();
        
        $datetime = $userData['User']['created'];
        $datetime = preg_split('/ /', $datetime, 2);
        $date = $datetime[0];
        $this->set('date', $datetime[0]);

        if(!empty($this->data)){
            $this->data['User']['id'] = $u_id;
            $this->set('uid', $u_id);
            
            if(array_key_exists('pic', $this->data['Profile']) && $this->data['Profile']['pic']['error'] == UPLOAD_ERR_OK){
                //process user photo
                $pictype = explode("/", $this->data['Profile']['pic']['type']);
                $imagePath = WWW_ROOT.SYSTEM_PATH_W."profiles/";
                $thumbPath = WWW_ROOT.SYSTEM_PATH_W."profiles/thumbs/";
                
                if($this->data['Profile']['pic']['size'] > 5242880){
                    $this->Session->write('Note.error', "File is too big!");
                }elseif ($pictype[0] !== 'image'){
                    $this->Session->write('Note.error', "File is not picture file!");
                }else{
                    if($this->data['Profile']["pic"]["error"] == '0'){
                        $tmp = $this->data['Profile']["pic"]["tmp_name"];
                        $extension = substr(strrchr($this->data['Profile']["pic"]['name'], '.'), 1);
                        $profmod = $this->Profile->findByid_user($u_id);
                        $filename = $profmod['Profile']['photo_path'];
                        
                        if(file_exists($imagePath . $filename) && $filename !== 'default_avatar.png'){
                            unlink($imagePath . $filename);
                        }
                        $image = $u_id . '.' . $extension;
                        $path = $imagePath . $image;
                        move_uploaded_file($tmp, $path);
                        
                        //checking for images size
                        $sizes = getimagesize($imagePath . $image);
                        if($sizes[0] > 1000 || $sizes[1] > 1000){
                            $this->Image->set_paths($imagePath, $imagePath);
                            $this->Image->width = 1200;
                            $this->Image->height = 1200;
                            $thumb = $this->Image->thumb($imagePath . $image);
                            rename($thumb, $imagePath . $image);
                        }
                        //generating thumbnail
                        $this->Image->set_paths($imagePath, $thumbPath);
                        $this->Image->width = 300;
                        $this->Image->height = 200;
                        $thumb = $this->Image->thumb($imagePath . $image);
                        rename($thumb, $thumbPath . $image);
                        $this->data['Profile']['photo_path'] = $image;
                    }
                }
            }else{
                unset($this->data['Profile']['pic']);
            }
            //$this->Profile->saveAll($this->data);
            $result = $this->Profile->saveProfile($this->data);
            if(!$result){                
                $this->redirect('/profiles/create/');
            }
            $this->loadModel('User');
            $this->User->save($this->data['User']);
            $this->Session->write('User.first_name', $this->data['User']['first_name']);
            $this->Session->write('User.last_name', $this->data['User']['last_name']);
            $this->data = $this->Profile->findByid_user($u_id);
            $path = $this->data['Profile']['photo_path'];
            if ($path == '') {
                $path = 'default_avatar.png';
            }
            $this->set('path', $path);
            if(!$this->Session->check('Note.error')){
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->Session->write('Note.ok', "Profile sucessfully saved");
                //redirect to listPlace
                $this->redirect('/places/ListPlace/');
            }
        }
        if(empty($this->data)){
            $this->data = $this->Profile->findByid_user($u_id);
            $path = $this->data['Profile']['photo_path'];
            $this->set('path', $path);
        }
    }

    public function UploadPhoto() {        
        $this->layout = 'User_Account_Page';
        $u_id = $this->Auth->getUser();
        $data = $this->Profile->findByid_user($u_id, array('id', 'photo_path'));
        if (!empty($this->data)){
            if (array_key_exists('file', $this->data['Profile']) && $this->data['Profile']['file']['size']>0) {
                $pictype = explode("/", $this->data['Profile']['file']['type']);
                $imagePath = WWW_ROOT.SYSTEM_PATH_W."profiles/";
                $thumbPath = WWW_ROOT.SYSTEM_PATH_W."profiles/thumbs/";
                if ($this->data['Profile']['file']['size'] > 5242880) {
                    //echo '<SCRIPT>alert("File is too big!");</SCRIPT>';
                    $this->Session->write('Note.error', "File is too big!");
                }elseif ($pictype[0] !== 'image') {
                    //echo '<SCRIPT>alert("File is not picture file!");</SCRIPT>';
                    $this->Session->write('Note.error', "File is not picture file!");
                } else {
                    if ($this->data['Profile']['file']["error"] == '0') {
                        $tmp = $this->data['Profile']["file"]["tmp_name"];
                        $extension = substr(strrchr($this->data['Profile']["file"]['name'], '.'), 1);
                        //$profmod = $this->Profile->findByid_user($u_id);
                        $filename = $data['Profile']['photo_path'];
                        if (file_exists($imagePath . $filename) && $filename !== 'default_avatar.png') {
                            unlink($imagePath . $filename);             
                        }
                        $image = $u_id . '.' . $extension;
                        $path = $imagePath . $image;
                        move_uploaded_file($tmp, $path);
                        //checking for images size
                        $sizes = getimagesize($imagePath . $image);
                        if ($sizes[0] > 1000 || $sizes[1] > 1000) {
                            $this->Image->set_paths($imagePath, $imagePath);
                            $this->Image->width = 1200;
                            $this->Image->height = 1200;
                            $thumb = $this->Image->thumb($imagePath . $image);
                            rename($thumb, $imagePath . $image);
                        }
                        //generating thumbnail
                        $this->Image->set_paths($imagePath, $thumbPath);
                        $this->Image->width = 300;
                        $this->Image->height = 200;
                        $thumb = $this->Image->thumb($imagePath . $image);
                        rename($thumb, $thumbPath . $image);
                        $data['Profile']['photo_path'] = basename($u_id . '.' . $extension);
                        if (!$this->Session->check('Note.error')) {
                            $this->Profile->save($data);

                            //clean up session dashboard entery
                            $this->Dashboard->cleanUp();

                            $this->Session->write('Note.ok', "Profile sucessfully updated");                
                        }
                    }
                }  
            }
        }        
        $photopath = $data['Profile']['photo_path'];
        $this->set('path', $photopath);
    }

    public function verifications() {
        $u_id = $this->Auth->getUser();
        //$l_id = $this->Dashboard->getListingId();        
        
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $userData = $this->Dashboard->getData();
        //$this->checkVerifications($userData);
        $socials = array('fb' => array(is_null($userData['Profile']['facebook']) ? false : $userData['Profile']['facebook']),
            'tw' => array(is_null($userData['Profile']['twitter']) ? false : $userData['Profile']['twitter']),
            'li' => array(is_null($userData['Profile']['linkedin']) ? false : $userData['Profile']['linkedin']));
        //$this->set('userData',$userData);
        $this->data = $userData; 
        $this->set('socials', $socials);
        // place data
        $p_id = $this->Dashboard->getListingId();
        $cDetails = json_decode($userData['Place']['c_details']);
        $no_ver = $this->checkVerifications($userData, true);
        //var_dump(!$no_ver );var_dump(in_array('Calendar', $cDetails));
        $this->set('no_ver',(!$no_ver && in_array('Calendar', $cDetails))?'checked':'');
        $this->set('completeness', $userData['Place']['completeness']);
        $this->set('cDetails', $cDetails);
        $this->set('title', $userData['Place']['title']);
        $this->loadModel('PlacePhoto');
        $photo = $this->PlacePhoto->find('first', array('conditions' => array('PlacePhoto.id_place' => $p_id, 'PlacePhoto.primary' => 'ye')));
        if (!$photo) {
            $photo = $this->PlacePhoto->findByid_place($p_id);
        }
        if (!empty($photo)) {
            $this->set('path', $photo['PlacePhoto']['location']);
        } else {
            $this->set('path', false);
        }
        $this->set('p_id', $p_id);
    }
    
    public function saveVerifications(){
        $u_id = $this->Auth->getUser();
        
        if (!empty($this->data)) {             
            $current = $this->Profile->findByid_user($u_id);
            $toSave = array_merge($current['Profile'], $this->data['Profile']);
            $this->data['Profile'] = $toSave;
            $this->checkVerifications($this->data);
            $this->Profile->save($this->data);
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            //$this->addCompleteness();
            $this->redirect('/place_terms/Define');
        }else{
            $this->redirect('/profiles/verifications');
        }
    }

    public function twitter() {
        // Twitter connect
        if ($this->Session->check('twitter_request_token')) {
            $requestToken = $this->Session->read('twitter_request_token');
            $accessToken = $this->OauthConsumer->getAccessToken('Twitter', 'https://api.twitter.com/oauth/access_token', $requestToken);
            $twitter = $this->OauthConsumer->getfullResponse();
            $this->Session->delete('twitter_request_token');
            $twitter = explode('&', $twitter['body']);
            $userId = null;
            foreach ($twitter as $item) {
                if (strpos($item, 'user_id=') !== false) {
                    $userId = str_ireplace('user_id=', '', $item);
                }
            }
            if (!is_null($userId)) {
                $u_id = $this->Auth->getUser();
                $userData = $this->Profile->findByid_user($u_id);
                $userData['Profile']['twitter'] = $userId;
                $this->Profile->save($userData);
                
                //updating listing modified field
                $this->Dashboard->updateListing();
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->addCompleteness();
                $this->redirect('/profiles/verifications');
            }
            $this->redirect('/profiles/verifications');
        } else {
            $requestToken = $this->OauthConsumer->getRequestToken('Twitter', 'https://api.twitter.com/oauth/request_token', FULL_BASE_URL . '/profiles/twitter');
            $this->Session->write('twitter_request_token', $requestToken);
            $this->redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . $requestToken->key);
        }
        // end of Twitter
    }

    public function facebook() {
        // Facebook connect
        App::import('Vendor', 'facebook', array('file' => 'facebook/facebook.php'));
        $facebook = new Facebook(array(
                        'appId' => Configure::read("FB_APP_ID"),
                        'secret' => Configure::read("FB_APP_SECRET"),
                ));
        // Get User ID
        $user = $facebook->getUser();
        $fLoginUrl = $facebook->getLoginUrl(
                array(
                    'scope' => 'user_about_me,user_birthday,email',
                    'redirect_uri' => FULL_BASE_URL . '/profiles/facebook/',
                )
        );
        //$this->set('fb_login', $loginUrl);

        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        } else {
            $this->redirect($fLoginUrl);
        }
        if (isset($user_profile)) {
            $u_id = $this->Session->read('User.id');
            $userData = $this->Profile->findByid_user($u_id);
            $userData['Profile']['facebook'] = $user_profile['id'];
            $this->Profile->save($userData);
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->addCompleteness();
            $this->redirect('/profiles/verifications');
        }
        $this->redirect('/profiles/verifications');
        // end of Facebook
    }

    public function linkedin() {
        $this->Linkedin->connect(array('action' => 'linkedinRet'));
    }

    public function linkedinRet() {
        $this->Linkedin->authorize(array('action' => 'linkedinSave'));
    }

    public function linkedinSave() {
        $response = $this->Linkedin->call('people/~', array(
            'id',
            'picture-url',
            'first-name', 'last-name', 'summary', 'specialties', 'associations', 'honors', 'interests', 'twitter-accounts',
            'positions' => array('title', 'summary', 'start-date', 'end-date', 'is-current', 'company'),
            'educations',
            'certifications',
            'skills' => array('id', 'skill', 'proficiency', 'years'),
            'recommendations-received',
                ));
        if (isset($response['person']['id'])) {
            $u_id = $this->Auth->getUser();
            $userData = $this->Profile->findByid_user($u_id);
            $userData['Profile']['linkedin'] = $response['person']['id'];
            $this->Profile->save($userData);
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->addCompleteness();
            $this->redirect('/profiles/verifications');
        }
        $this->redirect('/profiles/verifications');
    }

    public function PublicProfile($id = null) {
        $this->layout = 'User_Master_Page';
        $this->data = $this->Profile->findByid_user($id);
        if ($this->data) {
            $username = $this->data['User']['first_name'] . ' ' . substr($this->data['User']['last_name'], 0, 1);
            $this->set('username', $username);
            $this->set('photo_path', $this->data['Profile']['photo_path']);
            $this->set('p_id', $id);
            $this->set('aboutMe',$this->data['Profile']['about_me']);
            //verification info
            $fb = 'false';
            $tw = 'false';
            $li = 'false';
            if ($this->data['Profile']['facebook']) {
                $fb = 'true';
            } else {
                $fb = 'false';
            }
            $this->set('fb', $fb);
            if ($this->data['Profile']['twitter']) {
                $tw = 'true';
            } else {
                $tw = 'false';
            }
            $this->set('fb', $tw);
            if ($this->data['Profile']['linkedin']) {
                $li = 'true';
            } else {
                $li = 'false';
            }
            $this->set('li', $li);
            //load all recomendations
            $this->loadModel('Recommendation');
            $recomendations = $this->Recommendation->AllForUser($id);
            if (!$recomendations) {
                $this->set('recomendations', false);
            } else {
                $this->set('recomendations', $recomendations);
            }

            $this->loadModel('Pet');
            $pets = $this->Pet->find('all', array('conditions' => array('id_user' => $id)));
            if (!empty($pets)) {
                $this->set('pets', $pets);
            } else {
                $this->set('pets', false);
            }
        } else {
            $this->redirect('/');
        }
    }
    
/*  one time function needed to resize large images to normal size , used for images uploaded before image resizing functionality
    public function correctImages() {
        $images = $this->Profile->query("SELECT * from profiles WHERE `photo_path` LIKE '%app/webroot/img/profiles%' LIMIT 1");
        $imagePath = WWW_ROOT .SYSTEM_PATH_W."profiles/";
        $thumbPath = WWW_ROOT .SYSTEM_PATH_W."profiles/thumbs/";
        if (count($images) > 0) {
            $old = $images[0]['profiles']['photo_path'];
            $new = str_ireplace('/app/webroot'.SYSTEM_PATH.'profiles/', '', $old);
            //$new = str_ireplace('/app/webroot/img/appimages/', '', $new);
            $images[0]['profiles']['photo_path'] = $new;
            $id = $images[0]['profiles']['id'];
            //checking size 
            $sizes = getimagesize($imagePath . $new);
            if ($sizes[0] > 1000 || $sizes[1] > 1000) {
                $this->Image->set_paths($imagePath, $imagePath);
                $this->Image->width = 1200;
                $this->Image->height = 1200;
                $thumb = $this->Image->thumb($imagePath . $new);
                rename($thumb, $imagePath . $new);
                echo "main image resized" . "<br />";
            } else {
                echo "main image is normal" . "<br />";
            }
            //generating thumbs
            $this->Image->set_paths($imagePath, $thumbPath);
            $this->Image->width = 300;
            $this->Image->height = 200;
            $thumb = $this->Image->thumb(CAKE_CORE_INCLUDE_PATH . $old);
            rename($thumb, $thumbPath . $new);
            $this->Profile->query("UPDATE profiles SET photo_path = '" . $new . "' WHERE id = " . $id);
            die("done" . $id);
        } else {
            die("Everything processed");
        }
    }
*/
    public function addCompleteness() {
        $p_id = $this->Dashboard->getListingId();
        $this->loadModel('Place');
        $place = $this->Place->findByid($p_id);
        $cDetails = json_decode($place['Place']['c_details']);

        if (!in_array('Calendar', $cDetails)) {
            $cDetails[] = 'Calendar';
            $place['Place']['completeness'] = count($cDetails) * 20;
            $cDetails = json_encode($cDetails);
            $place['Place']['c_details'] = $cDetails;
            $this->Place->save($place);

            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            //$this->redirect('/profiles/verifications/');
        }        
    }

    protected function checkVerifications($userData,$return = null) { 
        $data = $userData['Profile'];
        if($return){
            if (!is_null($data['facebook']) || !is_null($data['twitter']) || !is_null($data['linkedin'])
                || $data['psi'] != 0 || $data['nap'] != 0 || $data['pup'] != 0 
                || $data['other'] != 0 || $data['idb'] != 0 || $data['pfa'] != 0 ) {
                return true;
            }else{
                return false;
            }
        }
        if (!is_null($data['facebook']) || !is_null($data['twitter']) || !is_null($data['linkedin'])
                || $data['psi'] != 0 || $data['nap'] != 0 || $data['pup'] != 0 
                || $data['other'] != 0 || $data['idb'] != 0 || $data['pfa'] != 0 || $data['noVerifications']=='1') {

            $this->addCompleteness();
        }
    }

    public function verificationChanges() {
        $status = true;
        $message = '';
        if($_POST){
            $name = $_POST['name'];
            $checked = $_POST['checked'];
            $name = str_ireplace('data[Profile][', '', $name);
            $name = str_ireplace(']', '', $name);
            $name = trim($name);
            $checked = trim($checked);
            $checked = (($checked === "true")? 1 : 0);
            if(!$this->Session->check('User.id')){
                $status = false;
                $message = 'Error while processing verification change';
            }else{
                $u_id = $this->Auth->getUser();
                //$profile = $this->Profile->findByid_user($u_id);
                $profile = $this->Dashboard->getData();
                if(!isset($profile['Profile'][$name])){
                    $status = false;
                    $message = 'Verification invalid';
                }else{
                    $profile['Profile'][$name] = $checked;
                    $this->Profile->save($profile); 
                    $message = 'Verification changed successfully';
                    $this->checkVerifications($profile);
                }
            }
        }else{
            $status = false;
            $message = 'Error while processing verification change';
        }
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        //return the result
        $return = array('status' => $status, 'message' => $message);
        echo json_encode($return);
        exit;
    }
    
    public function myHome(){
        $this->loadModel('Place');
        $u_id = $this->Auth->getUser();
        $p_id = $this->Session->read('Place.id');
        if(!empty($p_id)){
            $this->redirect('/places/myHome');
        }else{
            $this->redirect('/places/ListPlace');
        }
    }
    
    public function services(){
        //var_dump($this->data);
        $u_id = $this->Auth->getUser();
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $this->layout = 'User_Account_Page';
        $current = $userDb['Profile'];
        $toSave = null;
        if(!empty($this->data)){
            $toSave = array_merge($current, $this->data['Profile']);
            $this->data['Profile'] = $toSave;
            $this->Profile->save($this->data['Profile']);
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->data['Profile'] = $toSave;
            $this->redirect('/profiles/services');
        }
        $this->data['Profile'] = $current;
        
    }
    
}
?>
