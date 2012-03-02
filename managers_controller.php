<?php
/**
 * Managers logic handler class
 * @property RequestHandlerComponent $RequestHandler Request processor
 * @property Recommendation $Recommendation The Recommendations model
 * @property AuthComponent $Auth Authentication Component
 * @property Pet $Pet Model to handle pets
 */
class ManagersController extends AppController {

    public $name = 'Managers';
    public $components = array(
        'Cookie', 'Image', 'Auth', 'Mailer', 'RequestHandler'
    );

    function beforeFilter() {
        parent::beforeFilter();
        $this->Cookie->name = 'rememberMe';
        $this->Cookie->time = 3600 * 24 * 5;
        $this->Cookie->path = '/';
        $this->Cookie->domain = '';
        $this->Cookie->secure = false;  //i.e. only sent if using secure HTTPS  
        $this->Cookie->key = 'qSI232qs*&sXOw!';
        $this->loadModel('Content');
    }

    public function index() {
        $u_id = $this->Auth->getManager();
        $this->layout = 'Admin_Page';
    }

    public function pages() {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        $list = $this->Content->query("SELECT * from `contents` WHERE `type` = 'home' OR `type` = 'pages' ");
        $this->set('pages', $list);
        $this->paginate = array(
            'conditions' => array("Content.type = 'home' OR Content.type = 'pages'"),
            'limit' => 10,
            'order' => array(
                'Content.date' => 'desc'
            )
        );
        $this->data = $this->paginate('Content');
    }

    public function login() {
        $this->loadModel('User');
        $this->layout = 'Admin_Page';
        if ($this->Auth->checkManager()) {
            $this->redirect('/managers/listings');
        }
        if (!empty($this->data) || $this->Cookie->read('mango')) {
           
            // checking for remember me cookie
            if ($this->Cookie->read('mango')) { //var_dump($this->Cookie->read('mango'));die();
                $user = $this->User->findByemail($this->Cookie->read('mango'));
            } else {
                $user = $this->User->validateAdmin($this->data['Manager']);
            }            
            if (!empty($user)) {
                if ($this->data['Manager']['remember'] == "1") {
                    $this->remember();
                }
                $this->Session->write('Manager.id', $user['User']['id']);
                $this->Session->setFlash('Succesfully Loged in!');
                if ($this->Session->check('Redirect.url')) {
                    $url = $this->Session->read('Redirect.url');
                    $this->Session->delete('Redirect.url');
                    $this->redirect($url);
                } else {
                    $this->redirect('/managers/listings');
                }
            } else {
                echo '<SCRIPT>alert(\'invalid username or password!\');</SCRIPT>';
            }
        }
    }

    public function logout() {
        $this->Cookie->delete('mango');
        $this->Session->destroy();
        $this->redirect('/');
    }

    // saving encoded email address of user for future validation
    public function remember() {
        $this->Cookie->write('mango', $this->data['Manager']['email']);
    }

    public function edit($id) {
        $this->layout = 'Admin_Page';
        $this->set('page_id', $id);
        $u_id = $this->Auth->getManager();
        //find page
        $this->data = $this->Content->findByid($id);
        $message_owner = $this->data['Content']['manager_id'];
        $canread = true;
        //security check
        /*if ($message_owner == $u_id) {
            $canread = true;
        } else {
            $canread = false;
        }*/
        if ($canread) {
            $this->set('id', $id);
            $this->set('u_id', $u_id);
            if ($this->data['Content']['type'] == "slider") {
                $this->set('type', $this->data['Content']['type']);
                $this->loadModel('Place');
                $places = $this->Place->find('all', array('conditions' => array('approved' => 'yes'), 'fields' => array('id', 'full_address')));
                $listings = array();
                foreach ($places as $place) {
                    $listings[$place['Place']['id']] = $place['Place']['full_address'];
                }
                $this->set('places', $listings);
                $this->set('badge', $this->data['Content']['badge']);
            } else {
                $this->set('type', null);
            }
        } else {
            $this->redirect('/managers/login');
        }
    }

