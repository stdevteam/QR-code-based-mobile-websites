<?php
/**
 * Places handler conteroller
 * @property AuthComponent $Auth Authentification handling component
 */
App::import('Sanitize');
class PlacesController extends AppController{

    public $name = 'Places';
    public $components = array('Email','Image','Auth','Dashboard','Mailer','Check','Geocoder');

    //the main view of the place, this available to the public,
    public function index($id = null, $preview = null){
        if(!isset($this->params['id'])){
            $this->redirect('/');
        }
        $this->layout = 'User_Master_Page';		
        $includes = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>'.PHP_EOL.
                                '<script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>'.PHP_EOL.
                                '<script type="text/javascript" src="/js/date_check.js"></script>'.PHP_EOL.
                                '<link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />'.PHP_EOL;
        if($this->Session->read('error')!=''){
            $error =$this->Session->read('error');
            $includes .= '<script type="text/javascript">alert(\''.$error.'\');</script>';
            $this->Session->delete('error');
        }
        
        $isLoggedIn = (bool)$this->Auth->checkUser();
        $this->set('isLoggedIn', $isLoggedIn);
        $userDb = ($isLoggedIn) 
                ? $this->Dashboard->getData()
                : array('Pet' => array());
        if(!isset($userDb['Pet']) || count($userDb['Pet'])<1 ){
            $countDog = 'false';
        }else{
            $countDog = 'true';
        }
        $this->set('userDogsCount',count($userDb['Pet']));
        $this->set('countDog',$countDog);
        $selected = count($userDb['Pet']);
        //var_dump($this->data['Booking']['dogs']);die;

        $this->set('selected', $selected);
        $this->set('includes', $includes);
        $this->set('id', $this->params['id']);
        $place = $this->Place->findByid($this->params['id']);
        if(!$place){
            $this->redirect('/');
        }
        $show = false;

        if(array_key_exists('isPreview', $_POST)){
            if($_POST['isPreview'] == 'yes'){
                    $show = true;
            }
        }

        if($preview != null){
            $show = true;
        }

        if($place['Place']['approved'] == 'yes'){
            $show = true;
        }

        if(!$show){
            $this->redirect('/');
        }
        $this->set('place', $place);
        
        //process photos
        $photo_set = $this->Place->query('
            SELECT location 
            FROM place_photos 
            WHERE place_photos.id_place = '.$this->params['id'].' 
            ORDER BY place_photos.primary DESC'
        );
        $this->set('photo_set', $photo_set);
        
        //process terms
        $place_terms = $this->Place->query('SELECT * FROM place_terms WHERE place_terms.id_place = '.$this->params['id']);
        $this->set('place_terms', $place_terms);
        
        //process user picture
        $user_pic = $this->Place->query('SELECT * FROM profiles WHERE profiles.id_user = '.$place['User']['id']);
        $this->set('user_pic', $user_pic);
        //process the recommendations
        $user_rec = $this->Place->query('
            SELECT r.*,p.photo_path,u.first_name,u.last_name 
            FROM recommendations as r 
            LEFT JOIN `users` as u ON r.`by_user_id` = u.`id` 
            LEFT JOIN `profiles` as p ON r.`by_user_id` = p.`id_user`  
            WHERE r.user_id = '.$place['User']['id'].' 
                AND approved = "approved"'
        );
        //var_dump($user_rec);die;
        if(!empty($user_rec)){
            $this->set('user_rec', $user_rec);
        }else{
            $this->set('user_rec', null);
        }
        //show services
        //var_dump($place['Place']['id_user']);die;
        $this->loadModel('Service');
        $pu_id = $place['Place']['id_user'];
        $service = $this->Service->findByuser_id($pu_id);
        //var_dump($service);die;
        $this->set('service', $service);
        
        //process pets
        $this->loadModel('Pet');
        $pets = $this->Pet->find('all',array('conditions' => array('id_user' => $place['Place']['id_user'] ))); //the pets owned by the owner listing, not your pets!
        $this->set('pets',$pets);
        
        //process the availability
        $this->loadModel('Booking');
        $availability = $this->Booking->find('all',array('conditions' => array('id_place' => $this->params['id'])));
        $dates = array();
        if($availability){
            foreach($availability as $item){
                $in = $item['Booking']['drop'];
                $out = $item['Booking']['pick'];
                $day = array('start' => $in, 'end' => $out);
                $dates[] = $day;
            }
        }
        $this->set('dates',  json_encode($dates));
        if (!Configure::read('CHECKOUT')) {
            return $this->render('index_old');
        }
        
    }		

