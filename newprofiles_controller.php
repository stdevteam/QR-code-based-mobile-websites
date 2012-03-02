<?php
/**
 * Profiles controller
 * @property AuthComponent $Auth Authentication component
 * @property DashboardComponent $Dashboard Dashboard data collector
 * @property MailerComponent $Mailer General email sender component
 * @property SessionComponent $Session The Session handler
 * @property FileUploaderComponent $FileUploader Ajax file uploader hanlder
 * @property ImageProcessComponent $ImageProcess processing image upload resizing moveing 
 */
class NewprofilesController extends AppController {

    public $name = 'Newprofiles';
    public $components = array(
            'Image', 'Check', 'FileHandler', 'OauthConsumer', 
            'Linkedin.Linkedin' => array(
                    'key' => 'z7cmrjjmfus5',
                    'secret' => 't3STwNVxdM6ERHHq',
            ),
            'Auth','Dashboard','Socials','Mailer', 'UploadHandler', 'ImageProcess', 'FileUploader' => array(
                'ext' => array('jpg', 'jpeg', 'gif', 'png')
            ));

    //main public function of profile creation, will decide if the profile is being edited (and save changes to it),
    //or reviewed
    public function index(){        
        $u_id = $this->Auth->checkUser();
        if ($u_id == null) {
            $this->Session->write('Redirect.url','/newprofiles/');
            
            $this->redirect('/users/add/');
        }
        $u_id = $this->Auth->getUser();
        $f_name = $this->Session->read('User.first_name');
        $l_name = $this->Session->read('User.last_name');
        //set in view
        $full_name = $f_name . ' ' . $l_name;
        $this->set('title_for_layout', 'Dogvacay | ' . $full_name . ' Profile.');
        $this->set('full_name', $full_name);
        $this->layout = 'new_listing_steps';
        //if not exist insert profile
        $this->loadModel('Profile');
        $this->loadModel('User');
  
        $profile = $this->Profile->findByid_user($u_id);
        $user = $this->User->findByid($u_id);
        $image = ($profile['Profile']['photo_path'])?$profile['Profile']['photo_path']:'default_avatar.png';
        $this->set('image', $image);
        $this->set('profile', $profile['Profile']);
        if($profile){
            $this->data['newprofile'] = array_merge($profile['Profile'], $user['User'] );            
        }            
        //$this->redirect('/messages/inbox/');
    }    
    
    public function save(){
        if(!empty($this->data)){
                 // var_dump($this->data);die;
            $u_id = $this->Auth->getUser();
            $f_name = $this->Session->read('User.first_name');
            $l_name = $this->Session->read('User.last_name');
            //var_dump($this->data);die;
            $this->loadModel('Profile');
            $this->loadModel('User');
            $this->loadModel('Place');
            $u_ph = $this->data['newprofile']['phone'];
            //var_dump($this->data['newprofile']['phone']);die;
            //var_dump($this->Check->checkPhone($u_ph));die;
            if($this->Check->checkPhone($u_ph) == false){
                $this->Session->write('Note.error','Please enter a valid phone number');
                $this->redirect('/newprofiles/');
            }
            $y_exp = $this->data['newprofile']['exp_years'];
           
            if(!is_numeric($y_exp) || $y_exp>100){
                $this->Session->write('Note.error','Please enter a number of years of experience');
                $this->redirect('/newprofiles/');
            }
            $profile = $this->Profile->findByid_user($u_id);
            $user = $this->User->findByid($u_id);
            
            //saving picture
            if(
                isset($this->data['newprofile']['file']) &&
                trim($this->data['newprofile']['file'])
            ){

                $params = array(
                    'action' => 'profiles',
                    'path'   => $this->data['newprofile']['file'],
                    'thumb'  => true,
                    'upload' => true,
                    );
                $pic = $this->ImageProcess->processImage($params);

                $this->data['newprofile']['file'] = $pic;
                $this->data['newprofile']['photo_path'] = $pic['image'];
            } else {
                unset($this->data['newprofile']['file']);
            }
            //END saving picture
            
            if($this->Session->check('Note.error')){
                $this->redirect('/newprofiles');
            }else{
                $this->Session->write('Note.ok','Your Profile info successfully saved');
                //creating the place
                $place = $this->Place->findByid_user($u_id);
                if(!$place){
                    $this->Place->create();
                    $place = array('Place' => array(
                        'id_user' => $u_id,
                    ));
                    $this->Place->save($place);
                }else{               
                    $this->Dashboard->updateListing();
                }
                 if($this->data['User']){
                     $to_save = array_merge($user['User'], $this->data['User']);
                     $this->User->save($to_save);            
                 }
                 if($this->data['newprofile']){
                     $to_save = array_merge($profile['Profile'], $this->data['newprofile']);
                     $this->Profile->save($to_save);            
                 }
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->redirect('/newprofiles/dogs');
            }
        }else{
            $this->redirect('/newprofiles');
        }
    }