    public function save() {
        if ($this->data) {
            $u_id = $this->Auth->getManager();
            //if ($u_id == $this->data['Content']['manager_id']) {
                if (!isset($this->data['Content']['id'])) {
                    $this->Content->create();
                    $this->data['Content']['date'] = date('Y-m-d');
                }
                if (isset($this->data['Content']['image']) && $this->data['Content']['image']['error'] == UPLOAD_ERR_OK) {
                    $name = explode('.', $this->data['Content']['image']['name']);
                    $extension = end($name);
                    $filename = md5($this->data['Content']['image']['name']) . "." . $extension;
                    move_uploaded_file($this->data['Content']['image']['tmp_name'], dirname(dirname(__FILE__)) . '/webroot'.SYSTEM_PATH.'slider/' . $filename);
                    $sizes = getimagesize(WWW_ROOT.SYSTEM_PATH.'slider/' . $filename);
                    if ($sizes[0] > 800 || $sizes[1] > 800) {
                        $this->Image->set_paths(WWW_ROOT.SYSTEM_PATH.'slider/', WWW_ROOT.SYSTEM_PATH.'slider/');
                        $this->Image->width = 800;
                        $this->Image->height = 800;
                        $thumb = $this->Image->thumb(WWW_ROOT.SYSTEM_PATH.'slider/' . $filename);
                        rename($thumb,WWW_ROOT.SYSTEM_PATH.'slider/' . $filename);
                    }
                    $this->data['Content']['image'] = $filename;
                } else {
                    unset($this->data['Content']['image']);
                }
                $this->Content->save($this->data);
                $this->redirect('/managers/edit/' . $this->Content->id);
            //}
        }
    }

    public function add($type=null) {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        $this->set('u_id', $u_id);
        $this->data['Content']['manager_id'] = $u_id;
        if (!is_null($type)) {
            $this->set('type', $type);
            $this->loadModel('Place');
            $places = $this->Place->find('all', array('conditions' => array('approved' => 'yes'), 'fields' => array('id', 'full_address')));
            $listings = array();
            foreach ($places as $place) {
                $listings[$place['Place']['id']] = $place['Place']['full_address'];
            }
            $this->set('places', $listings);
        } else {
            $this->set('type', null);
        }
    }

    public function slider() {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        $this->paginate = array(
            'conditions' => array("Content.type = 'slider'"),
            'limit' => 10,
            'order' => array('Content.order' => 'ASC')
        );
        $this->data = $this->paginate('Content');
        //$list = $this->Content->query("SELECT * from `contents` WHERE `manager_id` = ".$u_id." AND `type` = 'slider'");
        $this->set('slider', $this->data);
    }

    public function delete($id) {
        $u_id = $this->Auth->getManager();
        $this->Content->delete(array('id' => $id));
        $this->redirect('/managers/pages');
    }

    public function publish($id, $type=null) {
        $u_id = $this->Auth->getManager();
        $this->Content->id = $id;
        $this->Content->saveField('status', 'published');
        if (isset($type) && $type == 'slider') {
            $this->redirect('/managers/slider');
        }
        $this->redirect('/managers/pages');
    }

    public function unpublish($id, $type=null) {
        $u_id = $this->Auth->getManager();
        $this->Content->id = $id;
        $this->Content->saveField('status', 'unpublished');
        if (isset($type) && $type == 'slider') {
            $this->redirect('/managers/slider');
        }
        $this->redirect('/managers/pages');
    }

