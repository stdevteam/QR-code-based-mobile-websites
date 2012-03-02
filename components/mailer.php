<?php
/**
 * MailerComponent
 *
 * This component is used for handling automated emailing stuff
 *
 * @package       dogvacay
 * @subpackage    dogvacay.mailer
 * @property EmailComponent $Email The dependency email component used for email stuff handling
 * @property EmailServiceComponent $EmailService The dependency email service component 
 * used for email through AWS stuff handling
 */
class MailerComponent extends Object{
    public $components = array('Email','EmailService');

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
    public function startup(&$controller) {
        $this->EmailService->delivery = (Configure::read('debug') == 0)
                ? 'aws_ses_raw'
                : 'debug';
    }
    
    /**
     * Sends an email to the user that have just registered
     * @param array $data An array of user and profile data
     * @return bool Returns true if email is sent successfully, false otherwise
     */
    public function userRegistrationEmail($data){
        $user = $data['User'];
        $profile = $data['Profile'];

        $email = $user['email'];
        $firstName = $user['first_name'];
        $lastName = $user['last_name'];
        $name = $firstName.' '.$lastName;

        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $name.' <'.$email.'>';
        $this->EmailService->sendAs = 'both';

        $this->EmailService->subject = 'You have just registered at Dogvacay.com!';
        $this->EmailService->template = 'registration';

        $this->controller->set('contentForEmail','');

        return $this->EmailService->send();
    }

    /**
     * Sends an email to the dog owner user after successfull booking
     * @param array $data An array of data needed for email template and subject body etc.
     * @return bool Returns true if email is sent successfully, false otherwise
     */
    public function bookUserEmail($data){
        extract($data);

        //notification for dog owner
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';    
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';    
        $this->EmailService->to = $dog_owner_email;          
        $this->EmailService->subject = 'Your booking information from DogVacay.com';    
        $this->EmailService->template = 'book_user'; 
        $this->EmailService->sendAs = 'both';

        $this->controller->set('user_name', $user_name); //Hi! <username>
        $this->controller->set('puppy_name', $pet_names); // <puppu_name> thanks you! Please review your booking...
        $this->controller->set('host_name', $host_name); // Please review your booking and contact your host, <host_name>
        $this->controller->set('home_pic', $pic);
        $this->controller->set('order', $order_id);
        $this->controller->set('drop_date', $drop_date);
        $this->controller->set('pick_date', $pick_date);
        $this->controller->set('dogs_quantity', $dogs_quantity);
        $this->controller->set('host_phone', $host_phone);
        $this->controller->set('host_mail', $host_mail);
        $this->controller->set('address', $address);
        $this->controller->set('total_charge', $total_charge);
        $this->controller->set('cancellation', $cancellation);

        return $this->EmailService->send();
    }

    /**
     * Sends an email to the hoster user after successfull booking
     * @param array $data An array of data needed for email template and subject body etc.
     * @return bool Returns true if email is sent successfully, false otherwise
     */
    public function bookHostEmail($data){
        extract($data);

        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';    
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';    
        $this->EmailService->to = $host_mail;          
        $this->EmailService->subject = 'Good News! Someone has booked your place at DogVacay.com';    
        $this->EmailService->template = 'book_host'; 
        $this->EmailService->sendAs = 'both';
        $this->controller->set('host_name', $host_name);
        $this->controller->set('puppy_name', $pet_names);
        $this->controller->set('dog_owner_name', $user_name);
        $this->controller->set('home_pic', $pic);
        $this->controller->set('order', $order_id);
        $this->controller->set('drop_date', $drop_date);
        $this->controller->set('pick_date', $pick_date);
        $this->controller->set('dogs_quantity', $dogs_quantity);
        $this->controller->set('dog_owner_phone', $dog_owner_phone);
        $this->controller->set('dog_owner_mail', $dog_owner_email);
        $this->controller->set('pet_info', $pet_info);
        $this->controller->set('dog_owner_address', $dog_owner_address);
        $this->controller->set('total_charge', $total_charge);
        $this->controller->set('cancellation', $cancellation);

        return $this->EmailService->send();
    }

    public function sendEmail($to,$subject,$type,$details=null){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        if($subject=='registration'){
            $this->EmailService->subject = 'NOTIFICATION: new user registration';
            $this->EmailService->template = 'notification';
        }else{
            $this->EmailService->subject = 'NOTIFICATION: new user error';
            $this->EmailService->template = 'notification';
        }           
        $this->EmailService->to = 'Concierge Dog Vacay <concierge@dogvacay.com>';
        $this->EmailService->sendAs = 'text';
        //$this->controller->set('type',$type);
        //$this->controller->set('subject',$subject);
        if(!isset($details)){
            $this->controller->set('contentForEmail','');
        }else{
            $content = "New user registered on DogVacay.com<br />".
                    "First name: ".$details['first_name']."<br />".
                    "Last name: ".$details['last_name']."<br />".
                    "Email: ".$details['email']."<br />";
            $this->controller->set('contentForEmail',$content);
        }

        return $this->EmailService->send();
    }