    public function redirector(){
        $urlRaw = reset($this->params['pass']);
        $url = base64_decode($urlRaw);
        if($urlRaw && is_string($urlRaw) && $url && $url !== false){
            $userId = $this->Auth->getUserOrRedirect($url);
            //if we have reached here than the user is logged in
            $this->redirect('/profiles/view');
        }else{
            $this->redirect('/profiles/view');
        }
    }
    
    function SingleMap($id = null){
        $this->layout = 'blank_for_test';
        $place = $this->Place->findByid($id);
        $ll = $place['Place']['ll'];
        $this->set('ll', $ll);			
    }

    function HowItWorks($id = null){
        $this->layout = 'blank_for_test';
        
        $this->_setMetaData(
            'Dog Vacay allows you to find a real home to board your dog. It\'s better than a kennel 
             where Spot will be stuck in a cage all day and exposed to diseases.', 
            'how it works, how DogVacay works'
        );
    }
    
    function BenefitsAndSafety($id = null){
        $this->layout = 'blank_for_test';
        
        $this->_setMetaData(
            'Most dog owners love their dog too much to keep them crated for hours on end, so why 
             should that change when you are out of town? DogVacay allows you to find a home 
             environment for your puppy, where they can get individual attention and roam free', 
            'benefits, safety'
        );
    }
    
    function WhyHost($id = null){
        $this->layout = 'blank_for_test';
        
        //set the meta
        $this->_setMetaData(
            'Make money doing what you love - Welcome other dogs into your home to make extra cash.
             Do what you already do - Many home boarders keep the same walking and feeding schedule they already have with their own dogs ', 
            'why host on DogVacay, make money with dog boarding'
        );
    }
    
    function PlaceTerms(){
        $this->layout = 'User_Master_Page';
        $u_id = $this->Auth->getUser();
        $p_id = $this->Session->read('Place.id');
    }

    function CalendarView($id = null){
            $this->layout = 'blank_for_test';
            echo 'single map view';
    }

    //function to handle the search.ctp view
    public function search($default = null){
        $params = $this->Session->read('Search.params');
        if (!is_array($params) || !extract($params)) {
            // set defaults
            $address = 'Los Angeles, CA';
            $address_lat = 34.0522342;
            $address_lng = -118.2436859;
            $orderBy = 'proximity';

            // change defaults on IP lookup
            $ip = $this->Geocoder->ip_lookup($_SERVER['REMOTE_ADDR']);
            if (!empty($ip['geoplugin_city']) && !empty($ip['geoplugin_region'])) {
                $address = $ip['geoplugin_city'] . ', ' . $ip['geoplugin_region'];
                $address_lat = $ip['geoplugin_latitude'];
                $address_lng = $ip['geoplugin_longitude'];
            }
        }

        // validate inputs
        if (array_key_exists('places_sortby', $_REQUEST)) {
            $orderBy = trim($_REQUEST['places_sortby']);
        }
        if (array_key_exists('search', $_REQUEST)) {
            $geo = $this->Geocoder->getLatLng($_REQUEST['search']);
            if (is_numeric($geo['lat']) && is_numeric($geo['lng'])) {
                $address = $_REQUEST['search'];
                $address_lat = $geo['lat'];
                $address_lng = $geo['lng'];
            }
        }
        // preserve the old values
        $this->Session->write('Search.params', array(
                'address' => $address,
                'address_lat' => $address_lat,
                'address_lng' => $address_lng,
                'orderBy' => $orderBy,
                ));

        // set the search parameters and execute
        $this->paginate = array(
                'limit' => 20,
                'order' => array('Message.created' => 'desc'),
                'conditions' => array('data' => array(
                        'search' => $address,
                        'places_sortby' => $orderBy,
                        'address_lat' => $address_lat,
                        'address_lng' => $address_lng,
                        )),
                );
        $places = $this->paginate('Place');

        // set up parameters for showing Google Maps
        $json = array();
        foreach($places as $place){
            if($place['places']['lat'] && $place['places']['lng']){
                $json[] = array(
                    'id' => $place['places']['pid'],
                    'lat' => $place['places']['lat'],
                    'lng' => $place['places']['lng'],
                    'url' => FULL_BASE_URL.'/places/'.$place['places']['pid'],
                );
            }
        }

        $this->set('json', json_encode($json));
        $this->set('count', count($places));
        $this->set('search_results', $places);
        $this->set('search_term',$address);
        $this->set('orderBy', $orderBy);

        $this->layout = 'User_Master_Page';
    }