    public function twitter() {
        //loading the models
        $this->loadModel('Profile');
        
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
                
                $this->Session->write('Note.ok', 'You are connected to Twitter!');
                //updating listing modified field
                $this->Dashboard->updateListing();
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->_processCompleteness('Calendar');
                $this->redirect('/newprofiles/');
            }
            $this->Session->write('Note.error', ' An error occurred, please check your Twitter settings.');
            $this->redirect('/newprofiles/');
        } else {
            $requestToken = $this->OauthConsumer->getRequestToken('Twitter', 'https://api.twitter.com/oauth/request_token', FULL_BASE_URL . '/newprofiles/twitter/');
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
                    'redirect_uri' => FULL_BASE_URL . '/newprofiles/facebook/',
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
            $this->loadModel('Profile');
            $userData = $this->Profile->findByid_user($u_id);
            $userData['Profile']['facebook'] = $user_profile['id'];
            $this->Profile->save($userData);
            $this->Session->write('Note.ok', 'You are connected to Facebook!');
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->_processCompleteness('Calendar');
            $this->redirect('/newprofiles/');
        }
        $this->Session->write('Note.error', ' An error occurred, please check your Facebook settings.');
        $this->redirect('/newprofiles/');
        // end of Facebook
    }

    public function linkedin() {
        $this->Linkedin->connect(array('action' => 'linkedinRet'));
    }

    public function linkedinRet() {
        $this->Linkedin->authorize(array('action' => 'linkedinSave'));
    }

    public function linkedinSave() {
        //loading the models
        $this->loadModel('Profile');
        
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
            
            $this->Session->write('Note.ok', 'You are connected to LinkedIn!');
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->_processCompleteness('Calendar');
            $this->redirect('/newprofiles/');
        }
        $this->Session->write('Note.error', 'An error occurred, please check your LinkedIn settings.');
        $this->redirect('/newprofiles/');
    }
    
    public function services(){
        $u_id = $this->Auth->getUser();
        $userData = $this->Dashboard->getData();
        
        $this->loadmodel('Service');
        
        $this->set('userData',$userData);
        $this->layout = 'new_listing_steps';
        
        $current = $userData['Service'];
        $toSave = null;
        if(!empty($this->data)){
           //var_dump($this->data);die;
            if(isset($current)){
                $toSave = array_merge($current, $this->data['Service']);
            }else{                
                $toSave = $this->data['Service'];
            }
            $toSave['user_id'] = $u_id;
            $this->Service->save($toSave);

            if (!empty($this->data['Service']['walking_rate']) && !is_numeric($this->data['Service']['walking_rate'])) {
                $this->Session->write('Note.error', 'Please enter a valid rate!');
            }
            if (!empty($this->data['Service']['walking_radius']) && !is_numeric($this->data['Service']['walking_radius'])) {
                $this->Session->write('Note.error', 'Please enter a valid service radius!');
            }
            if (!empty($this->data['Service']['walking_bulk']) && !is_numeric($this->data['Service']['walking_bulk'])) {
                $this->Session->write('Note.error', 'Please enter a valid bulk rate!');
            }

            if (!empty($this->data['Service']['training_rate']) && !is_numeric($this->data['Service']['training_rate'])) {
                $this->Session->write('Note.error', 'Please enter a valid rate!');
            }
            if (!empty($this->data['Service']['training_radius']) && !is_numeric($this->data['Service']['training_radius'])) {
                $this->Session->write('Note.error', 'Please enter a valid service radius!');
            }
            if (!empty($this->data['Service']['training_bulk']) && !is_numeric($this->data['Service']['training_bulk'])) {
                $this->Session->write('Note.error', 'Please enter a valid bulk rate!');
            }

            if (!empty($this->data['Service']['sitting_rate']) && !is_numeric($this->data['Service']['sitting_rate'])) {
                $this->Session->write('Note.error', 'Please enter a valid rate!');
            }
            if (!empty($this->data['Service']['sitting_radius']) && !is_numeric($this->data['Service']['sitting_radius'])) {
                $this->Session->write('Note.error', 'Please enter a valid service radius!');
            }
            if (!empty($this->data['Service']['sitting_bulk']) && !is_numeric($this->data['Service']['sitting_bulk'])) {
                $this->Session->write('Note.error', 'Please enter a valid bulk rate!');
            }

            if (!empty($this->data['Service']['care_rate']) && !is_numeric($this->data['Service']['care_rate'])) {
                $this->Session->write('Note.error', 'Please enter a valid rate!');
            }
            if (!empty($this->data['Service']['care_radius']) && !is_numeric($this->data['Service']['care_radius'])) {
                $this->Session->write('Note.error', 'Please enter a valid service radius!');
            }
            if (!empty($this->data['Service']['care_bulk']) && !is_numeric($this->data['Service']['care_bulk'])) {
                $this->Session->write('Note.error', 'Please enter a valid bulk rate!');
            }

            if (!empty($this->data['Service']['other_rate']) && !is_numeric($this->data['Service']['other_rate'])) {
                $this->Session->write('Note.error', 'Please enter a valid rate!');
            }
            if (!empty($this->data['Service']['other_radius']) && !is_numeric($this->data['Service']['other_radius'])) {
                $this->Session->write('Note.error', 'Please enter a valid service radius!');
            }
            if (!empty($this->data['Service']['other_bulk']) && !is_numeric($this->data['Service']['other_bulk'])) {
                $this->Session->write('Note.error', 'Please enter a valid bulk rate!');
            }

            if ($this->Session->check('Note.error')) {
                $this->redirect('/newprofiles/services/');
            }
            else {
                $this->Session->write('Note.ok','Your services info was successfully saved');
            }
            
            //updating listing modified field
            $this->Dashboard->updateListing();
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            $this->data['Service'] = $toSave;
            $this->redirect('/newprofiles/finePrint');
        }
        
        if(isset($current) && $current){
            $this->data['Service'] = $current;

            if(
                ($this->data['Service']['walking_rate'] == '' || is_null($this->data['Service']['walking_rate'])) && 
                ($this->data['Service']['walking_radius'] == '' || is_null($this->data['Service']['walking_radius'])) &&
                ($this->data['Service']['walking_bulk'] == '' || is_null($this->data['Service']['walking_bulk']))
            ){
                $this->data['Service']['walking'] = 0;
            }else{
                $this->data['Service']['walking'] = 1;
            }

            if(
                ($this->data['Service']['training_rate'] == '' || is_null($this->data['Service']['training_rate'])) && 
                ($this->data['Service']['training_radius'] == '' || is_null($this->data['Service']['training_radius'])) && 
                ($this->data['Service']['training_bulk'] == '' || is_null($this->data['Service']['training_bulk'])) 
            ){
                $this->data['Service']['training'] = 0;
            }else{
                $this->data['Service']['training'] = 1;
            }

            if(
                ($this->data['Service']['sitting_rate'] == '' || is_null($this->data['Service']['sitting_rate'])) &&
                ($this->data['Service']['sitting_radius'] == '' || is_null($this->data['Service']['sitting_radius'])) &&
                ($this->data['Service']['sitting_bulk'] == '' || is_null($this->data['Service']['sitting_bulk'])) 
            ){
                $this->data['Service']['sitting'] = '0';
            }else{
                $this->data['Service']['sitting'] = '1';
            }

            if(
                ($this->data['Service']['care_rate'] == '' || is_null($this->data['Service']['care_rate'])) &&
                ($this->data['Service']['care_radius'] == '' || is_null($this->data['Service']['care_radius'])) &&
                ($this->data['Service']['care_bulk'] == '' || is_null($this->data['Service']['care_bulk']))
            ){
                $this->data['Service']['care'] = '0';
            }else{
                $this->data['Service']['care'] = '1';
            }
            
            

            if(
                ($this->data['Service']['other_label'] == '' || is_null($this->data['Service']['other_label'])) &&
                ($this->data['Service']['other_rate'] == '' || is_null($this->data['Service']['other_rate'])) &&
                ($this->data['Service']['other_radius'] == '' || is_null($this->data['Service']['other_radius'])) && 
                ($this->data['Service']['other_bulk'] == '' || is_null($this->data['Service']['other_bulk'])) 
            ){
                $this->data['Service']['other'] = '0';
            }else{
                $this->data['Service']['other'] = '1';
            }
        }
    }
    
    public function place(){
        $this->layout = 'new_listing_steps';
        //load the models
        $this->loadModel('PlacePhoto');
        $this->loadModel('Place');
        $this->loadModel('PlaceTerm');
        $this->loadModel('User');
        
        $userId = $this->Auth->getUser();
        $userDb = $this->Dashboard->getData();
        
        //load progress data
        $p_id = $this->Dashboard->getListingId();
        $this->set('p_id', $p_id);
        
        //place terms  
        $place = $userDb['Place'];
        
        $cDetails = json_decode($place['c_details']);
        $this->set('completeness', $place['completeness']);
        $this->set('cDetails', $cDetails);
        $this->set('title', $place['title']);
        
        //check for completeness
        /*if($place['completeness']=='100' && !in_array('Requested',$cDetails) ){
            $cDetails[] = 'Requested';            
            $cDetails = json_encode($cDetails);
            $place['c_details'] = $cDetails;
            $this->Place->save(array('Place' => $place));

            $user = $this->User->findByid($userId);
            $email = $user['User']['email'];
            //send approval notification
            $this->Mailer->adminApproveListing($p_id,$email);
            //send approval notification
            $this->Dashboard->cleanUp();
            $this->redirect('/places/afterlisting');
        }*/
        
        //get all photos for the place
        $photo = $this->PlacePhoto->find('all', array('conditions' => array(
                'PlacePhoto.id_place'   => $p_id, 
        )));
        //set place photos to view
        $this->set('photos', $photo);
        
        
        //get place terms
        $placeTerms = $this->PlaceTerm->findByid_place($p_id);
        $terms = ((is_array($placeTerms) && is_array($placeTerms['PlaceTerm']))? $placeTerms['PlaceTerm'] : array());
        if(count($terms)){
            $terms["introductory_rate"] = ((!empty($terms["introductory_rate"]))? explode(',', $terms["introductory_rate"]) : '');
        }
        
        //if post submitted...
        if(!empty($this->data)){
            try {
                if(
                    $this->data['Place']['title'] == '' || $this->data['Place']['description'] == '' ||
                    $this->data['Place']['property_type'] == '' || $this->data['Place']['yard'] == ''
                ){
                    throw new Exception('Please fill in all required fields');
                    //$this->Session->write('Note.error', "Please fill in all required fields");
                }//list place
                elseif(!isset($this->data['Place']['full_address']) || $this->data['Place']['full_address'] == ''){
                    //$this->Session->write('Note.error', "Please input your full address to continue");
                    throw new Exception('Please input your full address to continue');
                }elseif(
                        !isset($this->data['Place']['zip']) || 
                        (!is_string($this->data['Place']['zip']) && $this->data['Place']['zip'] <= 0)
                ){
                    //$this->Session->write('Note.error', "Please input correct address to continue");
                    throw new Exception('Please input correct address to continue');
                }elseif(strlen($this->data['Place']['title'])>35){
                    throw new Exception('Title Must be maximum 35 characters');
                }else{
                    $this->data['Place']['id_user'] = $userId;
                    $this->data['Place']['id'] = $p_id;
                    //$this->data['Place']['completeness'] = '20';
                    $cDetails = json_encode(array('placeDetails'));
                    $this->data['Place']['c_details'] = $cDetails;
                    
                    //add fields from post
                    $this->data['Place']['title'] = $this->data['Place']['title'];
                    $this->data['Place']['description'] = $this->data['Place']['description'];
                    $this->data['Place']['full_address'] = $this->data['Place']['full_address'];
                    $this->data['Place']['property_type'] = $this->data['Place']['property_type'];
                    $this->data['Place']['yard'] = $this->data['Place']['yard'];
                    //set the updated date
                    $this->data['Place']['modified'] = gmdate("Y-m-d H:i:s");
                    
                    //set address data from list place
                    if(isset($this->data['Place']['ll']) && $this->data['Place']['ll']){
                        $ll = $this->data['Place']['ll'];
                        //var_dump($this->data);die;
                        unset($this->data['Place']['ll']);
                        $ll = str_ireplace('(', '', $ll);
                        $ll = str_ireplace(')', '', $ll);
                        $ll = explode(',', $ll);

                        $this->data['Place']['lat'] = $ll[0];
                        $this->data['Place']['lng'] = $ll[1];
                    }else{
                        unset($this->data['Place']['lat']);
                        unset($this->data['Place']['lng']);
                    }
                    
                    //send approval notification
                    $user = $this->User->findByid($userId);
                    $this->Mailer->adminListingStarted($p_id, $user['User']['email']);
                    //$this->Mailer->startListingForUser($email,$this->Place->id);
                    
                    //save the place
                    $this->Place->save($this->data);
                }

                //place terms
                $this->data['PlaceTerm'] = array_merge($terms, $this->data['PlaceTerm']);
                $this->data['PlaceTerm']['id_place'] = $p_id;
                $this->data['PlaceTerm']['id_user'] = $userId;
                
                //process fields from post
                $this->data['PlaceTerm']["nigthly_rates"] = $this->_processFloatField($this->data['PlaceTerm']["nigthly_rates"]);
                $this->data['PlaceTerm']["introductory_rate"] = (is_array($this->data['PlaceTerm']["introductory_rate"]))
                        ? implode(',', $this->data['PlaceTerm']["introductory_rate"])
                        : '';
                $this->data['PlaceTerm']["cancellation_policy"] = trim($this->data['PlaceTerm']["cancellation_policy"]);
                $this->data['PlaceTerm']["large_dogs"] = $this->_processFloatField($this->data['PlaceTerm']["large_dogs"]);
                $this->data['PlaceTerm']["pick_up"] = $this->_processFloatField($this->data['PlaceTerm']["pick_up"]);
                $this->data['PlaceTerm']["bath"] = $this->_processFloatField($this->data['PlaceTerm']["bath"]);
                $this->data['PlaceTerm']["cleaning_fees"] = $this->_processFloatField($this->data['PlaceTerm']["cleaning_fees"]);
                $this->data['PlaceTerm']["weekly_rates"] = $this->_processFloatField($this->data['PlaceTerm']["weekly_rates"]);
                
                if(!$this->data['PlaceTerm']["nigthly_rates"] || !$this->data['PlaceTerm']["cancellation_policy"]){
                    //$this->Session->write('Note.error', "Please fill in all required fields");
                    throw new Exception('Please fill in all required fields');
                }elseif($this->data['PlaceTerm']["nigthly_rates"] <= 15){
                    throw new Exception('Please enter a valid rate amount greater than $15');
                }else{
                    $this->PlaceTerm->save($this->data);
                }
                
                //clean up session dashboard entry
                $this->Dashboard->cleanUp();
                $this->data = $this->Place->find('first', array('id' => $this->Place->id, 'id_user' => $userId));
                //$this->redirect('/newprofiles/place/');
                $this->Session->write('Note.ok', "Your home info was successfully saved");         
                $this->redirect('/newprofiles/services/');
                
                //update overall progress bar
                /*$place = $this->Place->findByid($p_id);
                $cDetails = json_decode($place['Place']['c_details']);
                if(!in_array('Prices', $cDetails)){
                    $cDetails[] = 'Prices';
                    $place['Place']['completeness'] = count($cDetails)*20;
                    $cDetails = json_encode($cDetails);
                    $place['Place']['c_details'] = $cDetails;
                    $this->Place->save($place);
                    $this->Dashboard->cleanUp();
                    if($place['Place']['completeness'] == '100'){
                        $this->redirect('/places/afterlisting');
                    }else{
                        $this->redirect('/place_terms/Define/');
                    }
                }elseif(!in_array('Requested', $cDetails)){
                    if($place['Place']['completeness'] == '100'){
                        $this->redirect('/places/afterlisting');
                    }
                }else{
                    if($place['Place']['completeness'] == '100'){
                        $this->redirect('/place_terms/Define/');
                    }
                }*/ 
            
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error', "$message");            
            }
        }
        
        //set data to pass to view
        $this->data['PlaceAndTerms'] = array_merge($place, $terms);
    
    }

    public function placePhoto(){
        $u_id = $this->Auth->getUser();
        $p_id = $this->Dashboard->getListingId();
        
        //load the models
        $this->loadModel('PlacePhoto');
        
        $photoCount = $this->PlacePhoto->find('count', array('conditions' => array(
            'PlacePhoto.id_place'   => $p_id,
        )));
        if($photoCount <= 0){
            $this->data['PlacePhoto']['primary'] = 'ye';
        }else{
            $this->data['PlacePhoto']['primary'] = 'no';
        }
        $this->PlacePhoto->create();

        $this->data['PlacePhoto']['id_place'] = $p_id;					
        $this->data['PlacePhoto']['id_user'] = $u_id;
        $this->PlacePhoto->save($this->data);
        
        $params = array(
                    'action'    => 'places',
                    'ajax'      => true,
                    'upload'    => true,
                    'photoId'   => $this->PlacePhoto->id,
                    'thumb'     => true
                    );
        $result = $this->ImageProcess->processImage($params);
        
        if($result['success']){
            $filename = $result['filename'];            
        }else{
            $this->PlacePhoto->delete($this->PlacePhoto->id);
        }                             
        				
        $this->data['PlacePhoto']['photo_alt'] = $filename;
        
        $this->data['PlacePhoto']['location'] = $result['image'];
        $this->PlacePhoto->save($this->data);
        

        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        
        if(!$this->Session->check('Note.error')){
            #$this->Session->write('Note.ok',"All images uploaded successfully");
        }
        
        //update overall progress bar
        $this->_processCompleteness('uploadPhoto');
        
        //output the response
        $result['filename'] = $result['image'];
        echo json_encode($result);
        exit;
    }

    public function profilePhoto(){
        $u_id = $this->Auth->getUser();
        
        $params = array(
            'action'    => 'profiles',
            'ajax'      => true,
            'upload'    => true,
        );
        $result = $this->ImageProcess->processImage($params);
        
        //output the response
        echo json_encode($result);
        exit;
    }
    
    public function setPrimary($id = null){
        $userId = $this->Auth->getUser();
        $p_id = $this->Dashboard->getListingId();
        
        //load the models
        $this->loadModel('PlacePhoto');
        
        //remove previous primary photo
        $this->PlacePhoto->query('UPDATE place_photos SET place_photos.primary = \'no\' WHERE place_photos.id_place = '.$p_id.';');
        //set new primary photo
        $this->PlacePhoto->query('UPDATE place_photos SET place_photos.primary = \'yes\' WHERE place_photos.id = '.$id.';');
        
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/newprofiles/place/');
    }

    //file still exist
    public function DeletePhoto($id = null, $isAjax = null){
        $userId = $this->Auth->getUser();
        $p_id = $this->Dashboard->getListingId();

        $this->loadModel('PlacePhoto');
        $this->PlacePhoto->delete($id);
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        if(!is_null($isAjax) && $isAjax){
            echo 'success';
            exit;
        }
        $this->redirect('/newprofiles/place/');
    }

    public function DeleteProfilePhoto($filename = null, $isAjax = null){
        //delete the file
        if(!is_null($filename)){
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'profiles/'.$filename);
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'profiles/thumbs/'.$filename);
        }
        
        $userId = $this->Auth->getUser();
        
        $this->loadModel('Profile');
        
        $userDb = $this->Dashboard->getData();
        if(isset($userDb['Profile']) && $userDb['Profile']['photo_path'] && $userDb['Profile']['photo_path'] != 'default_avatar.png'){
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'profiles/'.$userDb['Profile']['photo_path']);
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'profiles/thumbs/'.$userDb['Profile']['photo_path']);
            
            $userDb['Profile']['photo_path'] = 'default_avatar.png';
            $this->Profile->save($userDb);
        }
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        if(!is_null($isAjax) && $isAjax){
            echo 'success';
            exit;
        }
        $this->redirect('/newprofiles/');
    }
    
    public function DeleteDogPhoto($filename = null, $isAjax = null){
        //delete the file
        if(!is_null($filename) && $filename !== 'default_dog.jpg'){
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'dogs/'.$filename);
            @unlink(WWW_ROOT.SYSTEM_PATH_W.'dogs/thumbs/'.$filename);
        }
        
        $userId = $this->Auth->getUser();
        
        $this->loadModel('Pet');
        
        $pet = $this->Pet->findByimage($filename);
        if(!is_null($pet) && $pet['Pet']['image'] && $pet['Pet']['image'] != 'default_dog.jpg'){
            /*@unlink(WWW_ROOT.'img/profiles/'.$userDb['Profile']['photo_path']);
            @unlink(WWW_ROOT . "img/profiles/thumbs/".$userDb['Profile']['photo_path']);
            */
            $pet['Pet']['image'] = '';
            $this->Pet->save($pet);
        }
        
        //updating listing modified field
        $this->Dashboard->updateListing();
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        if(!is_null($isAjax) && $isAjax){
            echo 'success';
            exit;
        }
        $this->redirect('/newprofiles/');
    }
    
    public function dogs(){
        $this->layout = 'new_listing_steps';
        
        //loading the models
        $this->loadModel('Place');
        $this->loadModel('Pet');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Profile');

        $userId = $this->Auth->getUser();
        //for the listing process in header
        $placeId = $this->Dashboard->getListingId();

        $place = $this->Dashboard->getData();
        //check to add completeness for pets
        if(is_array($place['Pet']) && count($place['Pet']) > 0){
            $this->_processCompleteness('Pets');
        }
        $cDetails = json_decode($place['Place']['c_details']);
        $this->set('completeness', $place['Place']['completeness']);
        $this->set('cDetails', $cDetails);
        $this->set('title', $place['Place']['title']);
        
        //get all the dogs for user
        $allDogs = $this->Pet->findAllByid_user($userId);
        $this->set('allDogs', $allDogs);
        //weather no dogs checkbox is checked
        if(is_array($cDetails) && in_array('Pets', $cDetails) && count($allDogs) <= 0){ 
            $checked = true;
        }else{ 
            $checked = false;
        }
        $this->set('noDogsChecked', $checked);
        
        //process place photo
        $photo = $this->PlacePhoto->find('first', array('conditions' => array(
            'PlacePhoto.id_place' => $placeId, 
            'PlacePhoto.primary' => 'ye'
        )));
        if(!$photo){
                $photo = $this->PlacePhoto->findByid_place($placeId);
        }
        if(!empty($photo)){
                $this->set('path', $photo['PlacePhoto']['location']);
        }else{
                $this->set('path', false);
        }
        
        $this->data['Pet']['other_pets'] = $place['Place']['other_pets'];
    }
    
    public function saveDog(){
        $userId = $this->Auth->getUser();
        
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);    
        //check if user is creating a place
        $placeId = $this->Dashboard->getListingId();
        
        $this->Session->delete('Note.error');
        
        //load the models
        $this->loadModel('Place');
        $this->loadModel('Pet');
        
        if(isset($this->data['Pet']['noDogs']) && $this->data['Pet']['noDogs'] == '1'){
            $noDogs = true;
        }else{
            $noDogs = false;
        }
        
        //update the other pets field
        if($this->data['Pet']['other_pets']){
            $userDb['Place']['other_pets'] = $this->data['Pet']['other_pets'];
            $this->Place->save($userDb);
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            //updating listing modified field
            $this->Dashboard->updateListing();
            $userDb = $this->Dashboard->getData();
        }
        
        if(!empty($this->data) && $noDogs == false ){

            try{
                if(is_array($this->data['each']) && count($this->data['each'])){
                    foreach($this->data['each'] as $item){
                        $this->_processSingleDog($item);
                    }
                }

                //updating listing modified field
                $this->Dashboard->updateListing();                        
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();

                $this->Session->write('Note.ok',"Dog info successfully saved");
                

                $dogs = $this->Pet->findAllByid_user($userId);
                if($dogs){
                    $this->_processCompleteness('Pets');
                }
                
                if($this->data['Pet']['isAddDog'] === 'true'){
                    $url = '/newprofiles/dogs/';
                }else{
                    $url = '/newprofiles/place/';
                }
                $this->redirect($url);
                
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
                $this->redirect('/newprofiles/dogs/');
            }               
        }else{
            if($noDogs == true){
                $this->_processCompleteness('Pets');
                $this->Session->write('Note.ok',"Info about your pets was successfully saved");
            }
            if(!(is_array($userDb['Pet']) && count($userDb['Pet']) > 0) && $noDogs == false){
                $this->Session->write('Note.error',"We need to know a little more about your pet.");
            }
                
            $this->redirect('/newprofiles/place/');
        }  
    }
    
    public function updateDog($d_id) {
        $this->layout = 'new_listing_steps';
        
        $userId = $this->Auth->getUser();
        
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);    
        //check if user is creating a place
        $placeId = $this->Dashboard->getListingId();
        
        //load the models
        $this->loadModel('Place');
        $this->loadModel('Pet');
        
        $current = $this->Pet->findAllByid($d_id);
        $image = ($current[0]['Pet']['image'])?$current[0]['Pet']['image']:'default_dog.jpg';
        //set to session
        $this->Session->write('Dog.oldImage', $image);
        
        $this->set('d_id', $d_id);
        $this->set('image', $image);
        
        if(!empty($this->data)){            
            try{
                if(!trim($this->data['Pet']['pet_name']) || !$this->data['Pet']['breed']){
                    throw new Exception('Please fill in all the required fields');
                }
                
                
                //process dog image
                $imagePath = WWW_ROOT.SYSTEM_PATH_W.'dogs/';
                $thumbPath = WWW_ROOT.SYSTEM_PATH_W.'dogs/thumbs/';
                
                $allowed = array('jpg','jpeg','gif','png');
                if(
                    isset($this->data['Pet']['image']) && $this->data['Pet']['image'] != '' && 
                    $this->data['Pet']['image'] != $this->Session->read('Dog.oldImage')
                ){
                    $params = array(
                        'path'      => $this->data['Pet']['image'],
                        'action'    => 'dogs',
                        'upload'    => true,
                        'photoId'   => $d_id,
                        'thumb'     => true,                            
                        );
                    $result = $this->ImageProcess->processImage($params);
                    if(isset($result['errors'])){
                        throw new Exception($result['errors']);
                    }
                    $image = $result['image'];
                    $this->data['Pet']['image'] = $image;
                }else{
                    unset($this->data['Pet']['image']);
                }
                //process dog image
                
                if(!$this->Session->check('Note.error')){
                    $toSave = array_merge($current[0]['Pet'], $this->data['Pet']);
                    $this->Pet->save($toSave);
                    
                    //updating listing modified field
                    $this->Dashboard->updateListing();                        
                    //clean up session dashboard entery
                    $this->Dashboard->cleanUp();
                    
                    #$this->Session->write('Note.ok',"Pet info successfully saved");
                }

                $dogs = $this->Pet->findAllByid_user($userId);
                $url = '/newprofiles/dogs/';
                
                $this->redirect($url);
                
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
                $this->redirect('/newprofiles/updateDog/'.$d_id);
            }               
        }else{            
            
             $this->data['Pet'] = $current[0]['Pet'];   
            //$this->redirect('/newprofiles/dogs/');
        }  
    }
        
    public function dogPhoto(){
        $u_id = $this->Auth->getUser();
        
        if(!isset($_REQUEST['currentNumber']) || !is_numeric($_REQUEST['currentNumber'])){
            $result['success'] = false;
        }
        
        $params = array(
                'action'    => 'dogs',
                'ajax'      => true,
                'upload'    => true,
                );
        $result = $this->ImageProcess->processImage($params);
        $result['numOfForm'] = $_REQUEST['currentNumber'];
        //output the response
        echo json_encode($result);
        exit;
    }
    public function dogPhotoSingle(){
        $u_id = $this->Auth->getUser();
        
        $params = array(
                'action'    => 'dogs',
                'ajax'      => true,
                'upload'    => true,
                );
        $result = $this->ImageProcess->processImage($params);
        //output the response
        echo json_encode($result);
        exit;
    }
    
    public function deleteDog($id = null){
        $userId = $this->Auth->getUser();
        
        $this->loadModel('Pet');
        $this->Pet->delete($id);
        $this->Dashboard->updateListing();
        $this->Dashboard->cleanUp();
        $this->redirect('/newprofiles/dogs/');
    }
    
    public function finePrint(){
        $userId = $this->Auth->getUser();
        $placeId = $this->Dashboard->getListingId();
        
        $userDb = $this->Dashboard->getData();
        
        $this->layout = ('new_listing_steps');
        //loading the models
        $this->loadmodel('PaymentOption');
        $this->loadmodel('User');
        
        $paymentOptions = $this->PaymentOption->findByuser_id($userId);
        if(!$paymentOptions){
            $firstTime = true;
        }else{
            $firstTime = false;
        }

        //proess the checked of payment option
        $paypalChecked = '';
        $checkChecked = '';
        if($paymentOptions){
            if('1' === $paymentOptions['PaymentOption']['has_paypal']){
                $paypalChecked = 'checked="checked"';
                $checkChecked = '';
            }
            if('1' === $paymentOptions['PaymentOption']['has_check']){
                $paypalChecked = '';
                $checkChecked = 'checked="checked"';
            }
        }
        $this->set('paypalChecked', $paypalChecked);
        $this->set('checkChecked', $checkChecked);
        
        if(!empty($this->data)){

            try{
                if(!empty($this->data['PaymentOption']['has_check'])){
                    //Check is selected

                    if(!trim($this->data['PaymentOption']['check_address'])){
                        throw new Exception("Address for check can't be left empty");
                    }
                    
/*
                    if($this->data['PaymentOption']['check_address'] != $this->Session->read('oldAddress')){
                        //set address data from list place
                        if(isset($this->data['PaymentOption']['ll']) && $this->data['PaymentOption']['ll']){
                            $ll = $this->data['PaymentOption']['ll'];
                            unset($this->data['PaymentOption']['ll']);
                            $ll = str_ireplace('(', '', $ll);
                            $ll = str_ireplace(')', '', $ll);
                            $ll = explode(',', $ll);

                            $this->data['PaymentOption']['lat'] = $ll[0];
                            $this->data['PaymentOption']['lng'] = $ll[1];

                            if(
                                round($this->data['PaymentOption']['lat'], 3) != round($userDb['Place']['lat'], 3) ||
                                round($this->data['PaymentOption']['lng'], 3) != round($userDb['Place']['lng'], 3)
                            ){
                                throw new Exception(
                                        "The address you have mentioned is much diferent than your home address, please verify your address and try again."
                                );
                            }
                        }
                    }
*/
                }
                else {
                    $this->data['PaymentOption']['has_check'] = 0;
                }

                if(!empty($this->data['PaymentOption']['has_paypal'])){
                    if (empty($this->data['PaymentOption']['paypal_email']) || !filter_var($this->data['PaymentOption']['paypal_email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Please enter a valid Paypal email address.");
                    }
                    if ($this->data['PaymentOption']['paypal_email'] != $this->data['PaymentOption']['paypal_confirm']) {
                        throw new Exception("Your Paypal email addresses must confirm.");
                    }
                }
                else {
                    $this->data['PaymentOption']['has_paypal'] = 0;
                }

                $this->data['PaymentOption']['user_id'] = $userId;
                if($paymentOptions){
                    $this->data['PaymentOption']['id'] = $paymentOptions['PaymentOption']['id'];
                }
                $result = $this->PaymentOption->save($this->data['PaymentOption']);
                if($firstTime){
                    $user = $this->User->findByid($userId);
                    $email = $user['User']['email'];
                    //send approval notification
                    
                    //$this->Mailer->adminApproveListing($placeId, $email);
                    $this->redirect('/places/afterlisting/');
                }else{
                    $this->Session->write('Note.ok','Preferences successfully saved');
                    $this->redirect('/newprofiles/');
                }
                
            }catch(Exception $ex){
                $this->Session->write('Note.error', $ex->getMessage());
                $this->redirect('/newprofiles/finePrint/');
            }
        }
        if(!$paymentOptions){
            $paymentOptions = array(
                'PaymentOption' => array(
                    'has_paypal' => 0,
                    'has_check'  => 0,
                ),
            );
        }else{
            $this->Session->write('oldEmail', $paymentOptions['PaymentOption']['paypal_email']);
            $this->Session->write('oldAddress', $paymentOptions['PaymentOption']['check_address']);
        }
        $this->data = $paymentOptions;
    }
    
    public function processServices(){
        //exit as this action is not for usual use
        //exit();
        
        //load the models
        $this->loadModel('User');
        $this->loadModel('Profile');
        $this->loadModel('Service');
        
        $queryProfile = "SELECT * from `profiles` Profile ";
        $queryService = "SELECT * from `services` Service ";
        
        $profiles = $this->Profile->query($queryProfile);
        $services = $this->Service->query($queryService);
        
        $userIds = array();
        if(is_array($services) && count($services)){
            foreach($services as $item){
                $service = $item['Service'];
                $userIds[] = $service['user_id'];
            }
        }
        
        if(is_array($profiles) && count($profiles)){
            foreach($profiles as $item){
                $profile = $item['Profile'];
                //check for unexistent user record in services row
                if(!in_array($profile['id_user'], $userIds) && $profile['id_user']){
                    //unexistent record
                    $data = array(
                        'Service' => array(
                            'user_id' => $profile['id_user'],
                            'walking_rate' => $this->_processFloatField($profile['walking_price']),
                            'walking_radius' => '',
                            'walking_bulk' => '',
                            'training_rate' => $this->_processFloatField($profile['training_price']),
                            'training_radius' => '',
                            'training_bulk' => '',
                            'sitting_rate' => $this->_processFloatField($profile['sitting_price']),
                            'sitting_radius' => '',
                            'sitting_bulk' => '',
                            'care_rate' => $this->_processFloatField($profile['day_care_price']),
                            'care_radius' => '',
                            'care_bulk' => '',
                            'other_rate' => '',
                            'other_radius' => '',
                            'other_bulk' => '',
                            'other_label' => '',
                        ),
                    );
                    $this->Service->create();
                    $this->Service->save($data);
                }
            }
        }
        
        die('Services data merged successfully, please verify.');
    }
    
    /**
     * Takes an array of single dog data and saves it accordingly
     * @param array $data The dog data
     * @throws Exception If some of validations has failed
     */
    protected function _processSingleDog($data){
        $userId = $this->Auth->getUser();

        $data = array(
            'Pet' => $data,
        );
        if(!trim($data['Pet']['pet_name']) || !$data['Pet']['breed']){
            throw new Exception('Please fill in all the required fields');
        }

        //set the user id
        $data['Pet']['id_user'] = $userId;
        
        //process dog image
        $tempVar = $data['Pet']['image'];
        $this->Pet->create();
        $this->Pet->save($data);
        $data['Pet']['image'] = $tempVar;
        $data['Pet']['id'] = $this->Pet->id;
        $imagePath = WWW_ROOT.SYSTEM_PATH_W."dogs/";
        $params = array(
                'path'      => $data['Pet']['image'],
                'action'    => 'dogs',
                'upload'    => true,
                'photoId'   => $this->Pet->id,
                'thumb'     => true
                );
        if(
            isset($data['Pet']['image']) && 
            file_exists($imagePath.$data['Pet']['image']) && 
            is_file($imagePath.$data['Pet']['image'])
        ){
            $result = $this->ImageProcess->processImage($params);
            if($result !== false){
                $image = $result['image'];                    
                $data['Pet']['image'] = $image;            
            }else{
                throw new Exception("Invalid image");
            }
        }else{
            unset($data['Pet']['image']);
        }
        //process dog image
        if(!$this->Session->check('Note.error')){
            $this->Pet->create();
            $this->Pet->save($data);
        }
    }


    /**
     * Method that checks for a particular name in completeness details and 
     * adds that name and percents to completeness of the place if the name not found. <br />
     * <b>Note: </b>this method does not perform any checks prior to adding completeness 
     * make sure to check and call to this method only in right place
     * @param string $name The name of completeness step to check
     */
    protected function _processCompleteness($name){
        $data = $this->Dashboard->getData();
        $cDetails = json_decode($data['Place']['c_details']);
        if(!$cDetails){
            $cDetails = array();
        }
        if(!in_array($name, $cDetails)){
            $cDetails[] = $name;
            $data['Place']['completeness'] = count($cDetails)*25;//to gain 100 in 4 steps
            $cDetails = json_encode($cDetails);
            $data['Place']['c_details'] = $cDetails;

            $res = $this->Place->save($data['Place']);
            
            $this->Dashboard->cleanUp();
        }
    }
    /**
     * Processes the value for float fields
     * @param string|float $value The value to process
     * @return float|null Return float equivalent of the value if the value is present or null otherwise
     */
    protected function _processFloatField($value){
        $value = trim($value);
        if(!$value){
            return null;
        }else{
            return (float)$value;
        }
    }

    /**
     * Method to check that all required prices are correct
     * @param array $prices An array of data with prices
     * @throws Exception If any of required prices were incorrect
     */
    protected function _validatePrices($prices){
        // array of fileds whcih need to be decimal
        $decimals = array(
            'nigthly_rates','weekly_rates', 'large_dogs','puppies','bath',
            'special_situations','security_deposit','cleaning_fees', 'taxi_service'
        );
        $requireds = array('nigthly_rates','cancellation_policy');
        
        foreach($prices as $key => $price){
            if(in_array($key, $decimals) && $price){
                $price = str_ireplace(',', '.', $price);
                $pattern = "#^[\d]+$#isx";
                if(preg_match($pattern, $price) == 0){
                    throw new Exception('all prices must be decimal');
                }
            }
            if(in_array($key,$requireds) && (!isset($price) || $price === '0')){
                    throw new Exception('Please fill in all the required fields');
            }
        }
        
    }
    
    /*public function test($id){
        //$this->Linkedinc->getFriends($id);
        $data = $this->Facebook->getFriends($id);
        var_dump($data);die;
    }*/
    
}
?>
