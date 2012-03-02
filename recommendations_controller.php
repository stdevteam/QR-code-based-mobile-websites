<?php
/**
 * Recommendations handler controller
 * @property Recommendation $Recommendation The recommendations model
 * @property RequestHandlerComponent $RequestHandler The Request Handler Component
 * @property MailerComponent $Mailer General email sender component
 * @property AuthComponent $Auth Authentication component
 */
class RecommendationsController extends AppController{
    public $components = array(
        'Email',
        'RequestHandler',
        'Dashboard',
        'Auth',
        'Mailer'
    );
	
    public $name = 'Recommendations';
    public $paginate = array(
        'limit' => 10,
        'order' => array(
            'Recommendation.date' => 'desc'
        )
    );
    
    
    public function index(){
        if($this->Auth->getUser()){
            $this->redirect('/recommendations/view');
        }
    }
    
    // showing existing recommendations
    public function view(){
        $this->layout = 'User_Master_Page';
        $u_id = $this->Auth->getUser();
        $this->set('u_id', $u_id);
        $recommendations = $this->Recommendation->AllForUser($u_id,false);
        if($recommendations){
            $this->set('recommendations', $recommendations);
        }
        $this->paginate = array(
                'conditions' => array('Recommendation.user_id = '.$u_id),
                'limit' => 10,
                'order' => array(
                    'Recommendation.date' => 'desc'
                )
        );
        
        $this->data = $this->paginate('Recommendation');
    }
    public function request(){        
        $u_id = $this->Auth->getUser();
        $this->set('u_id',$u_id);
        
        $isAjax = $this->RequestHandler->isAjax();
        
        if($isAjax){
            $this->layout = false;
            $this->render('request_ajax');
        }else{
            $this->layout = 'User_Master_Page';
        }
        
        if(!empty($this->data)){
            //$this->data['Recommendation']['emails'] = explode(',', $this->data['Recommendation']['emails']);
            $this->data['Recommendation']['emails'] = preg_split("/[\s,]+/", $this->data['Recommendation']['emails']);
            //$this->Recommendation->set($this->data['Recommendation']);
            $ruls = array('maxcount' => 10,'required' => true,);      
            $result = $this->emails($this->data['Recommendation']['emails'],$ruls);
            //var_dump($result===true);die;
            if($result === true){
                //$options = array();
                if(isset($this->data['Recommendation']['custom_message']) && $this->data['Recommendation']['custom_message']!= ''){
                    $options['custom_message'] = $this->data['Recommendation']['custom_message'];
                }
                $emails =  $this->data['Recommendation']['emails'];
                $count = 0;
                $message = 'You have requested reviews from: ';
                foreach($emails as $email){
                    $email = trim($email);
                    $message .= $email.' ';                
                    if($this->sendInvitation($u_id, $email, $options)){
                        $count++;
                    }
                }
                if(isset($options['custom_message'])){
                    $message .= 'with the message : '.$options['custom_message'];
                }
                $this->Mailer->mailAndMessageToUser($u_id, $message);
                
                if($count == count($emails)){
                    if($isAjax){
                        $this->redirect('/recommendations/view');
                    }else{
                        $this->Session->write('Note.ok', "Your request for review has been submitted");
                        $this->redirect('/newprofiles/services');                                             
                    }
                }else{
                    $this->Session->write('Note.error', "Cannot send email at this moment, please try later");
                }                
            }else{
                //$this->set('error',$this->Recommendation->validationErrors);
                //var_dump($this->Recommendation->validationErrors);die;
                $this->Session->write('Note.error', $result);
            }
            
            //Redirect back to profile page, as now this buntion works with lightbox
            $this->redirect('/newprofiles/services');            
        }
    }
    public function requestBKP(){        
        $u_id = $this->Auth->getUser();
        $this->set('u_id',$u_id);
        
        $isAjax = $this->RequestHandler->isAjax();
        
        if($isAjax){
            $this->layout = false;
            $this->render('request_ajax');
        }else{
            $this->layout = 'User_Master_Page';
        }
        
        if(!empty($this->data)){
            //$this->data['Recommendation']['emails'] = explode(',', $this->data['Recommendation']['emails']);
            $this->data['Recommendation']['emails'] = preg_split("/[\s,]+/", $this->data['Recommendation']['emails']);
            //$this->Recommendation->set($this->data['Recommendation']);
            $ruls = array('maxcount' => 10,'required' => true,);      
            $result = $this->emails($this->data['Recommendation']['emails'],$ruls);
            //var_dump($result===true);die;
            if($result === true){
                //$options = array();
                if(isset($this->data['Recommendation']['custom_message']) && $this->data['Recommendation']['custom_message']!= ''){
                    $options['custom_message'] = $this->data['Recommendation']['custom_message'];
                }
                $emails =  $this->data['Recommendation']['emails'];
                $count = 0;
                $message = 'You have requested reviews from: ';
                foreach($emails as $email){
                    $email = trim($email);
                    $message .= $email.' ';                
                    if($this->sendInvitation($u_id, $email, $options)){
                        $count++;
                    }
                }
                die("the end");
                if(isset($options['custom_message'])){
                    $message .= 'with the message : '.$options['custom_message'];
                }
                $this->Mailer->mailAndMessageToUser($u_id, $message);
                
                if($count == count($emails)){
                    if($isAjax){
                        $this->redirect('/recommendations/view');
                    }else{
                        $this->Session->write('rec.sent', 1);
                        $this->redirect('/profiles/view');
                    }
                }else{
                    $this->set('error', "Cannot send email at this moment, please try later");
                    $this->Session->write('Note.error', "Cannot send email at this moment, please try later");
                }                
            }else{
                //$this->set('error',$this->Recommendation->validationErrors);
                //var_dump($this->Recommendation->validationErrors);die;
                $this->Session->write('Note.error', $result);
            }
            
            //Redirect back to profile page, as now this buntion works with lightbox
            $this->redirect('/profiles/view');            
        }
    }
    