    //function to handle the list_place.ctp view
    public function ListPlace(){
        $u_id = $this->Auth->checkUser();
        if($u_id == null){
            $this->Session->write('Redirect.url','/places/ListPlace');
            $this->redirect('/users/add/');
        }
        $userDb = $this->Dashboard->getData();
        if(!is_null($userDb['Place'])){
            $this->redirect('/places/CreationOverview/');
        }
        $this->layout = 'User_Master_Page';
        if(!empty($this->data)){
            if(!isset($this->data['Place']['full_address']) || $this->data['Place']['full_address'] == ''){
                //echo '<SCRIPT>alert(\'Please input your full address to continue\');</SCRIPT>';
                $this->Session->write('Note.error', "Please input your full address to continue");
                //$this->redirect('/places/ListPlace');
            }elseif(
                    !isset($this->data['Place']['zip']) || 
                    (!is_string($this->data['Place']['zip']) && $this->data['Place']['zip'] <= 0)
            ){
                
                $this->Session->write('Note.error', "Please input correct address to continue");
                $this->redirect('/places/ListPlace');
            }else{

                $this->data['Place']['id_user'] = $u_id;
                $ll = $this->data['Place']['ll'];
                unset($this->data['Place']['ll']);
                $ll = str_ireplace('(', '', $ll);
                $ll = str_ireplace(')', '', $ll);
                $ll = explode(',', $ll);
                $this->data['Place']['lat'] = $ll[0];
                $this->data['Place']['lng'] = $ll[1];
                $cDetails = array();                
                $cDetails = json_encode($cDetails);
                $this->data['Place']['c_details'] = $cDetails;
                $this->Place->save($this->data);
                $this->loadModel('User');
                $user = $this->User->findByid($u_id);

                $email = $user['User']['email'];

                //send approval notification
              //  $this->Mailer->startListingForUser($email,$this->Place->id);
                $this->Mailer->adminListingStarted($this->Place->id,$email);
                //send approval notification

                $this->Session->write('Place.id', $this->Place->id);

                //clean up session dashboard entery
                $this->Dashboard->cleanUp();

                $this->data = $this->Place->find('first', array('id' => $this->Place->id, 'id_user' => $u_id));
                $this->redirect('/places/PlaceDetails/');
            }
        }
    }
    
