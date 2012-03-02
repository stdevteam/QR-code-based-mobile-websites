<?php
class MessagesController extends AppController{

    public $components = array('Email','Dashboard','Auth');
    public $paginate = array(
        'limit' => 10,
        'order' => array(
            'Message.created' => 'desc'
            )
        );
    
    public function inbox($order=null){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        
        $this->data['messages']['search'] = $order;
        $u_id = $this->Auth->getUser();
        if($order){
            if($order=='unread'){
            $orderBy=array(
                    'messages.tag desc',
                    'messages.created desc' 
                    );
            $conditions=array(
                'messages.id_user = '.$u_id.' AND messages.deleted_for!='.$u_id,
                'messages.from_user'
                );
            }elseif($order=='read'){
                $orderBy=array(
                    'messages.tag asc',
                    'messages.created desc'
                    );
                $conditions=array(
                    'messages.id_user = '.$u_id.' AND messages.deleted_for!='.$u_id,
                    'messages.from_user'
                    );
            }elseif($order=='wait'){
                $orderBy=array(
                    'messages.created asc'
                    );
                $conditions=array(
                    'messages.from_user = '.$u_id.' AND messages.deleted_for!='.$u_id.' AND messages.replied = \'0\' ',
                    'messages.id_user',
                    'foo'
                );
            }elseif($order=='sent'){
                $orderBy=array(
                    'messages.created desc'
                );
                $conditions=array(
                    'messages.from_user = '.$u_id.' AND messages.deleted_for!='.$u_id,
                    'messages.id_user',
                    'foo'
                );
                
            }elseif($order=='reservation'){
                $orderBy=array(
                    'messages.created desc'
                );
                $conditions=array(
                    'messages.id_user = '.$u_id.' AND messages.deleted_for!='.$u_id,
                    'messages.from_user'
                    );
            }else{
                $orderBy=array(
                    'messages.created desc'
                );
                $conditions=array(
                    'messages.id_user = '.$u_id.' AND messages.deleted_for!='.$u_id,
                    'messages.from_user'
                );
            }            
        }else{
           $orderBy=array(
                    'messages.created desc'
                );
                $conditions=array(
                    'messages.id_user = '.$u_id.' AND messages.deleted_for != '.$u_id,
                    'messages.from_user'
                    );
        }
        $this->set('order',$order);
        $this->layout = 'User_Account_Page';
        $this->paginate = 	array(
            'conditions'    => $conditions,
            'limit'         => 10,
            'order'         => $orderBy,
            );
        $this->data = $this->paginate('Message');
        /*for($i=0;$i<count($this->data);$i++){
            $temp = $this->data[$i];
            $from_id = $temp['Message']['from_user'];
            $sender_info = $this->Message->query('SELECT users.first_name, users.last_name, profiles.photo_path FROM users, profiles WHERE users.id = '.$from_id.' AND profiles.id_user = '.$from_id);
            $this->data[$i]['Message']['from_user'] = $sender_info;
        }*/
        //$message_count = count($message_count);
        //$this->set('m_count', $message_count);
        $this->set('msg_content', $this->data);
    }

    function read($from_id,$msg_id){
        $this->loadModel('Place');
        $this->loadModel('Offer');
        
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $this->set('msg_id', $msg_id);
        $u_id = $this->Auth->getUser();
        
        //find topic
        $conditions=array(
          $u_id,
          $from_id,
        );
        $this->data = $this->Message->allMessages($conditions);
        $listing_owner=$this->Place->findByid($this->data[0]['messages']['id_listing']);
        $offers=$this->Offer->getOffers($u_id,$from_id);
        $this->set('listing_owner',$listing_owner['Place']['id_user']);
        $this->set('offers',$offers);
        //read message
        $newData=array();
        $count=0;
        foreach($this->data as $item){
            if($item['messages']['tag'] == 'unread'){
                $this->data[$count]['messages']['tag']='read';
                $this->Message->save($this->data[$count]['messages']);
            }
            $count++;
        }   
       //$this->Message->save($this->data);
    /*    //$subject = $this->data['Message']['subject'];
        $body_text = $this->data['Message']['body_text'];
        $to = $this->data['Message']['from_user'];
        $date = $this->data['Message']['created'];
        $listing = $this->data['Message']['id_listing'];

        //$this->set('subject', $subject);
        $this->set('body_text', $body_text);
        $this->set('to', $to);
        $this->set('date', $date);
        $this->set('listing', $listing);
*/
        $this->set('messages', $this->data);
        $this->set('from',$from_id);
        $this->set('user',$u_id);
    }