    function delete($id){
        $u_id = $this->Auth->getUser();
        $this->Recommendation->delete(array('id' => $id,'user_id' => $u_id));
        $this->redirect('/recommendations/view');
    }
    
    function approve($id){
        $u_id = $this->Auth->getUser();
        $data = array('Recommendation' => array(array('id' => $id,'user_id' => $u_id), 'approved' => 'approved'));
        $this->Recommendation->save($data);
        
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/recommendations/view');
    }
    
    function decline($id){
        $u_id = $this->Auth->getUser();
        $data = array('Recommendation' => array(array('id' => $id,'user_id' => $u_id), 'approved' => 'declined'));
        $this->Recommendation->save($data);
        
        //clean up session dashboard entery
        $this->Dashboard->cleanUp();
        $this->redirect('/recommendations/view');
    }
    
    public function read($id){
        $this->layout = 'User_Master_Page';
        $this->set('rec_id', $id);
        $u_id = $this->Auth->getUser();

        //find topic
        $recommendation = $this->Recommendation->ViewDetails($id);
        $message_owner = $recommendation['Recommendation']['user_id'];
        $canread = false;
        //security check
        if($message_owner == $u_id){
                $canread = true;
        }else{
                $canread = false;
        }

        //read message
        if($canread){

                if($recommendation['Recommendation']['read'] == 'unread'){
                        $recommendation['Recommendation']['read'] = 'read';
                        $this->Recommendation->save($recommendation);

                        //clean up session dashboard entery
                        $this->Dashboard->cleanUp();
                }


                $body_text = $recommendation['Recommendation']['content'];
                $to = $recommendation['Recommendation']['by_user_id'];
                $date = $recommendation['Recommendation']['date'];
                $listing = $recommendation['Recommendation']['listing_id'];
                $status = $recommendation['Recommendation']['approved'];
                $byUser = $recommendation['users']['first_name'].' '.$recommendation['users']['last_name'];
                $userImage = $recommendation['profiles']['photo_path'];

                $this->set('userImage', $userImage);
                $this->set('status', $status);
                $this->set('byUser', $byUser);
                $this->set('body_text', $body_text);
                $this->set('to', $to);
                $this->set('date', $date);
                $this->set('listing', $listing);

        }
    }
        