    //function to handle the place_details.ctp view
    public function PlaceDetails(){
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';

        $p_id = '';
        $u_id = $this->Auth->getUser();
        $userDb = $this->Dashboard->getData();
        if(is_null($userDb['Place'])){
            $this->redirect('/places/ListPlace/');
        }
        $p_id = $userDb['Place']['id'];
        if(!empty($this->data)){
            if(
                $this->data['Place']['title'] == '' ||
                $this->data['Place']['description'] == '' ||
                $this->data['Place']['property_type'] == '' ||
                $this->data['Place']['bedrooms'] == '' ||
                $this->data['Place']['dog_care_experience'] == '' ||
                $this->data['Place']['yard'] == '' ||
                $this->data['Place']['nearby'] == '' ||
                $this->data['Place']['pet_responsible'] == ''
            ){
                //highlight
                $title = $this->data['Place']['title'];
                $description = $this->data['Place']['description'];
                $property_type = $this->data['Place']['property_type'];
                $bedrooms = $this->data['Place']['bedrooms'];
                $dog_care_experience = $this->data['Place']['dog_care_experience'];
                $yard = $this->data['Place']['yard'];
                $nearby = $this->data['Place']['nearby'];
                $pet_responsible = $this->data['Place']['pet_responsible'];

                $this->set('title',$title);
                $this->set('description',$description);
                $this->set('property_type',$property_type);
                $this->set('bedrooms',$bedrooms);
                $this->set('dog_care_experience',$dog_care_experience);
                $this->set('yard',$yard);
                $this->set('nearby',$nearby);
                $this->set('pet_responsible',$pet_responsible);
                $this->Session->write('Note.error', "Please fill in all required fields");
            }else{  
                $this->data['Place']['id_user'] = $u_id;
                $this->data['Place']['id'] = $p_id;
                $this->data['Place']['completeness'] = '20';
                $cDetails = json_encode(array('placeDetails'));
                $this->data['Place']['c_details'] = $cDetails;
                $this->Place->save($this->data);
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                $this->data = $this->Place->find('first', array('id' => $this->Place->id, 'id_user' => $u_id));
                $this->redirect('/place_photos/Upload');
            }
        }
    }