    public function startListingForUser($email,$place_id){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $email;
        $this->EmailService->sendAs = 'both';

        $this->EmailService->subject = 'Take a sneak peek at your profile at DogVacay.com!';
        $this->EmailService->template = 'beginListingForUser'; 

        $this->controller->set('id_place', $place_id);

        return $this->EmailService->send();
    }
    public function listingActivated($email,$place_id){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = array('Concierge <concierge@dogvacay.com>'); #, 'partners@science-inc.com');
        $this->EmailService->to = $email;
        $this->EmailService->sendAs = 'html';

        $this->EmailService->subject = 'Your profile has been approved at DogVacay.com!';
        $this->EmailService->template = 'approveListingForUser'; 

        $this->controller->set('id_place', $place_id);

        return $this->EmailService->send();
    }
    
    public function messageForApproved($email,$message,$place_id,$subject){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = array('Concierge <concierge@dogvacay.com>'); #, 'partners@science-inc.com');
        $this->EmailService->to = $email;
        $this->EmailService->sendAs = 'html';

        $this->EmailService->subject = ($subject == 'approved')? 'Your profile has been approved at DogVacay.com!' :  'Your profile has been unapproved at DogVacay.com!';
        $this->controller->set('message', '<p>'.nl2br($message).'</p>');
        $this->EmailService->template = 'approveMessage'; 
        $this->controller->set('id_place', $place_id);
        return $this->EmailService->send();
    }
    

    public function passwordRecovery($user){
        $this->EmailService->from = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $user['User']['email'];
        $this->EmailService->sendAs = 'both';

        $this->EmailService->subject = 'Password recovery information for DogVacay.com';
        $this->EmailService->template = 'password_recovery'; 

        $this->controller->set('user', $user);

        return $this->EmailService->send();
    }

    /**
     * Sends an email to the user that have just registered to confirm his registration
     * @param array $data An array of user and profile data
     * @return bool Returns true if email is sent successfully, false otherwise
     */
    public function userConfirmEmail($data,$verify){
        $user = $data['User'];
        $profile = $data['Profile'];

        $email = $user['email'];
        $firstName = $user['first_name'];
        $lastName = $user['last_name'];
        $name = $firstName.' '.$lastName;

        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $name.' <'.$email.'>';
        $this->EmailService->sendAs = 'both';

        $this->EmailService->subject = 'Please confirm your email address to activate your account at DogVacay.com';
        $this->EmailService->template = 'confirm_registration';

        $url = FULL_BASE_URL . '/users/getVerification/' . $verify;
        $this->controller->set('contentForEmail', $user);
        $this->controller->set('url',$url);

        return $this->EmailService->send();
    }
    
    public function aboutPageEmail($email){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->to = 'Concierge <concierge@dogvacay.com>';    
        $this->EmailService->subject = 'NOTIFICATION: new email subscriber';
        $this->EmailService->template = 'about_page_email';
        $this->EmailService->sendAs = 'both';
        $this->controller->set('email', $email);
        
        return $this->EmailService->send();
    }
    
    public function messageReplyEmail($to,$subject,$toName,$fromName,$bodyText){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $to;
        $this->EmailService->subject = '[Dog Vacay] ' . $subject;
        $this->EmailService->template = 'main_contact';
        $this->EmailService->sendAs = 'both';
        $this->controller->set('user_name', $toName);
        $this->controller->set('sender_name', $fromName);
        $this->controller->set('text', $bodyText);
        $this->EmailService->send();
    }
    
    public function sendMessageEmail($to,$subject,$toName,$fromName,$bodyText){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $to;
        $this->EmailService->subject = $subject;
        $this->EmailService->template = 'main_contact';
        $this->EmailService->sendAs = 'both';
        
        $this->controller->set('user_name', $toName);
        $this->controller->set('sender_name', $fromName);
        $this->controller->set('text', $bodyText);
        $this->EmailService->send();
    }
    
    public function meetAndGreetEmail($data,$toUser,$fromUser,$tId){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $toUser['User']['email'];
        $this->EmailService->subject = 'Request for Meet & Greet at DogVacay.com';
        $this->EmailService->template = 'meet_and_greet';
        $this->EmailService->sendAs = 'both';
        $this->controller->set('data', $data);
        $this->controller->set('t_id', $tId);
        $this->controller->set('toUser', $toUser);
        $this->controller->set('byUser', $byUser);
        $this->EmailService->send();
    }
    
    public function adminApproveListing($pId,$email){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->to = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->subject = 'NOTIFICATION: listing waiting for approval (' . $pId . ')';    
        $this->EmailService->template = 'approve'; 
        $this->EmailService->sendAs = 'text';
        $this->controller->set('id_place', $pId);
        $this->controller->set('email', $email);
        $this->EmailService->send();
    }
    
    public function adminListingStarted($pId,$email){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->to = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->subject = 'NOTIFICATION: listing started (' . $pId . ')';    
        $this->EmailService->template = 'beginListing'; 
        $this->EmailService->sendAs = 'both';
        $this->controller->set('id_place', $pId);
        $this->controller->set('email', $email);
        $this->EmailService->send();
    }
    