    public function listings($count=50, $order='title', $direction='desc') {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        $this->loadModel('Places');
        $this->paginate = array(
            'limit' => $count,
        );

        switch ($order) {
            case 'place':
                $orderBy = 'Places.id'; break;
            case 'title':
                $orderBy = 'Places.title'; break;
            case 'address':
                $orderBy = 'Places.full_address'; break;
            case 'first':
                $orderBy = 'User.first_name'; break;
            case 'last':
                $orderBy = 'User.last_name'; break;
            case 'phone':
                $orderBy = 'Profile.phone'; break;
            case 'email':
                $orderBy = 'User.email'; break;
            case 'updated':
                $orderBy = 'Places.modified'; break;
            case 'created':
                $orderBy = 'Places.created'; break;
            case 'status':
                $orderBy = 'Places.approved'; break;
            case 'complete':
                $orderBy = 'Places.completeness'; break;
            default :
                $orderBy = 'Places.title';
                break;
        }
        $orderBy .= ('asc' == strtolower($direction))
                ? ' asc'
                : ' desc';

        $this->set('order', $order);
        $this->set('direction', $direction);
        $this->set('limit', $count);
        $this->paginate = array(
            'Places' => array(
                'order' => $orderBy,
                'limit' => $count,
                'joins' => array(
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'inner',
                        'conditions' => array('User.id = Places.id_user'),
                    ),
                    array(
                        'table' => 'profiles',
                        'alias' => 'Profile',
                        'type' => 'left',
                        'conditions' => array('Profile.id_user = Places.id_user'),
                    ),
                ),
                'fields' => array('Places.*', 'User.first_name', 'User.last_name', 'User.email', 'User.id', 'Profile.phone'),
            )
        );