    //function to handle the creation_overview.ctp view
    public function CreationOverview(){
            //$this->layout = 'User_Master_Page';
            $this->layout = 'User_Account_Page';
            $u_id = '';
            $p_id = '';
            $u_id = $this->Auth->getUser();
            //$p_id = $this->Session->read('Place.id');
            $p_id = $this->Dashboard->getListingId();
            if(empty($this->data)){
                    $place = $this->Place->findByid($p_id);
                    $this->data = $place;
            }else{ 
                    $place = $this->Place->findByid($p_id);
                    $cDetails = json_decode($place['Place']['c_details']);
                    if(!in_array('placeDetails', $cDetails)){
                            $cDetails[] = 'placeDetails';
                            $this->data['Place']['completeness'] = count($cDetails)*20;
                            $cDetails = json_encode($cDetails);
                            $this->data['Place']['c_details'] = $cDetails;
                    }                                   
                    $this->Place->save($this->data);
                   
                    //clean up session dashboard entery
                    $this->Dashboard->cleanUp();
                    
                    //$this->redirect('/place/CreationOverview/');
                    $this->redirect('/place_photos/Upload');
            }
            $p_id = $this->Dashboard->getListingId();
            //$this->loadModel('Place');
            //$place = $this->Place->findByid($p_id);
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
                    $this->set('path', false);
            }
            $this->set('p_id', $p_id);
            
    }

    function PlaceAvailability(){
            //$this->layout = 'User_Master_Page';
            $this->layout = 'User_Account_Page';

            $includes = '<link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.8.16.custom.css" /> '.
             '<link rel="stylesheet" type="text/css" href="/css/fullcalendar.css" /> '.
             '<link rel="stylesheet" type="text/css" href="/css/fullcalendar.print.css" media="print" /> '.
             '<script type="text/javascript" src="/js/jquery-1.5.2.min.js"></script> '.
             '<script type="text/javascript" src="/js/jquery-ui-1.8.16.custom.min.js"></script> '.
             '<script type="text/javascript" src="/js/fullcalendar.min.js"></script> ';

            $this->set('includes', $includes);
            $u_id = $this->Auth->getUser();
            $p_id = $this->Session->read('Place.id');
            $this->data = $this->Place->findByid($p_id);
            $calendar = $this->data['Place']['availability'];
            $this->set('v_title', $this->data['Place']['title']);
            $this->set('v_property_id', $p_id);
            $this->set('events', $calendar);
            if($this->Session->check('Place.id')){
                    $p_id = $this->Session->read('Place.id');
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
                            $this->set('path', '/img/home.png');
                    }
                    $this->set('p_id', $p_id);
            }else{
                    $this->set('p_id', '#');
            }
    }

    function ManageListings(){
            $this->layout = 'User_Master_Page';
            $u_id = $this->Auth->getUser();
            $places = $this->Place->findAllByid_user($u_id);
            for($i=0;$i<count($places);$i++){
                    $temp = $places[$i];
                    $placeid = $temp['Place']['id'];
                    $places[$i]['images'] = $this->Place->query('SELECT location FROM place_photos WHERE place_photos.id_user = '.$u_id.' AND place_photos.id_place = '.$placeid.' LIMIT 1');
                    if(count($places[$i]['images'])>0){
                            $pic = $places[$i]['images'][0]['place_photos']['location'];
                    }else{
                            $pic = 'home.png';
                    }
                    $places[$i]['images'] = $pic;
            }
            $this->set('places', $places);
    }

    function delete($id = null){
            $u_id = $this->Auth->getUser();
            //find this place
            $place = $this->Place->findByid($id);
            //check if the user owns the place 
            if($place['Place']['id_user'] == $u_id){
                    //delete the place
                    $this->Place->delete($id);
                    
                    //clean up session dashboard entery
                    $this->Dashboard->cleanUp();
            }
            $this->redirect('/places/ManageListings');
    }
    //depricated
    function LaunchEdit($id = null){
        $u_id = $this->Auth->getUser();
        $this->Session->write('Place.id', $id);
        $this->redirect('/places/CreationOverview');
    }

    function LaunchCalendar($id = null){
        $u_id = $this->Auth->getUser();
        $this->Session->write('Place.id', $id);
        $this->redirect('/places/PlaceAvailability');
    }

    function busyDays(){
    $day = 1000*3600*24;
    if(isset($_POST['action'])){
        if($_POST['action'] == "clearAll"){
            if($this->Auth->getUser() && $this->Session->check('Place.id')){
                $u_id = $this->Auth->getUser();
                $p_id = $this->Session->read('Place.id');
                $place = $this->Place->findByid($p_id);
                $place['Place']['availability'] = json_encode(array());
                $this->Place->save($place);
                
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                
                echo json_encode(array());
                die();
            }
        }else{
                $action = $_POST['action'];
                $start = $_POST['startDate'];
                $end = $_POST['endDate'];
                if($this->Auth->getUser() && $this->Session->check('Place.id')){
                    $u_id = $this->Auth->getUser();
                    $p_id = $this->Session->read('Place.id');
                    $place = $this->Place->findByid($p_id);
                    $calendar = json_decode($place['Place']['availability'],true);
                    if($action == 'block'){
                    if(count($calendar) > 0){
                        $alreadyCreated = false;
                        foreach($calendar as $key => $range){
                            if($start >= $range['sd'] && $start <= $range['ed']){
                                $oneDate = $this->createStd($range['sd'], $end);
                                $calendar[] = $oneDate;
                                unset($calendar[$key]);
                                $start = $range['sd'];
                                end($calendar);
                                $key = key($calendar);
                                $alreadyCreated = true;
                            }
                            if($end >= $range['sd'] && $end <= $range['ed']){
                                $oneDate = $this->createStd($start, $range['ed']);
                                $calendar[] = $oneDate;
                                unset($calendar[$key]);
                                $end = $range['ed'];
                                end($calendar);
                                $key = key($calendar);
                                $alreadyCreated = true;
                            }
                            if($start <= $range['sd'] && $end >= $range['ed'] ){
                                $oneDate = $this->createStd($start, $end);
                                if(isset($allKey)){
                                    unset($calendar[$allKey]);
                                }
                                $calendar[] = $oneDate;
                                unset($calendar[$key]);
                                $alreadyCreated = true;
                                end($calendar);
                                $allKey = key($calendar);
                            }
                            if($start >= $range['sd'] && $end <= $range['ed'] ){
                                $alreadyCreated = true;
                            }
                        }
                        if(!$alreadyCreated){
                            $oneDate = $this->createStd($start, $end);
                            $calendar[] = $oneDate;
                        }
                    }else{
                        $oneDate = $this->createStd($start, $end);
                        $calendar[] = $oneDate;
                    }
                }elseif($action == 'unblock'){
                    if(count($calendar) > 0){
                        foreach($calendar as $key => $range){ //var_dump($start-$day,$end+$day,$range);die;
                            if($start - $day >= $range['sd'] && $end + $day <= $range['ed'] ){
                                if($range['sd'] <= $start - $day){
                                    $oneDate = $this->createStd($range['sd'], $start - $day);
                                    $calendar[] = $oneDate;
                                }
                                if($end + $day <= $range['ed']){
                                    $oneDate = $this->createStd($end + $day, $range['ed']);
                                    $calendar[] = $oneDate;
                                }
                                unset($calendar[$key]);
                            }
                            elseif($start > $range['sd'] && $start - $day < $range['ed'] ){
                                if($range['sd'] <= $start - $day){
                                    $oneDate = $this->createStd($range['sd'], $start - $day);
                                    $calendar[] = $oneDate;
                                }
                                unset($calendar[$key]);
                                $start = $range['sd'];
                            }
                            elseif($end + $day > $range['sd'] && $end < $range['ed']){ //var_dump($start,$end,$calendar);die;
                                if($end + $day <= $range['ed']){
                                    $oneDate = $this->createStd($end + $day, $range['ed']);
                                    $calendar[] = $oneDate;
                                }
                                unset($calendar[$key]);
                                $end = $range['ed'];
                                $alreadyCreated = true;
                            }
                            elseif($start <= $range['sd'] && $end >= $range['ed'] ){
                                unset($calendar[$key]);
                            }
                            /*if($start <= $range['sd'] && $end <= $range['ed'] ){var_dump($start,$end,$calendar);die("hesa");
                                if($end + 1000*3600*24 <= $range['ed'] ){
                                $oneDate = $this->createStd($end + 1000*3600*24, $range['ed']);
                                $calendar[] = $oneDate;
                                }
                                unset($calendar[$key]);
                            }
                            if($start >= $range['sd'] && $end >= $range['ed'] ){
                                if($range['sd'] <= $start - 1000*3600*24){
                                $oneDate = $this->createStd($range['sd'], $start - 1000*3600*24);
                                $calendar[] = $oneDate;
                                }
                                unset($calendar[$key]);
                            }*/
                        }

                    }
                }
                $place['Place']['availability'] = json_encode($calendar);
                $cDetails = json_decode($place['Place']['c_details']);
                if(!in_array('Calendar', $cDetails)){
                                    $cDetails[] = 'Calendar';
                                    $place['Place']['completeness'] = count($cDetails)*20;
                                    $cDetails = json_encode($cDetails);
                                    $place['Place']['c_details'] = $cDetails;
                                }
                $this->Place->save($place);
                
                //clean up session dashboard entery
                $this->Dashboard->cleanUp();
                
                echo json_encode($calendar);
                die();
                }else{
                    echo "please login";
                    die();
                }
    }
            }else{
                echo "error";
                die();
            }
}


    public function unblockAll(){
            $p_id = $this->Session->read('Place.id');
            //update overall progress bar
            if($this->Session->check('Place.id')){
                    $place = $this->Place->findByid($p_id);
                    $cDetails = json_decode($place['Place']['c_details']);
                        if(!in_array('Calendar', $cDetails)){
                                $cDetails[] = 'Calendar';
                                $place['Place']['completeness'] = count($cDetails)*20;  // it is working perfectly , this is not right way
                                //$place['Place']['completeness'] = ($place['Place']['completeness']+20); //somehow the * operator was not working :s
                                $cDetails = json_encode($cDetails);
                                $place['Place']['c_details'] = $cDetails;
                                $this->Place->save($place);
                                
                                //clean up session dashboard entery
                                $this->Dashboard->cleanUp();
                           }

            }
            if(isset($this->data['Place']['location'])){
                $this->redirect('/places/PlaceAvailability/');
            }else{
                $this->redirect('/place_terms/Define/');
            }
    }

    public function createStd($start,$end){
        $oneDate = array();
        $oneDate['sd'] = $start;
        $oneDate['ed'] = $end;
        return $oneDate;
}

    public function ApproveListing($id = null){
            $this->data = $this->Place->findByid($id);
            $this->data['Place']['approved'] = 'yes';
            $this->Place->save($this->data);
            
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();                
            $this->redirect('/places/'.$id);
    }

    public function afterlisting(){
        $u_id = $this->Auth->getUser();
        $this->layout = 'User_Master_Page';
        $userDb = $this->Dashboard->getData();
        $p_id = $userDb['Place']['id'];
        $place = $this->Place->findByid($p_id);
        $cDetails = json_decode($place['Place']['c_details']);
        if(!in_array('Requested', $cDetails)){
            $requested = false;
            $cDetails[] = 'Requested';            
            $cDetails = json_encode($cDetails);
            $place['Place']['c_details'] = $cDetails;
            $this->Place->save($place);                        
            $this->loadModel('User');
            $user = $this->User->findByid($u_id);
            $email = $user['User']['email'];
            //send approval notification
            $this->Mailer->adminApproveListing($p_id,$email);
            $message = 'Thank you for completing your profile at Dog Vacay!';
            $this->Mailer->mailAndMessageToUser($u_id, $message);
            //send approval notification
            $this->Dashboard->cleanUp();             
        }else{
            $requested = true;            
        }
        $this->set('requested', $requested);       
    }

    public function correctLatLng(){
        $places = $this->Place->find('all');
        foreach($places as $key => $place){
            $ll = $place['Place']['ll'];
            $ll = str_ireplace('(', '', $ll);
            $ll = str_ireplace(')', '', $ll);
            $ll = explode(',', $ll);
            if(count($ll) == 2){
                $place['Place']['lat'] = $ll[0];
                $place['Place']['lng'] = $ll[1];
            }else{
                $place['Place']['lat'] = 0;
                $place['Place']['lng'] = 0;
            }
            $this->Place->save($place['Place']);
            
            //clean up session dashboard entery
            $this->Dashboard->cleanUp();
            var_dump($place);
        }
        //$this->Place->save($places);
    }

    public function myHome(){       
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $userDb = $this->Dashboard->getData();
        $this->set('userDb',$userDb);
        $u_id = $this->Auth->getUser();        
        /*$place = $this->Place->findByid_user($u_id);        
        $this->set('place', $place);
        $photo_set = $this->Place->primaryPhotoByUserId($u_id);
        $this->set('photo_set', $photo_set);*/
    }
    
    
    /**
     * Sets the meta description and keywords to be outputted in layout file
     * @param string $description The description to send to layout
     * @param string $keywords [optional] The keywords to send to layout, defaults to null
     */
    protected function _setMetaData($description, $keywords = null){
        if(!empty($description) && trim($description) != ''){
            $this->set('metaDescription', $description);
        }
        
        if(!is_null($keywords) && !empty($keywords) && trim($keywords) != ''){
            $this->set('metaKeywords', $keywords);
        }
    }
    
    public function test($text){
        //$this->Check->checker($text);
    }
    
    function reminder(){
        $places = $this->Place->forRemind();
        $message = 'Your home profile creation not completed please don`t forget to complete it';
        //var_dump($places);die;
        if($places){
            foreach($places as $place){
                $u_id = $place['places']['id_user'];
                $this->Mailer->mailAndMessageToUser($u_id, $message);
                $place['places']['reminded'] = '1';
                $this->Place->create();
                $this->Place->save($place['places']);
            }
        }
    }
    
    
}
?>