    public function sendReviewRequest($email,$senderName,$options,$url){
        $this->EmailService->from = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = '<'.$email.'>';
        $this->EmailService->subject = 'Invitation to review '.$senderName.' on DogVacay.com';
        $this->EmailService->template = 'request_recommendation';
        $this->EmailService->sendAs = 'both';
        if($options){
            if(isset($options['name'])){
                $this->controller->set('receiver_name', $options['name']);
            }
            if(isset($options['custom_message'])){
                $this->controller->set('custom_message', $options['custom_message']);
            }
        }
        $this->controller->set('url',$url);
        $this->controller->set('sender_name',$senderName);
        if($this->EmailService->send()){
            return true;
        }else{
            return false;
        }
    }
    
    /*
     *Sending internal message to user from Concierge and notification to Email
     *@param int $u_id Recever user id 
     *@param string $message message body 
    */
    public function mailAndMessageToUser($u_id,$message){
        $this->controller->loadModel('Thread');
        $this->controller->loadModel('Message');
        $this->controller->loadModel('User');
        $user = $this->controller->User->findByid($u_id);
        $concierge = $this->controller->User->findByid('1');
        /*$threadData = $this->controller->Thread->find(
                'first',array(
                    'conditions' => array(
                        'Thread.to_id'  => $u_id,
                        'Thread.from_id'=> $concierge['User']['id']
                        )
                    )
                );*/
        $thread = $this->controller->Thread->query("SELECT * from `threads` where `to_id`='".$u_id."' and `from_id`='".$concierge['User']['id']."'");
        if($thread){
            $threadData = $thread[0]['threads'];
        }
        //sending message to users inbox from concerge         
        $this->controller->data['Message']['from_user'] = $concierge['User']['id'];
        // fetching necessary data for thread
        $threadData['to_id'] = $user['User']['id'];
        $threadData['from_id'] = $concierge['User']['id'];
        $body_text = $message;                           
        $threadData['unread'] = $user['User']['id'];
        $threadData['last_text'] = trim($body_text);
        $threadData['unreplied'] = $concierge['User']['id'];
        // data for thread fetched
        // Creating new thread for conversation        
        $this->controller->Thread->create();
        $data['Thread'] = $threadData;
        $this->controller->Thread->save($data);
        $t_id = $this->controller->Thread->id;
        // Thread saved and id is available for saving in messages
        $this->controller->data['Message']['id_user'] = $user['User']['id'];                           
        $send_to = $user['User']['email'];                           
        // for lastname initial
        $name = $user['User']['first_name'].'  '.$user['User']['last_name']; 
        $this->controller->data['Message']['thread_id'] = $t_id;           
        $this->controller->data['Message']['body_text'] = trim($body_text);               
        $this->controller->Message->save($this->controller->data);
        $full_name = $concierge['User']['first_name'];
        //now send the notification to the user:
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';
        $this->EmailService->to = $send_to;          
        $this->EmailService->subject = 'You have a message on Dog Vacay from '.$full_name;    
        $this->EmailService->template = 'main_contact'; 
        $this->EmailService->sendAs = 'both';
        $this->controller->set('user_name', $name);                           
        $this->controller->set('sender_name', $full_name);
        $this->controller->set('text',$body_text);
        $this->EmailService->send();      
    }
    
    /**
     *
     * @param array $data host_name,pet_name,user_name,pic,drop_dat,pick_date,dogs_quantity,dog_owner_phone,dog_owner_email,pet_info,dog_owner_address,total_charge,cancellation
     * @return bool 
     */
    public function toPlaceOwnerForBooking($data){
        extract($data);
        
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';    
        $this->EmailService->bcc = 'Concierge <concierge@dogvacay.com>';    
        $this->EmailService->to = $host_mail;          
        $this->EmailService->subject = 'Good News! Someone has booked your place at DogVacay.com';    
        $this->EmailService->template = 'book_host_new'; 
        $this->EmailService->sendAs = 'both';
        $this->controller->set('host_name', $host_name);
        $this->controller->set('puppy_name', $pet_names);
        $this->controller->set('dog_owner_name', $user_name);
        $this->controller->set('home_pic', $pic);
        $this->controller->set('drop_date', $drop_date);
        $this->controller->set('pick_date', $pick_date);
        $this->controller->set('dogs_quantity', $dogs_quantity);
        $this->controller->set('dog_owner_phone', $dog_owner_phone);
        $this->controller->set('dog_owner_mail', $dog_owner_email);
        $this->controller->set('pet_info', $pet_info);
        $this->controller->set('dog_owner_address', $dog_owner_address);
        $this->controller->set('total_charge', $total_charge);
        $this->controller->set('cancellation', $cancellation);

        return $this->EmailService->send();
    }
    
    public function firstMessageForHost($email){
        $this->EmailService->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->EmailService->bcc = array('Concierge <concierge@dogvacay.com>'); #, 'partners@science-inc.com');
        $this->EmailService->to = $email;
        $this->EmailService->sendAs = 'html';

        $this->EmailService->subject = 'You have first message at DogVacay.com!';
        $this->EmailService->template = 'firstMessageForHost'; 

        
        return $this->EmailService->send();
    }
}