        $this->data = $this->paginate('Places');
        $this->set('listings', $this->data);
    }

    public function approve($id) {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Places');
        $this->loadModel('Users');                    
        $place = $this->Places->findByid($id);
        $user = $this->Users->findByid($place['Places']['id_user']);            
        $this->Mailer->listingActivated($user['Users']['email'], $id);

        //$message = 'Congratulations! Your profile is approved! Take a look:';
        //$this->Mailer->mailAndMessageToUser($place['Places']['id_user'], $message);

        $place['Places']['approved'] = "yes";
        $this->Places->save($place);
        $this->redirect('/managers/listings');
    }
    
    public function writeApproved($u_id = null){
        $m_id = $this->Auth->getManager();
        if(isset($u_id)){
            $this->set('u_id', $u_id);      
        }else{
            $this->set('u_id', ''); 
        }
        $this->loadModel('Places');
        $this->loadModel('Users');                    
        $place = $this->Places->findByid($u_id);
        $user = $this->Users->findByid($place['Places']['id_user']);
        $this->set('firstName', $user['Users']['first_name']);
        if(!empty($this->data)){
            
            $u_id = $this->data['Managers']['to'];
            $message = $this->data['Managers']['message'];
            $email = $user['Users']['email'];
            $place_id = $place['Places']['id'];
            $subject = 'approved';
            $this->Mailer->messageForApproved($email,$message,$place_id,$subject);
            $place['Places']['approved'] = "yes";
            $this->Places->save($place);
            $this->redirect('/managers/listings');
        }        
    }
    public function writeUnapproved($u_id = null){
        $m_id = $this->Auth->getManager();
        if(isset($u_id)){
            $this->set('u_id', $u_id);      
        }else{
            $this->set('u_id', ''); 
        }
        $this->loadModel('Places');
        $this->loadModel('Users');                    
        $place = $this->Places->findByid($u_id);
        $user = $this->Users->findByid($place['Places']['id_user']);            
        $this->set('firstName', $user['Users']['first_name']);
        if(!empty($this->data)){
            $u_id = $this->data['Managers']['to'];
            $message = $this->data['Managers']['message'];
            $email = $user['Users']['email'];
            $place_id = $place['Places']['id'];
            $subject = 'unapproved';
            $this->Mailer->messageForApproved($email,$message,$place_id,$subject);
            $place['Places']['approved'] = "no";
            $this->Places->save($place);
            $this->redirect('/managers/listings');
        }        
    }
    

    public function prohost() {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Places');
        //$ids=array_keys($this->data['places']);
        $zeros = array();
        $ones = array();
        foreach ($this->data['places'] as $key => $value) {
            if ($value == "1") {
                $ones[$key] = $value;
            } else {
                $zeros[$key] = $value;
            }
        }
        $this->Places->updateAll(
                array('Places.pro_host' => "1"), array('Places.id' => array_keys($ones))
        );
        $this->Places->updateAll(
                array('Places.pro_host' => "0"), array('Places.id' => array_keys($zeros))
        );
        $this->redirect('/managers/listings');
    }

    public function unapprove($id) {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Places');
        $place = $this->Places->findByid($id);
        $place['Places']['approved'] = "no";
        $this->Places->save($place);
        $this->redirect('/managers/listings');
    }

    public function editPage($id, $parentId = null) {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        //find page
        $this->data = $this->Content->findByid($id);
        $message_owner = $this->data['Content']['manager_id'];
        $canread = true;
        //security check
        /*if ($message_owner == $u_id) {
            $canread = true;
        } else {
            $canread = false;
        }*/

        //Check if manager has permissions
        if ($canread) {
            $this->set('pageId', $id);
            $this->set('u_id', $u_id);
            $this->set('page', $this->data['Content']['title']);
            if (is_null($parentId)) {
                $articles = $this->Content->findArticlesForPage($id);
                $this->set('allowAdd', false);
            } else {
                $articles = $this->Content->findArticlesForParent($parentId);
                $this->set('allowAdd', true);
                $this->set('parentId', $parentId);
            }
            $this->set('articles', $articles);
            $hasChild = array();
            foreach ($articles as $item) {
                if ($this->Content->hasChild($item['articles']['id'])) {
                    $hasChild[] = $item['articles']['id'];
                }
            }
            $this->set('hasChild', $hasChild);
        } else {
            $this->redirect('/managers/login');
        }
    }

    public function editArticle($id) {
        $this->layout = 'Admin_Page';
        $this->set('article_id', $id);
        $u_id = $this->Auth->getManager();
        //find page
        $article = $this->Content->findArticleById($id);
        $this->data = $this->Content->findByid($article[0]['articles']['page_id']);
        $message_owner = $this->data['Content']['manager_id'];
        $canread = true;
        //security check
        /*if ($message_owner == $u_id && $this->data['Content']['id'] == $article[0]['articles']['page_id']) {
            $canread = true;
        } else {
            $canread = false;
        }*/

        //read message
        if ($canread) {
            $this->set('id', $id);
            $this->set('u_id', $u_id);
            $this->set('page', $this->data['Content']['title']);
            $this->set('article', $article[0]);
            $this->data = $article[0];
        } else {
            $this->redirect('/managers/login');
        }
    }

    public function saveArticle() {
        if ($this->data) {
            $u_id = $this->Auth->getManager();
            //if ($u_id == $this->data['articles']['manager_id']) {
                $this->data['articles']['text'] = addslashes($this->data['articles']['text']);
                $this->data['articles']['name'] = addslashes($this->data['articles']['name']);
                if ($this->data['articles']['id'] == "new") {
                    $this->Content->addArticle($this->data['articles']);
                    $this->redirect('/managers/editPage/' . $this->data['articles']['page_id'] . '/' . $this->data['articles']['parent_id']);
                } else {
                    $this->Content->saveArticle($this->data['articles']);
                    $this->redirect('/managers/editArticle/' . $this->data['articles']['id']);
                }
            //}
        }
    }

    public function addArticle($pageId, $parentId) {
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        //find page
        $this->set('u_id', $u_id);
        $this->set('pageId', $pageId);
        $this->set('parentId', $parentId);
        $page = $this->Content->findByid($pageId);
        $this->set('page', $page['Content']['title']);
    }

    function clearCookie() {
        if ($this->Cookie->read('mango')) {
            $this->Cookie->delete('mango');
        }
        die('done <a href="/managers/login/">click here</a>');
    }

    public function writeTestimonial($placeId=null) {
        if (!empty($this->data)) {
            $recommendation = array();
            $recommendation['Recommendation']['content'] = $this->data['Manager']['content'];
            $recommendation['Recommendation']['by_user_id'] = $this->Auth->getManager();
            $recommendation['Recommendation']['user_id'] = $this->data['Manager']['forUser'];
            $recommendation['Recommendation']['date'] = date('Y-m-d H:i:s');
            $this->loadModel('Recommendation');
            if ($this->Recommendation->save($recommendation)) {
                //$this->Session->write('Rec.complete',1);
                $this->redirect('/managers/listings');
            } else {
                $this->set('error', $this->Recommendation->validationErrors);
            }
        }
        $this->loadModel('Place');
        $this->loadModel('User');
        $place = $this->Place->find('first', array('conditions' => array('Place.id' => $placeId)));
        $user = $this->User->find('first', array('conditions' => array('User.id' => $place['Place']['id_user'])));
        if (!$user) {
            $this->redirect('/managers/');
        }
        $u_id = $this->Auth->getManager();
        $this->layout = 'Admin_Page';
        $forUserName = $user['User']['first_name'] . ' ' . $user['User']['last_name'];
        $this->set('forUser', $user['User']['id']);
        //$this->set('u_id',$u_id);
        $this->set('forUserName', $forUserName);
        //$this->Session->write('Rec.forUserName',$forUserName);
    }

    public function listingOwner($u_id) {
        if (!$this->data) {
            if (!isset($u_id)) {
                $this->redirect('/managers/listings/');
            }
            $this->layout = 'Admin_Page';
            $this->loadModel('User');
            $user = $this->User->getFullUserInfo($u_id);
            if ($user) {
                $this->data = $user[0];
            } else {
                $this->Session->write('Note.error', "Incorrect user profile");
            }
        } else {
            $user = $this->data['User'];
            $profile = $this->data['Profile'];
            if ($profile['delete_photo'] == '1') {
                if ($profile['photo_path'] != 'default_avatar.png') {
                    $img = WWW_ROOT.SYSTEM_PATH_W.'profiles/' . $profile['photo_path'];
                    $thumb = WWW_ROOT.SYSTEM_PATH_W.'profiles/thumbs/' . $profile['photo_path'];
                    unlink($img);
                    unlink($thumb);
                    $profile['photo_path'] = 'default_avatar.png';
                }
            }
            $this->loadModel('User');
            $this->loadModel('Profile');
            $oldUser = $this->User->findByid($user['id']);
            $oldProfile = $this->Profile->findByid_user($user['id']);
            $oldUser['User']['first_name'] = $user['first_name'];
            $oldUser['User']['last_name'] = $user['last_name'];
            $oldUser['User']['email'] = $user['email'];
            $oldProfile['Profile']['phone'] = $profile['phone'];
            $oldProfile['Profile']['home_phone'] = $profile['home_phone'];
            $oldProfile['Profile']['photo_path'] = $profile['photo_path'];
            $oldProfile['Profile']['about_me'] = $profile['about_me'];
            //var_dump($oldUser);die;
            $this->User->save($oldUser);
            $this->Profile->save($oldProfile);
            $this->redirect('/managers/listings/');
        }
    }

    public function deleteListing($id) {
        if (!isset($id)) {
            $this->redirect('/managers/listings');
        }
        $this->loadModel('Place');
        $photos = $this->Place->deleteListing($id);
        //var_dump($photos);die;
        if (count($photos) > 0) {
            foreach ($photos as $photo) {
                $image = WWW_ROOT.SYSTEM_PATH_W.'places/' . $photo['place_photos']['location'];
                $thumb = WWW_ROOT.SYSTEM_PATH_W.'places/thumbs/' . $photo['place_photos']['location'];
                unlink($image);
                unlink($thumb);
            }
        }
        $this->redirect('/managers/listings/');
    }

    public function export_xls() {
        $this->Auth->getManager();
        $rows = $this->Manager->getAllListings();

        // build an array of headers
        $headers = array();
        foreach ($rows[0]['places'] as $key => $value) {
            $headers[] = ('id' != $key)
                    ? $key
                    : 'place_id';
        }
        foreach ($rows[0]['users'] as $key => $value) {
            $headers[] = $key;
        }
        foreach ($rows[0]['profiles'] as $key => $value) {
            $headers[] = $key;
        }

        // build an array for export
        $content = array();
        foreach ($rows as $row) {
            $temp = array();
            foreach($row['places'] as $field) {
                $temp[] = $field;
            }
            foreach($row['users'] as $field) {
                $temp[] = $field;
            }
            foreach($row['profiles'] as $field) {
                $temp[] = $field;
            }
            // clean the output for CSV
            $content[] = $temp;
        }

        $this->set('headers', $headers);
        $this->set('content', $content);
        $this->render('export_xls', 'export_xls');
    }
    
    public function reviews($id){
        $this->layout = 'Admin_Page';
        $u_id = $this->Auth->getManager();
        $this->loadModel('User');
        $this->loadModel('Place');
        $this->loadModel('Profile');
        $this->loadModel('Recommendation');
        $place = $this->Place->findByid_user($id);    
        $recommendations = $this->Recommendation->findAllByuser_id($id);
        $this->set('recommendations',$recommendations);        
    }
    
    public function unapprove_rec($id) {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Recommendation');
        $place = $this->Recommendation->findByid($id);
        //var_dump($place);die;
        $place['Recommendation']['approved'] = "unapproved";
        $this->Recommendation->save($place);
        $this->redirect('/managers/reviews/'.$place['Recommendation']['user_id']);
    }
    public function approve_rec($id) {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Recommendation');
        //$this->loadModel('Users');                    
        $place = $this->Recommendation->findByid($id);
        //$user = $this->Users->findByid($place['Places']['id_user']);            
        //$this->Mailer->listingActivated($user['Users']['email']);
        $place['Recommendation']['approved'] = "approved";
        $this->Recommendation->save($place);
        $this->redirect('/managers/reviews/'.$place['Recommendation']['user_id'] );
    }
    public function delete_rec($id,$user_id) {
        $u_id = $this->Auth->getManager();
        $this->loadModel('Recommendation');
        $this->Recommendation->delete(array('id' => $id));
        $this->redirect('/managers/reviews/'.$user_id);
    }
    
    public function edit_rec($id, $user_id){
        $this->layout = 'Admin_Page';
        
        $u_id = $this->Auth->getManager();
        $this->loadModel('Recommendation');
        $this->data = $this->Recommendation->findByid($id);
        $this->loadModel('User');
        $user = $this->User->findByid($this->data['Recommendation']['user_id']);
        $forUserName = $user['User']['first_name'].' '.$user['User']['last_name'];
        $this->set('forUserName',$forUserName);
        //set the ids to view
        $this->set('recId', $id);
        $this->set('recUserId', $user_id);
        
        //check for ajax occurrence
        $isAjax = $this->RequestHandler->isAjax();
        if($isAjax){
            //validation
            $_POST['name'] = trim($_POST['name']);
            $_POST['content'] = trim($_POST['content']);
            $_POST['good_bad'] = trim($_POST['good_bad']);
            if(
                !empty($_POST['name']) && !empty($_POST['content']) && 
                (!empty($_POST['good_bad']) || $_POST['good_bad'] === '0')
            ){
                $recommendation = $this->data;
                $recommendation['Recommendation']['name'] = $_POST['name'];
                $recommendation['Recommendation']['content'] = $_POST['content'];
                $recommendation['Recommendation']['good_bad'] = $_POST['good_bad'];

                $res = $this->Recommendation->save($recommendation);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }else{
                echo 'empty';
            }
            exit;
        }
    }
    
    public function writeReminder($u_id = null){
        $m_id = $this->Auth->getManager();
        $message = 'Your home profile creation not completed please don`t forget to complete it';
        if(isset($u_id)){
            $this->set('u_id', $u_id);        
        }else{
            $this->set('u_id', '');
        }
        $this->set('message', $message);
        if(!empty($this->data)){
            $message = $this->data['Managers']['message'];
            $u_id = $this->data['Managers']['to'];
            $this->Mailer->mailAndMessageToUser($u_id, $message);
            $this->redirect('/managers/reminderSent');
        }        
    }
    
    public function reminderSent(){
        
    }
    
    public function editUserInfo($u_id){
        $this->layout = 'Admin_Page';        
        $m_id = $this->Auth->getManager();
        $this->set('u_id', $u_id);
    }
    
    public function editPlace ($u_id){
        $this->layout = 'Admin_Page';        
        $m_id = $this->Auth->getManager();
        $this->loadModel('Place');
        $data = $this->Place->findByid_user($u_id);
        if(!empty($this->data)){
            $toSave = array_merge($data['Place'], $this->data['Place']);
            $this->Place->save($toSave);
            $this->redirect('/managers/editPlace/'.$toSave['id_user']);
        }
        $this->set('u_id', $u_id);
        $this->data = $data;
    }      
    public function editPet ($u_id){
        $this->layout = 'Admin_Page';        
        $m_id = $this->Auth->getManager();
        ////
        $this->loadModel('Pet');
        $profile_pic = $this->Pet->query('SELECT profiles.photo_path FROM profiles WHERE profiles.id_user = '.$u_id);
        $path = $profile_pic[0]['profiles']['photo_path'];
        $this->set('pic', $path);
        $this->set('owner_name', $this->Session->read('User.first_name').' '.$this->Session->read('User.last_name'));
        $this->set('u_id', $u_id);
        $all_dogs = $this->Pet->findAllByid_user($u_id);
        $this->set('all_dogs', $all_dogs);
        
        //////////////////////////////////////////////////
                $this->layout = 'Admin_Page';
        $m_id = $this->Auth->getManager();
        $this->loadModel('Pet');
        $this->set('u_id',$u_id);
        if(!empty($this->data)){
            $requireds = array(
                'pet_name'
            );
            try{
                foreach($this->data[Pet] as $key => $pet){
                    if(in_array($key,$requireds) && ($pet==='' || $pet === '0')){
                        throw new Exception('Please fill in all the required fields');
                    }
                }
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
                    $this->Session->write('Note.ok',"Pet info successfully saved");
                }
        }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
            }
            $pet_new = $this->Pet->findByid($petId);
               $this->set('petImage',$pet_new['Pet']['image']);        
        }else{
            $dog_id = $this->Session->Read('DogId');
           // $this->data->$this->findById($dog_id);
            $this->set('petImage',$this->data['Pet']['image']); 
        }
        
    }
    
    public function changePet($petId = null){
        if(is_null($petId) || !is_numeric($petId)){
            $this->redirect('/managers/listings');
        }
        $this->loadModel('Pet');
        
        $this->layout = 'Admin_Page';
        $userId = $this->Auth->getManager();
        
        $pet = $this->Pet->findByid($petId);
        if(!$pet){
            $this->redirect('/managers/listings');
        }
        $this->set('petId', $petId);
        
        if(!empty($this->data)){
            //save the data
            $requireds = array('pet_name');
           
            try{
                foreach($this->data['Pet'] as $key => $field){
                    if(in_array($key,$requireds) && ($field === '' || $field === '0')){
                        throw new Exception('Please fill in all the required fields');
                    }
                }
                //update overall progress bar
                /*if(!is_null($p_id)){
                    $cDetails = json_decode($userDb['Place']['c_details']);
                    if(!in_array('Pets', $cDetails)){
                            $cDetails[] = 'Pets';
                            $userDb['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $userDb['Place']['c_details'] = $cDetails;
                            $this->Place->save($userDb['Place']);
                    }
                }*/
                
                //process the image
                
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
                
                
                if(!$this->Session->check('Note.error')){
                    $this->Pet->save($this->data);
                    $petId = $this->Pet->id;
                    
                    //updating listing modified field
                    //$this->Dashboard->updateListing();
                    //clean up session dashboard entery
                    //$this->Dashboard->cleanUp();
                    $this->Session->write('Note.ok',"Pet info successfully saved");
                    //redirect to list
                    $this->redirect('/managers/editPet/'.$pet['Pet']['id_user']);
                }
           
            }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
            }
            
            $pet_new = $this->Pet->findByid($petId);
            $this->set('petImage',$pet_new['Pet']['image']);
        }else{
            $this->data = $pet;
            $this->set('petImage',$pet['Pet']['image']);
        }
    }
    
    public function addPet( $petId = null){
        $this->layout = 'Admin_Page';
        $m_id = $this->Auth->getManager();
        $this->loadModel('Pet');
        //$pet_check = $this->Pet->findByid_user($u_id);
        //$petId = $this->Pet->id;
        //$this->data['Pet']['id_user'] = $u_id;
       
        //$this->data = $this->Pet->findById($petId);
         // $this->set('u_id',$u_id);
        //var_dump($this->Pet->findByid($petId));die;
        
        if(!empty($this->data)){
            $requireds = array(
                'pet_name'
            );
            try{
                foreach($this->data['Pet'] as $key => $pet){
                    if(in_array($key,$requireds) && ($pet==='' || $pet === '0')){
                        throw new Exception('Please fill in all the required fields');
                    }
                }
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
                    $this->Session->write('Note.ok',"Pet info successfully saved");
                }
        }catch(Exception $error){
                $message = $error->getMessage();
                $this->Session->write('Note.error',$message);
            }
            $pet_new = $this->Pet->findByid($petId);
               $this->set('petImage',$pet_new['Pet']['image']);        
        }else{
            $dog_id = $this->Session->Read('DogId');
           // $this->data->$this->findById($dog_id);
            $this->set('petImage',$this->data['Pet']['image']); 
        }
    }
    public function editPrices ($u_id){
        $this->layout = 'Admin_Page';        
        $m_id = $this->Auth->getManager();
        $this->loadModel('PlaceTerm');
        $this->loadModel('Place');
        $place = $this->Place->findByid_user($u_id);
        $data = $this->PlaceTerm->findByid_place($place['Place']['id']);
        //var_dump($data);die;
        if(!empty($this->data)){
            $toSave = array_merge($data['PlaceTerm'], $this->data['Managers']);
            $this->PlaceTerm->save($toSave);
            $this->redirect('/managers/editPrices/'.$toSave['id_user']);
        }
        $this->set('u_id', $u_id);
        $this->data['Managers'] = $data['PlaceTerm'];
    }
    
    public function editVerify ($u_id){
        $this->layout = 'Admin_Page';        
        $m_id = $this->Auth->getManager();
        $this->loadModel('Profile');
        $data = $this->Profile->findByid_user($u_id);
        if(!empty($this->data)){            
            $toSave = array_merge($data['Profile'], $this->data['Profile']);
            $micro = time();
            $toSave['facebook'] = ($toSave['fb'])? (($toSave['facebook'])? $toSave['facebook'] : $micro) : NULL;
            $toSave['twitter'] = ($toSave['twit'])? (($toSave['twitter'])? $toSave['twitter'] : $micro) : NULL;
            $toSave['linkedin'] = ($toSave['lnin'])? (($toSave['linkedin'])? $toSave['linkedin'] : $micro) : NULL;            
            //var_dump($toSave);die;
            $this->Profile->save($toSave);
            $this->redirect('/managers/editVerify/'.$toSave['id_user']);
        }
        $facebook = ($data['Profile']['facebook'])?'checked':'';
        $twitter = ($data['Profile']['twitter'])?'checked':'';
        $linkedin = ($data['Profile']['linkedin'])?'checked':'';
        $this->set('u_id', $u_id);
        $this->set('facebook', $facebook);
        $this->set('twitter', $twitter);
        $this->set('linkedin', $linkedin);
        $this->data['Profile'] = $data['Profile'];
    }
        
}
?>
