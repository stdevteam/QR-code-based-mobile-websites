<?php
/**
 * User stuff handling controller
 * @property MailerComponent $Mailer Component that handles various email processing
 */
class UsersController extends AppController{
    public $name = 'Users';
    public $components = array('Cookie','Email', 'Mailer','Auth','Dashboard','RequestHandler');
    
    function beforeFilter() {
        parent::beforeFilter();
        $this->Cookie->name = 'rememberMe';  
        $this->Cookie->time =  3600*24*5;  
        $this->Cookie->path = '/';   
        $this->Cookie->domain = '';     
        $this->Cookie->secure = false;  //i.e. only sent if using secure HTTPS  
        $this->Cookie->key = 'qSI232qs*&sXOw!';
    }
    
    public function index(){
        $u_id = $this->Auth->getUser();
        $this->redirect('/messages/inbox/');
    }

    public function add(){
        $this->set('title_for_layout', 'DogVacay | User registration');
        $this->layout = 'User_Master_Page';
        if(!is_null($this->Auth->checkUser())){
            $this->redirect('/messages/inbox/');
        }
        if (!empty($this->data)) {
            // validation of registration data
            try {
                $this->User->ValidateRegistration($this->data['User']);
                $this->data['User']['password'] = md5($this->data['User']['password'].'dga');
                $this->data['User']['confirm_password'] = md5($this->data['User']['confirm_password'].'dga');
                $this->data['User']['active'] = 1;
                
                if($this->User->save($this->data)){                      
                    $email = $this->data['User']['email'] ;
                    $verification = $this->User->setVerification($email);
                    $this->loadModel('Profile');
                    $u_id = $this->User->id;
                    $this->data['Profile']['id_user'] = $u_id;
                    $this->Profile->save($this->data);

                    //clean up session dashboard entry
                    $this->Dashboard->cleanUp();
                    if($this->data['User']['remember'] == "1"){
                        $this->remember();
                    }
                    $this->Mailer->userConfirmEmail($this->data, $verification);
                    $this->Mailer->sendEmail('','registration', 'with form', $this->data['User']);

                    //send notification to registering user
                    //$this->Mailer->userRegistrationEmail($this->data);
                    $u_id = $this->User->id;

                    $message = '<p>Thank you for joining Dog Vacay!</p>';
                    $message .= '<p>Get started:</p>';
                    $message .= '<ul>';
                    $message .= '<li><a href="' . FULL_BASE_URL . '/newprofiles/">List your Home or Services</a></li>';
                    $message .= '<li><a href="' . FULL_BASE_URL . '/places/">Find a Place for your Dog to Stay</a></li>';
                    $message .= '</ul>';
                    $this->Mailer->mailAndMessageToUser($u_id, $message);

                    $this->Session->write('User.id', $u_id);
                    $this->Session->write('User.first_name', $this->data['User']['first_name']);
                    $this->Session->write('User.last_name', $this->data['User']['last_name']);
                    $redirect = $this->Session->read('Redirect.url');
                   // var_dump($redirect);die;
                    if((isset($redirect) || $redirect != '') && $redirect == '/newprofiles/'){
                         $this->redirect('/newprofiles/');
                    }else{
                         $this->redirect('/places/');
                    }
                }
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error', $message);
            }
        }
        
        //data for facebook registration
        App::import('Vendor', 'facebook', array('file' => 'facebook/facebook.php'));
        $facebook = new Facebook(array(
                        'appId' => Configure::read("FB_APP_ID"),
                        'secret' => Configure::read("FB_APP_SECRET"),
                ));
        // Get User ID
        $user = $facebook->getUser();

        $loginUrl = $facebook->getLoginUrl(array('scope' => 'user_about_me,user_birthday,email'));
        $this->set('fb_login', $loginUrl);
        
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }

