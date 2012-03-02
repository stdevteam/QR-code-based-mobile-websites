<?php

/**
 * Messaging handler controller
 * @property CheckComponent $Check Message text validator
 * @property AuthComponent $Auth Component for authentification
 */
class MessagesController extends AppController {

    public $components = array('Email', 'Dashboard', 'Auth', 'Check','Mailer');
    public $paginate = array(
        'limit' => 10,
        'order' => array(
            'th.created' => 'desc'
        )
    );

    public function inbox($order=null) {
        $userDb = $this->Dashboard->getData();
        $this->set('userDb', $userDb);
        $this->loadModel('Thread');
        $this->data['messages']['search'] = $order;
        $u_id = $this->Auth->getUser();
        if ($order && 1 == 2) {
            if ($order == 'unread') {
                $orderBy = array('modified desc');
                $union = "unread";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted != ' . $u_id . ' AND th.unread = ' . $u_id,
                );
            } elseif ($order == 'read') {
                $orderBy = array(
                    'modified desc'
                );
                $union = "read";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted !=' . $u_id . ' AND th.unread != ' . $u_id,
                );
            } elseif ($order == 'wait') {
                $orderBy = array('modified asc');
                $union = "wait";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.unreplied = ' . $u_id . ' AND th.deleted !=' . $u_id,
                );
            } elseif ($order == 'sent') {
                $orderBy = array('modified desc');
                $union = "sent";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted !=' . $u_id,
                );
            } elseif ($order == 'reservation') {
                $orderBy = array('modified desc');
                $union = "reservation";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted !=' . $u_id,
                );
            } else {
                $orderBy = array('modified desc');
                $union = "none";
                $conditions = array(
                    '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted !=' . $u_id,
                );
            }
        } else {
            $orderBy = array('modified desc');
            $union = "none";
            $conditions = array(
                '(th.from_id = ' . $u_id . ' OR th.to_id = ' . $u_id . ') AND th.deleted != ' . $u_id,
            );
        }
        //var_Dump($conditions,$union);die;
        $extra['u_id'] = $u_id;
        $extra['union'] = $union;
        $this->set('order', $order);
        $this->layout = 'User_Account_Page';
        $this->paginate = array(
            'conditions' => $conditions,
            'limit' => 10,
            'order' => $orderBy,
            'extra' => $extra
        );
        $this->data = $this->paginate('Thread');
        /* for($i=0;$i<count($this->data);$i++){
          $temp = $this->data[$i];
          $from_id = $temp['Message']['from_user'];
          $sender_info = $this->Message->query('SELECT users.first_name, users.last_name, profiles.photo_path FROM users, profiles WHERE users.id = '.$from_id.' AND profiles.id_user = '.$from_id);
          $this->data[$i]['Message']['from_user'] = $sender_info;
          } */
        //$message_count = count($message_count);
        //$this->set('m_count', $message_count);
        $this->set('msg_content', $this->data);
        $this->set('u_id', $u_id);
    }

    public function read($t_id) {
        $this->loadModel('Place');
        $this->loadModel('Offer');
        $this->loadModel('Thread');
        
        $userDb = $this->Dashboard->getData();
        $this->set('userDb', $userDb);
        
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $this->set('t_id', $t_id);
        $u_id = $this->Auth->getUser();
        //find topic
        $conditions = array(
            $u_id,
            $t_id,
        );
        $this->data = $this->Message->allMessages($conditions);
        if(!$this->data){
            $this->redirect('/messages/inbox/');
        }
        
        if($this->data){
            $sent = $this->Session->read('MessageSent');
            $this->Session->delete('MessageSent');
            $this->Thread->markAsRead($t_id, $u_id);
            $listing_owner = $this->Place->findByid($this->data[0]['messages']['id_listing']);
            $partner = ($this->data[0]['messages']['id_user'] == $u_id) ? $this->data[0]['messages']['from_user'] : $this->data[0]['messages']['id_user'];
            $offers = $this->Offer->getOffers($u_id, $partner);
            $this->set('listing_owner', $listing_owner['Place']['id_user']);
            $this->set('offers', $offers);
            $this->set('sent', $sent);
            //read message
            /* $newData = array();
              $count = 0;
              foreach($this->data as $item){
              if($item['messages']['tag'] == 'unread'){
              $this->data[$count]['messages']['tag']='read';
              $this->Message->save($this->data[$count]['messages']);
              }
              $count++;
              } */
            $this->set('messages', $this->data);
            $this->loadModel('Profile');
            $partnerInfo = $this->Profile->findByid_user($partner);
            $this->set('partner', $partnerInfo);
            $this->set('from', $partner);
            $this->set('user', $u_id);
        }else{
            $this->redirect('/messages/inbox/');
        }
    }

    public function delete($id) {
        $this->Message->delete($id);
        $this->redirect('/messages/inbox');
    }

    public function deleteAll($from) {
        $u_id = $this->Auth->getUser();
        $conditions = array(
            $u_id,
            $from
        );
        $this->data = $this->Message->allMessages($conditions);
        $count = 0;
        foreach ($this->data as $item) {
            if ($item['messages']['deleted_for'] == 0) {
                $this->data[$count]['messages']['deleted_for'] = $u_id;
                $this->Message->save($this->data[$count]['messages']);
            } elseif ($item['messages']['deleted_for'] != 0 && $item['messages']['deleted_for'] != $u_id) {
                $this->Message->delete($item['messages']['id']);
            }
            $count++;
        }
        $this->redirect('/messages/inbox');
    }

    //reply to a message. parameter $id is the message id which you are replying
    public function reply($t_id) {
        $userDb = $this->Dashboard->getData();
        $this->set('userDb', $userDb);
        $u_id = $this->Auth->getUser();
        /* $repliedmsg = $this->Message->findByid($id);
          $repliedmsg['Message']['replied'] = 1;
          $this->Message->save($repliedmsg); */
        $this->layout = 'blank_for_test';
        $to_id = $_POST['to'];
        $body_text = $_POST['body_text'];
        $message_text = $body_text;
        $body_text = $this->Check->filterText($u_id, $to_id, $body_text);
        
        $subject = $_POST['subject'];
        $to = $_POST['to']; //id user
        //$m_id = $_POST['m_id']; //id message
        $listingId = $_POST['id_listing'];
        $reply_data = $this->Message->query('SELECT users.first_name, users.last_name, email FROM users WHERE users.id = ' . $to);
        //Updating existing thread
        $this->loadModel('Thread');
        $this->Thread->updateRepliedThread($t_id, $u_id, $to_id, $body_text);
        //send reply
        
        $receiverName =  $reply_data[0]['users']['first_name'] . ' ' . $reply_data[0]['users']['last_name'];
        $lastName = $this->Session->read('User.last_name');
        $senderName = $this->Session->read('User.first_name') . ' ' . $lastName[0];
        $this->Mailer->messageReplyEmail($reply_data[0]['users']['email'],$subject,$receiverName,$senderName,$body_text);

        $this->Message->create();
        $this->data['Message']['id_user'] = $to;
        $this->data['Message']['from_user'] = $this->Auth->getUser();
        //$this->data['Message']['subject'] = $subject;
        $this->data['Message']['body_text'] = trim($body_text);
        $this->data['Message']['thread_id'] = $t_id;
        //$this->data['Message']['tag'] = 'unread';
        //$this->data['Message']['id_listing'] = $listingId;
        $this->Message->save($this->data);
        $this->Session->write('MessageSent', 'msg');
        echo $this->Message->id;
        exit();

        //$this->redirect('/messages/inbox/'.$id);
    }

    //carefull! this $id is a place id, do not confuse it with user id
    //this method is called from the listing page contact me button
    public function SendMessage($id = null) {
        $this->layout = 'blank_for_test';
        //var_dump($this->data);die;
        if(empty($this->data)){
            if(is_null($this->Auth->checkUser())){
                $this->redirect('/messages/LogInFirst/');
            }

            $owner_data = $this->Message->query('SELECT * FROM users, places WHERE places.id_user = users.id AND places.id = ' . $id);
            $full_name = $owner_data[0]['users']['first_name'] . ' ' . $owner_data[0]['users']['last_name'];
            $this->Session->write('oFullName', $full_name);
            $email_address = $owner_data[0]['users']['email'];
            $owner_id = $owner_data[0]['users']['id'];
            $this->Session->write('OwnerId', $owner_id);
            $this->Session->write('SendTo', $email_address);
            $this->Session->write('listingId', $id);
            $listing_title = $owner_data[0]['places']['title'];
            //setting variables
            $view_name = $owner_data[0]['users']['first_name'] . ' ' . $owner_data[0]['users']['last_name'][0];
            $this->set('full_name', $view_name);
            $this->set('email_address', $email_address);
            $this->set('listing_title', $listing_title);
            //set the owner id to view
            $this->set('toId', $owner_id);
        }
        if(!empty($this->data)){
            $u_id = $this->Auth->checkUser();
            
            if(!is_null($u_id)){
                $this->loadModel('Thread');
                $ounerId = $this->Session->read('OwnerId');
                $firstTime = $this->Thread->find('count',array(
                    'conditions' => array(
                        'Thread.to_id'      => $ounerId,
                        'Thread.from_id !=' => '1',
                        )
                    ));
                if($firstTime == 0){
                    $this->Mailer->firstMessageForHost($this->Session->read('SendTo'));
                }
                $this->data['Message']['from_user'] = $u_id;
                // fetching necessary data for thread
                $threadData['to_id'] = $ounerId;
                $threadData['from_id'] = $u_id;
                $body_text = $this->data['Message']['body_text'];
                $message_text = $body_text;
                $body_text = $this->Check->filterText($threadData['to_id'], $u_id, $body_text);

                $threadData['unread'] = $this->Session->read('OwnerId');
                $threadData['last_text'] = trim($body_text);
                $threadData['unreplied'] = $u_id;
                $threadData['listing_id'] = $this->Session->read('listingId');
                
                // data for thread fetched
                // Creating new thread for conversation
                $this->loadModel('Thread');
                $this->Thread->create();
                $data['Thread'] = $threadData;
                $this->Thread->save($data);
                $t_id = $this->Thread->id;
                
                // Thread saved and id is available for saving in messages
                $this->data['Message']['id_user'] = $this->Session->read('OwnerId');
                $this->Session->delete('OwnerId');
                $send_to = $this->Session->read('SendTo');
                $this->Session->delete('SendTo');
                
                // for lastname initial
                $lastName = $this->Session->read('User.last_name');
                //$this->data['Message']['subject'] = 'You have a message on Dog Vacay from '.$this->Session->read('User.first_name').' '.$lastName[0];
                //$this->data['Message']['tag'] = 'unread';
                //$this->data['Message']['result'] = '1'; //1 is for inquiry, 2 for accepted, 3 for denied
                $this->data['Message']['thread_id'] = $t_id;
                $this->data['Message']['id_listing'] = $this->Session->read('listingId');
                $this->data['Message']['body_text'] = trim($body_text);
                $this->Session->delete('listingId');
                $this->Message->save($this->data);
                
                $full_name = $this->Session->read('oFullName');
                $this->Session->delete('oFullName');
                //now send the notification to the user:
                $subject = 'You have a message on Dog Vacay from ' . $this->Session->read('User.first_name') . ' ' . $lastName[0];
                $lastName = $this->Session->read('User.last_name');
                $senderName = $this->Session->read('User.first_name') . ' ' . $lastName[0];
                $this->Mailer->sendMessageEmail($send_to,$subject,$full_name,$senderName,$body_text);
            }else{
                $this->redirect('/messages/LogInFirst/');
            }
            //redirect to message sent page.
            $this->redirect('/messages/MessageSent');
        }
    }

    public function MeetAndGreet($id = null) {
        $this->layout = 'blank_for_test';
        if(empty($this->data)){
            $byUId = $this->Auth->checkUser();
            if (is_null($byUId)) {
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
            $to = $this->data['Message']['to'];
            $this->loadModel('User');
            $this->loadModel('Place');
            $this->loadModel('Thread');
            $toUser = $this->User->findByid($to);
            $byUser = $this->User->findByid($this->data['Message']['by']);
            $place = $this->Place->findByid_user($to);
            if($u_id != ''){
                $ounerId = $this->data['Message']['to'];
                $firstTime = $this->Thread->find('count',array(
                    'conditions' => array(
                        'Thread.to_id'      => $ounerId,
                        'Thread.from_id !=' => '1',
                        )
                    ));
                if($firstTime == 0){
                    $this->Mailer->firstMessageForHost($toUser['User']['email']);
                }
                $notes_text = $this->data['Message']['notes'];
                $notes = $this->Check->filterText($this->data['Message']['to'], $u_id, $this->data['Message']['notes']);
                
                
                $message['Message']['id_user'] = $this->data['Message']['to'];
                $message['Message']['from_user'] = $u_id;
                $message['Message']['body_text'] = 'Meet and Greet request<br />' .
                        'Start Date - ' . $this->data['Message']['start_date'] . '<br />' .
                        'End Date - ' . $this->data['Message']['end_date'] . '<br />' .
                        'Dogs - ' . $this->data['Message']['dogs'] . '<br />' .
                        'Dogs - ' . $this->data['Message']['dogs'] . '<br />' .
                        'Preferred days and times for Meet and Greet  - ' . $this->data['Message']['preffered_days'] . '<br />' .
                        'Notes - ' . $notes . '<br />';
                //$message['Message']['result'] = '1';
                //$message['Message']['tag'] = 'unread';
                
                $message['Message']['id_listing'] = $place['Place']['id'];
                // fetching necessary data for thread
                $threadData['to_id'] = $this->data['Message']['to'];
                $threadData['from_id'] = $u_id;
                $threadData['unread'] = $this->data['Message']['to'];
                $threadData['last_text'] = trim($message['Message']['body_text']);
                $threadData['unreplied'] = $u_id;
                $threadData['listing_id'] = $message['Message']['id_listing'];
                $threadData['is_mag'] = 1;
                // data for thread fetched
                // Creating new thread for conversation
                $this->Thread->create();
                $data['Thread'] = $threadData;
                $this->Thread->save($data);
                $t_id = $this->Thread->id;
                // Thread saved and id is available for saving in messages

                $message['Message']['thread_id'] = $t_id;
                $this->Message->save($message);
                
                //now send the notification to the user:
                $this->data['Message']['notes'] = $notes;
                $this->Mailer->meetAndGreetEmail($this->data['Message'],$toUser,$byUser,$t_id);
            }
            //redirect to message sent page.
            $this->redirect('/messages/MagSent');
        }
    }

    public function checktext(){
        // TODO: make this a more robust function for checking text
        echo json_encode(array('status' => true, 'message' => '')); exit;

        $status = true;
        $message = '';
        
        $userId = $this->Auth->checkUser();
        if(is_null($userId)){
            $status = false;
            $message = 'No logged in user found';
        }

        $toId = $_REQUEST['to'];
        $text = $_REQUEST['body_text'];
        $res = $this->Check->checker($userId, $toId, $text);
        if($res !== true){
            $status = false;
            $message = 'Validation of text failed';
        }
        
        echo json_encode(array('status' => $status, 'message' => $message));
        exit;
    }

    public function MessageSent() {
        $this->layout = 'blank_for_test';
    }

    public function MagSent() {
        $this->layout = 'blank_for_test';
    }

    public function LogInFirst() {
        $this->layout = 'blank_for_test';
    }

    public function Search() {
        $userDb = $this->Dashboard->getData();
        $this->set('userDb', $userDb);
        $this->layout = 'User_Master_Page';
        $id = $this->Auth->getUser();
        $terms = $this->data['Message']['search'];
        $terms = explode(' ', $terms);
        $new = array();
        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) > 0) {
                $new[] = "'%{$term}%'";
            }
        }
        $result = $this->Message->search($new, $id);
        //sending to view
        $this->set('term', $terms);
        $this->set('msg_content', $result);
    }

    public function correctThreads() {
        $allMessages = $this->Message->query("select * from messages where `thread_id` = 0");
        $threads = array();
        $this->loadModel('Thread');
        foreach ($allMessages as $key => $message) {
            $message = $message['messages'];
            $preId = $message['id_user'] + $message['from_user'];
            if (array_key_exists($preId, $threads)) {
                $threads[$preId]['messages'][] = $message;
                $threads[$preId]['modified'] = $message['created'];
                $threads[$preId]['last_text'] = $message['body_text'];
            } else {
                $threads[$preId]['messages'][] = $message;
                $threads[$preId]['created'] = $message['created'];
                $threads[$preId]['last_text'] = $message['body_text'];
                $threads[$preId]['modified'] = $message['created'];
                $threads[$preId]['listing_id'] = $message['id_listing'];
            }
        }
        foreach ($threads as $thread) {
            $messageData = reset($thread['messages']);
            $data = array();
            $data['Thread']['to_id'] = $messageData['id_user'];
            $data['Thread']['from_id'] = $messageData['from_user'];
            $data['Thread']['created'] = $thread['created'];
            $data['Thread']['modified'] = $thread['modified'];
            $data['Thread']['last_text'] = $thread['last_text'];
            $data['Thread']['listing_id'] = $thread['listing_id'];
            if (isset($data['Thread']['to_id']) && isset($data['Thread']['from_id'])) {

                $this->Thread->create();
                $this->Thread->save($data);
                $t_id = $this->Thread->id;
                $messagesIds = '';
                foreach ($thread['messages'] as $message) {
                    $messagesIds.= "'" . $message['id'] . "',";
                }
                $messagesIds.= '+';
                $messagesIds = str_ireplace(',+', '', $messagesIds);
                $query = "UPDATE messages SET `thread_id` = " . $t_id .
                        " WHERE id IN (" . $messagesIds . ")";
                if ($this->Message->query($query)) {
                    echo $query . "<br />";
                } else {
                    echo "fail<br />";
                }
            } else {
                
            }
        }
        die("done<br />");
    }

    public function messageInfo() {
        $this->layout = 'blank_for_test';
    }
}
?>