    protected function sendInvitation($u_id, $email, $options=null){
        $this->loadModel('User');
        $user = $this->User->find('first',array('conditions' => array('User.id' => $u_id)));
        $place = $this->Dashboard->getData();
        $plId = $place['Place']['id'];
        if(!$user){
            return false;
        }
        $forUser = $user['User']['id'];
        $sender_name = $user['User']['first_name'].' '.$user['User']['last_name'];
        $token = base64_encode($forUser . '+' . md5($email . microtime()));
        $url = FULL_BASE_URL."/recommendations/write/". $token;
        $alreadyExist = $this->Recommendation->find('first', array(
            'conditions' => array(
                'Recommendation.user_id' => $forUser,
                'Recommendation.email'  => $email
                )
            ));
        if($alreadyExist){
            if($alreadyExist['Recommendation']['approved'] == 'approved'){
                return true;
            }elseif($alreadyExist['Recommendation']['approved'] == 'declined'){
                return true;
            }elseif($alreadyExist['Recommendation']['approved'] == 'pending'){
                $token = $alreadyExist['Recommendation']['token'];
                $url = FULL_BASE_URL."/recommendations/write/". $token;
                if($this->Mailer->sendReviewRequest($email,$sender_name,$options,$url)){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            $this->Recommendation->create();
            $recommendation['listing_id'] = $plId;
            $recommendation['user_id'] = $u_id;
            $recommendation['token'] = $token;
            $recommendation['email'] = $email;
            $recommendation['approved'] = 'pending';
            $this->Recommendation->save($recommendation);var_dump($email);
            if($this->Mailer->sendReviewRequest($email,$sender_name,$options,$url)){
                return true;
            }else{
                return false;
            }
        }
    }
        
    function write($user=null){
        $u_id = $this->Auth->checkUser();
        $isAjax = $this->RequestHandler->isAjax();
        if($isAjax){
            if($_POST['content']=='' || $_POST['name']=='' || $_POST['good_bad']==''){
                //$this->Session->write('Note.error',"All fileds are required");
                echo 'empty';
                die;
            }            
            $token = trim($_POST['for_user']);
            $userInfo = explode('+', base64_decode($_POST['for_user']));
            $for_id = $userInfo[0];
            $recommendation = $this->Recommendation->findBytoken($token);
            if(!$recommendation){
                echo 'error';
                die;
            }
            if(0 >= $for_id){
                echo 'error';
                die;
            }
            $recommendation['Recommendation']['content'] = $_POST['content'];
            $recommendation['Recommendation']['name'] = $_POST['name'];
            $recommendation['Recommendation']['good_bad'] = ($_POST['good_bad']!='')?(int)$_POST['good_bad']:'';
            $recommendation['Recommendation']['by_user_id'] = $u_id;
            //$recommendation['Recommendation']['user_id'] = $for_id;
            //$recommendation['Recommendation']['listing_id'] = 0;
            $recommendation['Recommendation']['date'] = date( 'Y-m-d H:i:s');
            $recommendation['Recommendation']['approved'] = 'approved';
            $recommendation['Recommendation']['token'] = null;

            //var_dump($this->Recommendation->save($recommendation));die;
            if($this->Recommendation->save($recommendation)){
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->Session->write('Rec.complete',1);
                die;
                //$this->redirect('/recommendations/thankyou');
            }else{
                //$this->set('error',$this->Recommendation->validationErrors);
                //$this->Session->write('Note.error',"Cannot send email at this moment, please try later");
                //$this->redirect('/recommendations/write');
                echo 'error';
                die;
            }
        }
        $tokenKey = $user;
        //var_dump($this->Recommendation->findBytoken($tokenKey));die;
        if(!$this->Recommendation->findBytoken($tokenKey)){
            //duplicate attempt made to write a review using same token, may need some error here
            $this->redirect('/');
        }
        $forUser = base64_decode($user);
        $forUser = explode('+', $forUser);
        $forUser = $forUser[0];
        $this->loadModel('User');
        $userInform = $this->User->find('first',array('conditions' => array('User.id' => $forUser)));
        if(!$userInform){
            $this->redirect('/');
        }
        if($u_id){
            $byUserId = $this->Recommendation->find('first',array('conditions' => array('Recommendation.by_user_id' => $u_id, 'Recommendation.user_id' => $forUser)));
            if($byUserId){
                $this->redirect('/');
            }
        }
        $this->Session->write('Redirect.url','/recommendations/write/'.$user);
        if($u_id){
            $user_data = $this->Recommendation->query('SELECT users.first_name, users.last_name, email FROM users WHERE users.id = '.$u_id);
            $user_name = $user_data[0]['users']['first_name'].'  '.$user_data[0]['users']['last_name'];
            $this->set('name',$user_name);
        }else{
            $this->set('name','');
        }
        $this->layout = 'User_Master_Page';
        $forUserName = $userInform['User']['first_name'].' '.$userInform['User']['last_name'];
        $this->set('forUser', $tokenKey);
        //$this->set('u_id',$u_id);
        $this->set('forUserName', $forUserName);
        $this->Session->write('Rec.forUserName',$forUserName);

    }
                  
    /*function thankyou(){
        $this->Auth->getUser();
        if(!$this->Session->check('Rec.complete')){
            $this->redirect('/profiles/view');
        }else{
            $this->set('forUserName',$this->Session->read('Rec.forUserName'));
            $this->Session->delete('Rec.forUserName');
            $this->Session->delete('Rec.complete');
            $this->layout = 'User_Master_Page';
        }

    }*/
    
    function emails($check, $rule) {        
        //$rule = func_get_arg(func_num_args()-1);
        //var_dump($rule);
        $field = key($check);
        $value = $check[$field];
        //var_dump(key($check));die;
        //$emails = $this->getEmails($value);                
        $errors = array();
        //$validation =& Validation::getInstance();
        foreach($check as $email) {
            $email = trim($email);                    
            if(preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',$email)==0 && $email!=''){
                $errors []= "Email '$email' is invalid.";                    
            }
        }
        if(isset($rule['maxcount']) && count($value) > $rule['maxcount']){
            $errors [] = "Up to {$rule['maxcount']} emails are allowed.";
        }

        if(isset($rule['required']) && trim($value[0])== ''){
            return 'Email field is required';
        }

        if(!empty($errors)){
            return implode("\n", $errors);
        }
        return true;
    }
}
?>