        if(isset($user_profile)){
            $email = $user_profile['email'];
            $first_name = $user_profile['first_name'];
            $last_name = $user_profile['last_name'];
            $password =  md5($user_profile['id']);
            
            $new_user['User']['email'] = $email;
            $new_user['User']['first_name'] = $first_name;
            $new_user['User']['last_name'] = $last_name;
            $new_user['User']['password'] = $password;
            $new_user['User']['active'] = '1';
            
            $check = $this->User->findByemail($email);
            if(!$check){
                $this->User->save($new_user);
                $verification = $this->User->setVerification();
                $u_id = $this->User->id;
                $this->loadModel('Profile');
                $source = "http://graph.facebook.com/{$user_profile['id']}/picture";
                $dest = "/system/profiles/{$user_profile['id']}.jpg";
                copy($source, WWW_ROOT.$dest);
                $photo_path= $user_profile['id'].".jpg";
                $new['Profile']['photo_path'] = $photo_path;
                $new['Profile']['id_user'] = $u_id;
                $this->Profile->save($new);
                
                //clean up session dashboard entry
                $this->Dashboard->cleanUp();
                
                //Send the registration email
                //$this->Mailer->sendEmail('','registration', 'with Facebook', $new_user['User']);
                
                $this->Session->write('User.id', $u_id);
                $this->Session->write('User.first_name', $new_user['User']['first_name']);
                $this->Session->write('User.last_name',  $new_user['User']['last_name']);                
                
                //process the redirect
                $this->_processRedirect();
                //$this->redirect('/places/ListPlace');
            }else{
                $this->Session->write('Note.error', "This profile already exists, please sign in.");
            }
        }      
    }
    
    public function getVerification($getVerification){        
        $user = $this->User->findByverification($getVerification);        
        if($user){
            $email = $user['User']['email'];
            $this->Session->write('Note.ok', "Thank You for confirming email address!");
            $this->User->removeVerification($email);
            $this->redirect('/users/login');
        }else{
            $this->redirect('/');
        }                  
    }

    public function login(){
        //remove this on 14.01.2011
        if($this->Session->check('Sess.prob')){
            $custUrl = str_ireplace('http://', '', FULL_BASE_URL);    
            setcookie("PHPSESSID", '258', time()-3600, "/", "dogvacay.com");
            setcookie("CAKEPHP", '258', time()-3600, "/", "dogvacay.com");
            setcookie("PHPSESSID", '258', time()-3600, "/", "beta.dogvacay.com");
            setcookie("CAKEPHP", '258', time()-3600, "/", "beta.dogvacay.com");
            $this->Session->write('Sess.prob');            
        }
        $this->loadModel('Place');
        $this->layout = 'User_Master_Page';
        if(!is_null($this->Auth->checkUser())){
            $this->redirect('/messages/inbox/');
        }
        if(!empty($this->data) || $this->Cookie->read('squeeded')){
            // checking for remember me cookie
            if($this->Cookie->read('squeeded')){
                $user = $this->User->getUserByEmail($this->Cookie->read('squeeded'));
            }else{
                $user = $this->User->validateLogin($this->data['User']);
            }
            if(!empty($user)){
                if($this->data['User']['remember'] == "1"){
                    $this->remember();
                }
                $this->Session->write('User.id', $user['User']['id']);
                $this->Session->write('User.first_name', $user['User']['first_name']);
                $this->Session->write('User.last_name', $user['User']['last_name']);
                $this->Session->setFlash('Succesfully Logged in!');
                $this->User->saveSec($user['User']['id'],$this->RequestHandler->getClientIp());
                //$place = $this->Place->findByid_user($user['User']['id']);
                if($this->Session->check('Redirect.url')){
                    $url = $this->Session->read('Redirect.url');
                    $this->Session->delete('Redirect.url');
                    /*if(!empty($place)){
                        $this->Session->write('Place.id', $place['Place']['id']);                                    
                    }*/
                    $this->redirect($url);
                }else{                               
                    $this->redirect('/messages/inbox/');
                }                
            }else{
                //echo '<SCRIPT>alert(\'invalid username or password!\');</SCRIPT>';
                $this->Session->write('Note.error', "Invalid username or password!");
            }
        }
        //data for facebook login...
        App::import('Vendor', 'facebook', array('file' => 'facebook/facebook.php'));
        $facebook = new Facebook(array(
                        'appId' => Configure::read("FB_APP_ID"),
                        'secret' => Configure::read("FB_APP_SECRET"),
                ));
        // Get User ID
        $user = $facebook->getUser();

        if(is_null($this->Auth->checkUser())){
            if ($user) {
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    $user_profile = $facebook->api('/me');
                } catch (FacebookApiException $e) {
                    error_log($e);
                    $user = null;
                }
            }
            // Login or logout url will be needed depending on current user state.
            if (!$user) {
                $loginUrl = $facebook->getLoginUrl(array('scope' => 'user_about_me,user_birthday,email'));
                $this->set('fb_login', $loginUrl);
            }
            if(isset($user_profile) ){
                $petvacay_user = $this->User->findByemail($user_profile['email']);
                if($petvacay_user){
                    $u_id = $this->User->id;
                    $this->Session->write('User.id', $petvacay_user['User']['id']);
                    $this->Session->write('User.first_name', $petvacay_user['User']['first_name']);
                    $this->Session->write('User.last_name',  $petvacay_user['User']['last_name']);
                    if($this->Session->check('Redirect.url')){
                        $url = $this->Session->read('Redirect.url');
                        $this->Session->delete('Redirect.url');
                        $this->redirect($url);
                    }else{
                        $this->redirect('/messages/inbox/');
                        //$this->redirect('/');
                    }
                    //debug($petvacay_user);
                }
            }
        }
    }

    function logout(){
        $this->Cookie->delete('squeeded');
        $this->Session->destroy();
        $this->redirect('/');
    }

    // saving encoded email address of user for future validation
    function remember(){
        $this->Cookie->write('squeeded',$this->data['User']['email']);
    }

    function settings(){
        $u_id = $this->Auth->getUser();
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $this->loadModel('EmailSetting');
        $this->set('u_id', $u_id);
        
        if($this->data){            
            $user = $this->User->findByid($u_id);
            if(md5($this->data['User']['oldPassword'].'dga') == $user['User']['password']){
                if($this->data['User']['newPassword'] && $this->data['User']['confirmPassword']){
                    if($this->data['User']['newPassword'] == $this->data['User']['confirmPassword']){
                        $user['User']['password'] = md5($this->data['User']['newPassword'].'dga');
                        $this->User->save($user);
                            
                        //clean up session dashboard entery
                        $this->Dashboard->cleanUp();
                        //$this->set('message',"Password changed successfully");
                        $this->Session->write('Note.ok', "Password changed successfully !");
                    }else{
                        //$this->set('message',"Passwords don't match, please try again");
                        $this->Session->write('Note.error', "Passwords don't match, please try again");
                    }
                }else{
                    //$this->set('message',"Please fill new password and confirmation");
                    $this->Session->write('Note.error', "Please fill new password and confirmation");
                }
            }else{
                //$this->set('message',"Old password is invalid");
                $this->Session->write('Note.error', "Old password is invalid");
            }
        }
        $settings = $this->EmailSetting->findByid_user($u_id);
        if(!$settings){
            $this->EmailSetting->create();
            $this->EmailSetting->save(array('id_user' => $u_id));
            
            //clean up session dashboard entry
            $this->Dashboard->cleanUp();
            $this->redirect('/users/settings');
        }
        $this->data['EmailSetting']['news'] = $settings['EmailSetting']['news'];
        $this->data['EmailSetting']['newsletter'] = $settings['EmailSetting']['newsletter'];
        $this->data['EmailSetting']['upcoming_reservation'] = $settings['EmailSetting']['upcoming_reservation'];
        $this->data['EmailSetting']['write_review'] = $settings['EmailSetting']['write_review'];
        $this->data['EmailSetting']['received_review'] = $settings['EmailSetting']['received_review'];
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        
    }
    
    // request password recovery
    public function recover(){
        if(!empty($this->data)){
            $email = $this->data['User']['email'];
            if(filter_var($email, FILTER_VALIDATE_EMAIL) && $this->User->checkIsActive($email)){
                $verification = $this->User->setVerification($email);
                $user = $this->User->findByemail($email);
                if($this->Mailer->passwordRecovery($user)){
                    $this->Session->write('Note.ok', "Password recovery request successfully sent, please check your email inbox ");
                }else{
                    $this->Session->write('Note.error', "We weren't able to send You password recovery email, please contact our support");
                }
            }else{
                $this->Session->write('Note.error', "User with this email adress doesn't exist");
            }
        }
        $this->layout = 'User_Master_Page';
    }
    
    // checking verification token and processing password verification
    public function password($verification = null){
        if(is_null($verification)){
            if($this->Session->check('sec.token')){
                $verification = $this->Session->read('sec.token');
            }else{
                $this->redirect('/users/login/');
            }
        }else{
            $this->Session->write('sec.token',$verification);
            $this->redirect('/users/password/');
        }
        $this->layout = 'User_Master_Page';
        $user = $this->User->findByverification($verification);
        if(empty($this->data)){
            if(!$user){
                $this->Session->write('Note.error', "You are using invalid or outdated link , please request password recovery once again or contact our support");
                $this->Session->delete('sec.token');
            }
        }else{
            if($this->data['User']['password'] && $this->data['User']['confirm_password']){
                if($this->data['User']['password'] == $this->data['User']['confirm_password']){
                    $user['User']['password'] = md5($this->data['User']['password'].'dga');
                    $this->User->save($user);
                    $this->User->removeVerification($user['User']['email']);
                    $this->Session->delete('sec.token');
                    $this->Session->write('Note.ok', "Password changed successfully, please login !");
                }else{
                    //$this->set('message',"Passwords don't match, please try again");
                    $this->Session->write('Note.error', "Passwords don't match, please try again");
                }
            }else{
                $this->Session->write('Note.error', "Please fill new password and confirmation");
            }
        }
    }
    
    /**
     * Processes the after registration redirect logic
     */
    protected function _processRedirect(){
        $defaultRedirectUrl = '/';        
        if($this->Session->check('Redirect.url')){           
            $url = $this->Session->read('Redirect.url');            
            $this->Session->delete('Redirect.url');
            if(stripos($url, 'places/') !== false  ){
                $this->redirect($url);                
            }else{
                $this->redirect($defaultRedirectUrl);
            }
        }else{            
            $this->redirect($defaultRedirectUrl);
        }
    }
    
    public function afterRegistration(){
        $this->layout = 'User_Master_Page';
    }
    
    public function afterRegistration_1(){        
        $this->layout = 'User_Master_Page';
    }
}
?>