    function delete($id){
        $this->Message->delete($id);
        $this->redirect('/messages/inbox');
    }
    function deleteAll($from){
       $u_id= $this->Auth->getUser();
       $conditions=array(
           $u_id,
           $from
       );
       $this->data = $this->Message->allMessages($conditions);
       $count=0;
       foreach($this->data as $item){
           if($item['messages']['deleted_for']==0){
               $this->data[$count]['messages']['deleted_for']=$u_id;
               $this->Message->save($this->data[$count]['messages']);
           }elseif($item['messages']['deleted_for']!=0 && $item['messages']['deleted_for']!=$u_id){
               $this->Message->delete($item['messages']['id']);
           }
           $count++;
       }
       $this->redirect('/messages/inbox');
    }

    //reply to a message. parameter $id is the message id which you are repling
    function reply($id){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $repliedmsg= $this->Message->findByid($id);
        $repliedmsg['Message']['replied'] = 1;
        $this->Message->save($repliedmsg);
        $this->layout = 'blank_for_test';
        $body_text = $_POST['body_text'];
        $subject = $_POST['subject'];
        $to = $_POST['to']; //id user
        //$m_id = $_POST['m_id']; //id message
        $listingId=$_POST['id_listing'];


        $reply_data = $this->Message->query('SELECT users.first_name, users.last_name, email FROM users WHERE users.id = '.$to);
        
        //send reply
        $this->Email->from = 'Dog Vacay <noreply@dogvacay.com>';
        $this->Email->bcc = 'Concierge at Dog Vacay <concierge@dogvacay.com>';
        $this->Email->to = $reply_data[0]['users']['email'];          
        $this->Email->subject = $subject;    
        $this->Email->template = 'main_contact'; 
        $this->Email->sendAs = 'both';
        $this->set('user_name', $reply_data[0]['users']['first_name'].' '.$reply_data[0]['users']['last_name']);
        $this->set('sender_name', $this->Session->read('User.first_name').' '.$this->Session->read('User.last_name'));
        $this->set('text', $body_text);
        $this->Email->send();

        $this->Message->create();

        $this->data['Message']['id_user'] = $to;
        $this->data['Message']['from_user'] = $this->Auth->getUser();
        //$this->data['Message']['subject'] = $subject;
        $this->data['Message']['body_text'] = trim($body_text);
        $this->data['Message']['tag'] = 'unread';
        $this->data['Message']['id_listing'] = $listingId;
        $this->Message->save($this->data);
        
        echo $this->Message->id;
        exit();
        
        //$this->redirect('/messages/inbox/'.$id);
    }

    //carefull! this $id is a place id, do not confuse it with user id
    //this method is called from the listing page contact me button
    function SendMessage($id = null){

            $this->layout = 'blank_for_test';
            //var_dump($this->data);die;
            if(empty($this->data)){
                    if(is_null($this->Auth->checkUser())){
                        $this->redirect('/messages/LogInFirst/');
                    }
                    $owner_data = $this->Message->query('SELECT * FROM users, places WHERE places.id_user = users.id AND places.id = '.$id);
                    $full_name = $owner_data[0]['users']['first_name'].' '.$owner_data[0]['users']['last_name'];
                    $this->Session->write('oFullName', $full_name);
                    $email_address = $owner_data[0]['users']['email'];
                    $owner_id = $owner_data[0]['users']['id'];
                    $this->Session->write('OwnerId', $owner_id);
                    $this->Session->write('SendTo', $email_address);
                    $this->Session->write('listingId', $id);
                    $listing_title = $owner_data[0]['places']['title'];
                    //setting variables
                    $view_name = $owner_data[0]['users']['first_name'].' '.$owner_data[0]['users']['last_name'][0];
                    $this->set('full_name', $view_name);
                    $this->set('email_address', $email_address);
                    $this->set('listing_title', $listing_title);
            }
            if(!empty($this->data)){
                $u_id = $this->Auth->checkUser();
                if(!is_null($u_id)){
                            $this->data['Message']['from_user'] = $u_id;
                            $this->data['Message']['id_user'] = $this->Session->read('OwnerId');
                            $this->Session->delete('OwnerId');
                            $send_to = $this->Session->read('SendTo');
                            $this->Session->delete('SendTo');
                            // for lastname initial
                            $lastName = $this->Session->read('User.last_name');
                            //$this->data['Message']['subject'] = 'You have a message on Dog Vacay from '.$this->Session->read('User.first_name').' '.$lastName[0];
                            $this->data['Message']['tag'] = 'unread';
                            $this->data['Message']['result'] = '1'; //1 is for inquiry, 2 for accepted, 3 for denied
                            $this->data['Message']['id_listing'] = $this->Session->read('listingId');
                            $this->data['Message']['body_text'] = trim( $this->data['Message']['body_text']);
                            $this->Session->delete('listingId');
                            $this->Message->save($this->data);
                            $full_name = $this->Session->read('oFullName');
                            $this->Session->delete('oFullName');
                            //now send the notification to the user:
                            $this->Email->from = 'Dog Vacay <noreply@dogvacay.com>';
                            $this->Email->bcc = 'Concierge at Dog Vacay <concierge@dogvacay.com>';
                            $this->Email->to = $send_to;          
                            $this->Email->subject = 'You have a message on Dog Vacay from '.$this->Session->read('User.first_name').' '.$lastName[0];    
                            $this->Email->template = 'main_contact'; 
                            $this->Email->sendAs = 'both';
                            $this->set('user_name', $full_name);
                            $lastName = $this->Session->read('User.last_name');
                            $this->set('sender_name', $this->Session->read('User.first_name').' '.$lastName[0]);
                            $this->set('text', $this->data['Message']['body_text']);
                            $this->Email->send();
                    }else{
                        $this->redirect('/messages/LogInFirst/');
                    }
                    //redirect to message sent page.
                    $this->redirect('/messages/MessageSent');
            }
            
    }
    function MeetAndGreet($id = null){
        $this->layout = 'blank_for_test';
        if(empty($this->data)){
            $byUId = $this->Auth->checkUser();
            if(is_null($byUId)){
                $this->redirect('/messages/LogInFirst/');
            }
            if(!isset($id)){
                $this->redirect('/');
            }
            //setting variables
            $this->set('byUId', $byUId);
            $this->set('toUId', $id);
        }else{
            $u_id = $this->Auth->getUser();
            if($u_id != ''){
                
                $to  = $this->data['Message']['to'];
                $message['Message']['id_user'] = $this->data['Message']['to'];
                $message['Message']['from_user'] = $u_id;
                $message['Message']['body_text'] = 'Meet and Greet request<br />'.
                        'Start Date - '.$this->data['Message']['start_date'].'<br />'.
                        'End Date - '.$this->data['Message']['end_date'].'<br />'.
                        'Dogs - '.$this->data['Message']['dogs'].'<br />'.
                        'Dogs - '.$this->data['Message']['dogs'].'<br />'.
                        'Preferred days and times for Meet and Greet  - '.$this->data['Message']['preffered_days'].'<br />'.
                        'Notes - '.$this->data['Message']['notes'].'<br />';
                $message['Message']['result'] = '1';
                
                $message['Message']['tag'] = 'unread';
                //var_dump($message);die;
                
                $this->loadModel('User');
                $this->loadModel('Place');
                $toUser = $this->User->findByid($to);
                $byUser = $this->User->findByid($this->data['Message']['by']);
                $place = $this->Place->findByid_user($to);
                $message['Message']['id_listing'] = $place['Place']['id'];
                $this->Message->save($message);
                $this->data['Message']['id'] = $this->Message->id;
                //var_dump($place);die;
                //now send the notification to the user:
                $this->Email->to = $toUser['User']['email'];
                $this->Email->from = 'Dog Vacay <noreply@dogvacay.com>';
                $this->Email->bcc = 'Concierge at Dog Vacay <concierge@dogvacay.com>';
                $this->Email->subject = "Request for Meet & Greet";
                $this->Email->template = 'meet_and_greet'; 
                $this->Email->sendAs = 'both';
                $this->set('data', $this->data['Message']);
                $this->set('toUser', $toUser);
                $this->set('byUser', $byUser);
                $this->Email->send();
            }
            //redirect to message sent page.
            $this->redirect('/messages/MagSent');
        }
    }

    function MessageSent(){
        $this->layout = 'blank_for_test';
    }

    function MagSent(){
        $this->layout = 'blank_for_test';
    }

    function LogInFirst(){
        $this->layout = 'blank_for_test';	
    }
    function Search(){
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $this->layout = 'User_Master_Page';
        $id = $this->Auth->getUser();
        $terms=$this->data['Message']['search'];
        $terms=explode(' ',$terms);
        $new=array();
        foreach($terms as $term){
            $term=trim($term);
            if(strlen($term)>0){           
                $new[]="'%{$term}%'";
            }
        }        
        $result=$this->Message->search($new,$id);
        //sending to view
        $this->set('term', $terms);
        $this->set('msg_content', $result);
    }
    function repaire_msg_listing_id(){
        
    }

}

?>
