<?php
/**
 * Booking process handler controller
 * @property MailerComponent $Mailer The main mailer component 
 * @property AuthComponent $Auth  
 * @property AuthorizeNetComponent $AuthorizeNet Handler for Authorize.net
 * @property PaypalComponent $Paypal Handler for Paypal.com
 * @property DateComponent $Date Includes several helper medthods to play with dates
 * @property DashboardComponent $Dashboard Dashboard Component
 * @property SocialsComponent $Socials Socials info Component
 */
class BookingsController extends AppController{
    public $components = array(
        'Email','Auth','Date', 'Dashboard', 
        'AuthorizeNet', 'Mailer','Socials'//, 'Paypal',
    );
    public $name = 'Bookings';
    /**
     * The fee Dog Vacay charges for checkout in percents
     * @var int
     */
    protected $_dogVacayFee = 15;
    /**
     * The additional fee Dog Vacay charges for checkout fixed amount
     * @var int
     */
    protected $_additionalFee = 0;
    
    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function index($id = null){
            $this->layout = 'User_Master_Page';
            $u_id = $this->Auth->getUser();

            if($id!=''){
                    $this->Session->write('Place.id', $id);
                    $place = $this->Booking->query('SELECT * FROM places WHERE places.id='.$id);
                    $picture = $this->Booking->query('SELECT * FROM place_photos WHERE place_photos.id_place ='.$id);
                    $terms = $this->Booking->query('SELECT * FROM place_terms WHERE place_terms.id_place ='.$id);
                    if(array_key_exists('drop', $_POST)){
                            $drop = $_POST['drop'];
                    }
                    if(array_key_exists('pick', $_POST)){
                            $pick = $_POST['pick'];
                    }
                    if(array_key_exists('dogs', $_POST)){
                            $dogs = $_POST['dogs'];
                            $this->Session->write('Payment.dogs_quantity', $dogs);
                    }
                    $path = '';
                    if(!$picture){
                            $path = '/img/no_picture.jpg';
                    }else{
                            $path = $picture[0]['place_photos']['location'];
                    }
                    $title = $place[0]['places']['title'];
                    $description = $place[0]['places']['description'];
                    $pick_date =  strtotime($pick);
                    $drop_date =  strtotime($drop);
                    $this->Session->write('Payment.drop', $drop);
                    $this->Session->write('Payment.pick', $pick);
                    $diff =  (($pick_date - $drop_date)/86400);
                    $additional_charges = $terms[0]['place_terms']['large_dogs'] + $terms[0]['place_terms']['puppies'] + $terms[0]['place_terms']['bath'] +$terms[0]['place_terms']['security_deposit']+$terms[0]['place_terms']['cleaning_fees'];
                    //here changed to 15% 
                    $total_payout = ((($terms[0]['place_terms']['nigthly_rates'] * $diff)+ $additional_charges)+15);
                    $this->Session->write('Payment.total', $total_payout);
                    $cancel = '';
                    if($terms[0]['place_terms']['cancellation_policy'] == 'Flexible'){
                            $cancel = 'Flexible, Full refund 1 day prior to start';
                    }
                    if($terms[0]['place_terms']['cancellation_policy'] == 'Moderate'){
                            $cancel = 'Moderate: Full refund 5 days prior to start';
                    }
                    if($terms[0]['place_terms']['cancellation_policy'] == 'Strict'){
                            $cancel = 'Strict: 50% refund 1 week prior to start';
                    }
                    $this->Session->write('Payment.cancellation', $cancel);
                    $this->set('id_place', $id);
                    $this->set('diff', $diff);
                    $this->set('terms', $terms);
                    $this->set('path', $path);
                    $this->set('title', $title);
                    $this->set('sec_dep', $terms[0]['place_terms']['security_deposit']);
                    $this->set('drop', $drop);
                    $this->set('pick', $pick);
                    $this->set('dogs', $dogs);
                    $this->set('description', $description);
                    $this->set('additional_charges', $additional_charges);
            }

            if(!empty($this->data)){
                    $valid = true;
                    if($this->data['Booking']['cardholder_name'] == '') $valid = false;
                    if($this->data['Booking']['street_address'] == 'street address' || $this->data['Booking']['street_address'] == '') $valid = false;
                    if($this->data['Booking']['city'] == '' || $this->data['Booking']['city'] == 'City') $valid = false;
                    if($this->data['Booking']['state'] == '' || $this->data['Booking']['state'] == 'state') $valid = false;
                    if($this->data['Booking']['postal_code'] == '' || $this->data['Booking']['postal_code'] == 'Postal Code') $valid = false;
                    if($this->data['Booking']['card_num'] == '' || $this->data['Booking']['card_num'] == 'Card Number') $valid = false;
                    if($this->data['Booking']['security_code'] == '' || $this->data['Booking']['security_code'] == 'Sec Code') $valid = false;
                    if($valid){
                            $this->Booking->save($this->data);
                            $this->Session->write('Payment.cc', $this->data['Booking']['card_num']);
                            $this->Session->write('Payment.e_date', $this->data['Booking']['m_expiry'].$this->data['Booking']['y_expiry']);
                            $this->Session->write('Payment.zip', $this->data['Booking']['postal_code']);
                            $this->Session->write('Payemnt.address', $this->data['Booking']['street_address']);
                            $this->Session->write('Payment.state', $this->data['Booking']['state']);
                            $this->Session->write('Payment.id', $this->Booking->id);
                            $this->redirect('/bookings/ChargeConfirmAmount/');
                    }else{
                            $this->Session->write('error', 'Some mandatory fields are missing, please review');
                            $p_id = $this->Session->read('Place.id');
                            $this->redirect('/places/'.$p_id);
                    }
            }
    }

    public function BookingsOverview(){
            $this->layout = 'User_Master_Page';
            $u_id = $this->Auth->getUser();
            $user_bookings = $this->Booking->query('
                SELECT * 
                FROM 
                        bookings, 
                        places, 
                        profiles, 
                        users 
                WHERE 
                        users.id = bookings.id_guest AND
                        profiles.id_user = bookings.id_guest AND 
                        bookings.id_place = places.id AND 
                        places.id_user = '.$u_id
            );
            $this->set('bookings', $user_bookings);
    }


    public function AcceptBooking($id = null){
            //accept the booking, charge, send notification, and place the thing in the calendar
            $this->layout = 'blank_for_test';
            $u_id = $this->Auth->getUser();
            //do payment
            $payment = $this->Booking->findByid($id);
            $user = $this->Booking->query('
                SELECT users.email 
                FROM users 
                WHERE users.id ='.$payment['Booking']['id_guest']
            );
            $user_email = $user[0]['users']['email'];
            $post_url = "https://secure.authorize.net/gateway/transact.dll";
            $post_values = array(
                // the API Login ID and Transaction Key must be replaced with valid values
                "x_login"		=> "7qrS88b54CH",
                "x_tran_key"		=> "7bHF46Ya8B7p2tQh",

                "x_version"			=> "3.1",
                "x_delim_data"		=> "TRUE",
                "x_delim_char"		=> "|",
                "x_relay_response"	=> "FALSE",

                "x_type"			=> "AUTH_CAPTURE",
                "x_method"			=> "CC",
                "x_card_num"		=> $payment['Booking']['card_num'],
                "x_exp_date"		=> $payment['Booking']['m_expiry'].$payment['Booking']['y_expiry'],

                "x_nightly_rate"	=> $payment['Booking']['night_rate'],
                "x_nights_booked"	=> $payment['Booking']['nights'],

                "x_amount"			=> ($payment['Booking']['total_payout'] - 1),
                "x_description"		=> "DogVacay.com home dog boarding and services, final payment.",

                "x_first_name"		=> $payment['Booking']['cardholder_name'],
                "x_email"			=> $user_email,
                "x_address"			=> $payment['Booking']['street_address'],
                "x_state"			=> $payment['Booking']['state'],
                "x_zip"				=> $payment['Booking']['postal_code']
                // Additional fields can be added here as outlined in the AIM integration
                // guide at: http://developer.authorize.net
            );

            $post_string = "";
            foreach( $post_values as $key => $value ){ 
                    $post_string .= "$key=" . urlencode( $value ) . "&"; 
            }
            $post_string = rtrim( $post_string, "& " );

            $request = curl_init($post_url); // initiate curl object
            curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
            curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
            $post_response = curl_exec($request); // execute curl post and store results in $post_response
            curl_close ($request); // close curl object
            // This line takes the response and breaks it into an array using the specified delimiting character
            $response_array = explode($post_values["x_delim_char"],$post_response);
            if($response_array[0] == 1){
                    //redirect to payment confirmation or something
            }else{
                    //uh oh :s
            }
    }//ends AcceptBookings

    public function ChargeConfirmAmount(){
        $this->layout = 'blank_for_test';
        $u_id = $this->Auth->getUser();
        $terms = $this->Session->read('terms');

        //Current API Login ID: 7qrS88b54CH
        //Current Transaction Key: 7bHF46Ya8B7p2tQh
        // By default, this sample code is designed to post to our test server for
        // developer accounts: https://test.authorize.net/gateway/transact.dll
        // for real accounts (even in test mode), please make sure that you are
        // posting to: https://secure.authorize.net/gateway/transact.dll
        $post_url = "https://secure.authorize.net/gateway/transact.dll";
        $post_values = array(
            // the API Login ID and Transaction Key must be replaced with valid values
            "x_login"			=> "7qrS88b54CH",
            "x_tran_key"		=> "7bHF46Ya8B7p2tQh",

            "x_version"			=> "3.1",
            "x_delim_data"		=> "TRUE",
            "x_delim_char"		=> "|",
            "x_relay_response"	=> "FALSE",

            "x_type"			=> "AUTH_CAPTURE",
            "x_method"			=> "CC",
            "x_card_num"		=> $this->Session->read('Payment.cc'),
            "x_exp_date"		=> $this->Session->read('Payment.e_date'),

            "x_amount"			=> "1.00",
            "x_description"		=> "DogVacay.com home dog boarding and services, credit card confirmation.",

            "x_first_name"		=> $this->Session->read('User.first_name'),
            "x_last_name"		=> $this->Session->read('User.last_name'),
            "x_email"			=> $this->Session->read('User.email'),
            "x_address"			=> $this->Session->read('Payemnt.address'),
            "x_state"			=> $this->Session->read('Payment.state'),
            "x_zip"				=> $this->Session->read('Payment.zip')
            // Additional fields can be added here as outlined in the AIM integration
            // guide at: http://developer.authorize.net
        );

        $post_string = "";
        foreach( $post_values as $key => $value ){ 
                $post_string .= "$key=" . urlencode( $value ) . "&"; 
        }
        $post_string = rtrim( $post_string, "& " );

        $request = curl_init($post_url); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
        $post_response = curl_exec($request); // execute curl post and store results in $post_response
        curl_close ($request); // close curl object
        // This line takes the response and breaks it into an array using the specified delimiting character
        $response_array = explode($post_values["x_delim_char"],$post_response);
        if($response_array[0] == 1){
                //send notifications
                //dog owner
                $user_name = $this->Session->read('User.first_name').' '.$this->Session->read('User.last_name');
                $dog_owner = $this->Booking->query('SELECT * FROM users, profiles WHERE profiles.id_user = '.$u_id.' AND users.id = '.$u_id);
                $dog_owner_email = $dog_owner[0]['users']['email'];
                $dog_owner_phone = $dog_owner[0]['profiles']['phone'];
                $dog_owner_address = $dog_owner[0]['profiles']['full_address'];

                $p_id = $this->Session->read('Place.id');
                //place owner
                $listing_owner = $this->Booking->query('SELECT * FROM users, places, profiles WHERE users.id = places.id_user AND users.id = profiles.id_user AND places.id = '.$p_id);
                $place_photos = $this->Booking->query('SELECT place_photos.location FROM place_photos WHERE place_photos.id_place = '.$p_id);
                if(count($place_photos)>0){
                        $pic = $place_photos[0]['place_photos']['location'];
                }else{
                        $pic = '/img/no_picture.jpg';
                }
                $aplicants_dogs = $this->Booking->query('SELECT pets.pet_name, pets.gender, pets.breed, pets.pet_size FROM pets WHERE pets.id_user = '.$u_id);
                $pet_names = 'dogs: ';
                $pet_info ='';
                for($i=0;$i<count($aplicants_dogs);$i++){
                        $pet_names .= $aplicants_dogs[$i]['pets']['pet_name'].', ';
                        $pet_names = rtrim($pet_names, ', ');
                        $pet_info .=  '['.$aplicants_dogs[$i]['pets']['gender'].', '.$aplicants_dogs[$i]['pets']['breed'].', '.$aplicants_dogs[$i]['pets']['pet_size'].' ]';
                }


                $host_name = $listing_owner[0]['users']['first_name'].' '.$listing_owner[0]['users']['last_name'];
                $order_id = $this->Session->read('Payment.id');
                $drop_date = $this->Session->read('Payment.drop');
                $pick_date = $this->Session->read('Payment.pick');
                $dogs_quantity = $this->Session->read('Payment.dogs_quantity');
                $host_phone = $listing_owner[0]['profiles']['phone'];
                $host_mail = $listing_owner[0]['users']['email'];
                $address = $listing_owner[0]['places']['full_address'];	
                $total_charge = $this->Session->read('Payment.total');
                $cancellation = $this->Session->read('Payment.cancellation');
                
                $data = compact('host_name', 'order_id', 'drop_date', 'pick_date', 'dogs_quantity', 
                    'host_phone', 'host_mail', 'address', 'total_charge', 'cancellation'
                );
                //loged in user

                //notification for dog owner
                //$res = $this->Mailer->bookUserEmail($data);

                //notification for listing owner
                //$resHost = $this->Mailer->bookHostEmail($data);

                $this->Session->delete('Payment');
                $this->redirect('/bookings/Confirmation');
        }else{
                $pay_id = $this->Session->read('Payment.id');
                $this->Booking->delete($pay_id);
                $this->redirect('/bookings/Denied');
        }
    }//Ends ChargeConfirmAmount

    public function Denied(){
            $this->layout = 'blank_for_test';
            echo 'Payment process was denied, please check your credit card or try again later';
    }//ends Denied credit card message

    public function Confirmation(){
        $this->layout = 'User_Master_Page';
        //the Confirmation on succesfull booking.
    }
    
    public function paymentInfo(){
        $this->layout = 'blank_for_test';
    }
    
    public function bookIt(){
        $this->layout = 'booking_details';
        $u_id = $this->Auth->getUser();
        
        if(empty($this->data) || !isset($this->data['Booking'])){
            $this->redirect('/places/');
        }else{
            $drop_day = $this->data['Booking']['drop'];
            $pick_day = $this->data['Booking']['pick'];
            $dog_count = $this->data['Booking']['dogs'];
            $p_id = $this->data['Booking']['place'];
            //validate
            if(
                strtotime($drop_day) === false || strtotime($pick_day) === false || 
                !is_numeric($dog_count) || !is_numeric($p_id)
            ){
                $this->redirect('/places/'.$p_id);
            }
            $this->set('placeId', $p_id);
        }
        
        //loading the models
        $this->loadModel('Place');
        $this->loadModel('PlaceTerms');
        $this->loadModel('Recommendation');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Profile');
        
        $place = $this->Place->findByid($p_id);
        if(!$place){
            $this->redirect('/places/');
        }
        $place_owner = $place['Place']['id_user'];
        
        $this->set('place', $place);
        //terms
        $placeTerms = $this->PlaceTerms->findByid_place($p_id);
        
        $this->data['Booking']['drop'] = $drop_day;
        $this->data['Booking']['pick'] = $pick_day;
        $this->data['Booking']['dogs'] = $dog_count;
        
        //payment information///
        $user_place = $this->Place->findByid_user($u_id);
        $guestProfile = $this->Profile->findByid_user($u_id);
        if(isset($this->data['Booking']['isCheckout']) && (int)$this->data['Booking']['isCheckout'] === 1){
            //process the checkout
            try{
                $this->_processCheckout($place, $placeTerms, $guestProfile);
            }catch(Exception $ex){
                $this->Session->write('Note.error', $ex->getMessage());
            }
        }
        $this->data['Booking']['full_address'] = (!is_null($user_place['Place']['full_address']))?$user_place['Place']['full_address']:'';
        $this->data['Booking']['city'] = (!is_null($user_place['Place']['city']))?$user_place['Place']['city']:'';
        $this->data['Booking']['state'] = (!is_null($user_place['Place']['state']))?$user_place['Place']['state']:'';
        $this->data['Booking']['postal_code'] = (!is_null($user_place['Place']['zip']))?$user_place['Place']['zip']:'';
        $this->data['Booking']['first_name'] = (!is_null($guestProfile['User']['first_name']))?$guestProfile['User']['first_name']:'';
        $this->data['Booking']['last_name'] = (!is_null($guestProfile['User']['last_name']))?$guestProfile['User']['last_name']:'';
        $this->data['Booking']['place_own'] = (!is_null($p_id))?$p_id:'';
        $this->data['Booking']['name_on_card'] = (!is_null($guestProfile['User']['first_name']) && !is_null($guestProfile['User']['last_name']))?$guestProfile['User']['first_name'].' '.$guestProfile['User']['last_name']:'';
        $nights = $this->Date->num_days($drop_day,$pick_day);
        $nights = $nights - 1;
        $this->data['Booking']['nights'] = $nights;
        
        //dog vacay fee
        $this->set('nights',$nights);
        $ratePerDay = $placeTerms['PlaceTerms']['nigthly_rates'];
        $subtottal = $ratePerDay*$nights*$dog_count;
        $dogVacayFee = $this->_getDogVacayFee($subtottal);
        $total = $subtottal + $dogVacayFee;
        $additionalFee = $this->_getAdditionalFee();
        
        $this->set('dogVacayFee', $dogVacayFee);        
        $this->set('ratePerDay', $ratePerDay);        
        $this->set('total', $total);
        $this->set('additionalFee', $additionalFee);
        $this->set('feePercent', Configure::read('dogVacayFee'));
        
        // Getting recommendations
        $recommend = $this->Recommendation->find('all', array(
                'conditions' => array('Recommendation.user_id' => $place_owner),
                'order' => 'Recommendation.date DESC',
                'limit' => 3,
        ));
        $this->set('recommend',$recommend);
        //End Recommendations
        
        //Dogs
        $guestDogs = $this->Dashboard->getData();
        $guestDogs = $guestDogs['Pet'];
        $this->set('guestDogs', $guestDogs);
        $this->set('dogsCount', $dog_count);
        //END Dogs
       
        //Place photo
        $plPhoto = $this->PlacePhoto->getPrimaryPhoto($place_owner, $p_id);
       
        if(is_array($plPhoto) && count($plPhoto) > 0){
            $primaryPhoto = '/system/places/'.$plPhoto['0']['place_photos']['location'];
        }else{
            $primaryPhoto = '/system/places/home.jpg';
        }
        $this->set('primaryPhoto', $primaryPhoto);
        //End place photo
        
        //////property////
        $place_address = $place['Place']['full_address'];
        $this->set('property_address',$place_address);

        $place_yard = $place['Place']['yard'];
        if(!isset($place_yard) || $place_yard == 'No' || $place_yard == ''){
            $place_yard = "N/A";
        }
        $this->set('property_yard',$place_yard);
      
        $dog_toys = $place['Place']['toys'];
        if(!isset($dog_toys) || $dog_toys == 'No' || $dog_toys == ''){
            $dog_toys = "N/A";
        }
        $this->set('dog_toys',$dog_toys);


        $profile = $this->Profile->findByid_user($place_owner);
        $idb = $profile['Profile']['idb'];
        $this->set('insured',$idb);       


        $pro_host = $place['Place']['pro_host'];
        $this->set('prof_cert',$pro_host);

        $psi_mem = $profile['Profile']['psi'];
        $this->set('psi_mem',$psi_mem);
        $nap_mem = $profile['Profile']['nap'];
        $this->set('nap_mem',$nap_mem);
        $pup_mem = $profile['Profile']['pup'];
        $this->set('pup_mem',$pup_mem);
        $other_mem = $profile['Profile']['other'];
        $other_text = $profile['Profile']['other_text'];
        $this->set('other_mem',$other_mem);
        $this->set('other_text',$other_text);
        
    }
    
    public function update($bookingId = null){
        if(is_null($bookingId) || !is_numeric($bookingId)){
            $this->redirect('/places/');
        }
        
        $this->layout = 'booking_details';
        $u_id = $this->Auth->getUser();
        
        //loading the models
        $this->loadModel('Place');
        $this->loadModel('PlaceTerms');
        $this->loadModel('Recommendation');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Profile');
        
        $booking = $this->Booking->find('first', array(
            'conditions' => array(
                'Booking.id' => $bookingId,
                'Booking.id_guest' => $u_id,
                'Booking.status' => 'PENDING',
            ),
        ));
        if(!$booking){
            $this->redirect('/places/');
        }else{
            //correct the formatting of dates
            $booking['Booking']['drop'] = date('m/d/Y', strtotime($booking['Booking']['drop']));
            $booking['Booking']['pick'] = date('m/d/Y', strtotime($booking['Booking']['pick']));
        }
        //booking found set the booking id
        $this->set('bookingId', $bookingId);
        if(!empty($this->data)){
            $isPost = true;
            $p_id = $this->data['Booking']['place'];
        }else{
            $isPost = false;
            $this->data = $booking;
            $p_id = $this->data['Booking']['id_place'];
        }
        $this->set('placeId', $p_id);
        
        $drop_day = $this->data['Booking']['drop'];
        $pick_day = $this->data['Booking']['pick'];
        $dog_count = $this->data['Booking']['dogs'];
        
        $place = $this->Place->findByid($p_id);
        if(!$place){
            $this->redirect('/places/');
        }
        $place_owner = $place['Place']['id_user'];
        
        $this->set('place', $place);
        //terms
        $placeTerms = $this->PlaceTerms->findByid_place($p_id);
        
        //payment information///
        $user_place = $this->Place->findByid_user($u_id);
        $guestProfile = $this->Profile->findByid_user($u_id);
        
        //if the form had been submitted
        if(
            $isPost && isset($this->data['Booking']['isCheckout']) && 
            (int)$this->data['Booking']['isCheckout'] === 1
        ){
            //process the checkout
            try{
                $this->_processCheckout($place, $placeTerms, $guestProfile, $booking);
            }catch(Exception $ex){
                $this->Session->write('Note.error', $ex->getMessage());
            }
        }

        $this->data['Booking']['place_own'] = (!is_null($p_id))?$p_id:'';
        $this->data['Booking']['name_on_card'] = (!is_null($guestProfile['User']['first_name']) && !is_null($guestProfile['User']['last_name']))?$guestProfile['User']['first_name'].' '.$guestProfile['User']['last_name']:'';
        
        $nights = $this->Date->num_days($drop_day, $pick_day);
        $nights = $nights - 1;
        $this->data['Booking']['nights'] = $nights;
        $this->set('nights',$nights);
        
        //dog vacay fee
        $ratePerDay = $placeTerms['PlaceTerms']['nigthly_rates'];
        $subtotal = $ratePerDay*$nights*$dog_count;
        $dogVacayFee = $this->_getDogVacayFee($subtotal);
        $total = $subtotal + $dogVacayFee;
        
        $this->set('dogVacayFee', $dogVacayFee);        
        $this->set('ratePerDay', $ratePerDay);
        $this->set('feePercent', $this->_dogVacayFee);
        $this->set('total', $total);
        
        // Getting recommendations
        $recommend = $this->Recommendation->find('all', array(
                'conditions' => array('Recommendation.user_id' => $place_owner),
                'order' => 'Recommendation.date DESC',
                'limit' => 3,
        ));
        $this->set('recommend',$recommend);
        //End Recommendations
        
        //Dogs
        $guestDogs = $this->Dashboard->getData();
        $guestDogs = $guestDogs['Pet'];
        $this->set('guestDogs', $guestDogs);
        $this->set('dogsCount', $dog_count);
        //END Dogs
       
        //Place photo
        $plPhoto = $this->PlacePhoto->getPrimaryPhoto($place_owner, $p_id);
       
        if(is_array($plPhoto) && count($plPhoto) > 0){
            $primaryPhoto = '/system/places/'.$plPhoto['0']['place_photos']['location'];
        }else{
            $primaryPhoto = '/system/places/home.jpg';
        }
        $this->set('primaryPhoto', $primaryPhoto);
        //End place photo
        
        //////property////
        $place_address = $place['Place']['full_address'];
        $this->set('property_address',$place_address);

        $place_yard = $place['Place']['yard'];
        if(!isset($place_yard) || $place_yard == 'No' || $place_yard == ''){
            $place_yard = "N/A";
        }
        $this->set('property_yard',$place_yard);
      
        $dog_toys = $place['Place']['toys'];
        if(!isset($dog_toys) || $dog_toys == 'No' || $dog_toys == ''){
            $dog_toys = "N/A";
        }
        $this->set('dog_toys',$dog_toys);


        $profile = $this->Profile->findByid_user($place_owner);
        $idb = $profile['Profile']['idb'];
        $this->set('insured',$idb);       


        $pro_host = $place['Place']['pro_host'];
        $this->set('prof_cert',$pro_host);

        $psi_mem = $profile['Profile']['psi'];
        $this->set('psi_mem',$psi_mem);
        $nap_mem = $profile['Profile']['nap'];
        $this->set('nap_mem',$nap_mem);
        $pup_mem = $profile['Profile']['pup'];
        $this->set('pup_mem',$pup_mem);
        $other_mem = $profile['Profile']['other'];
        $other_text = $profile['Profile']['other_text'];
        $this->set('other_mem',$other_mem);
        $this->set('other_text',$other_text);
    }
    
    public function completeCheckout($bookingId = null){
        if(is_null($bookingId) || !is_numeric($bookingId)){
            $this->redirect('/places/');
        }
       $this->set('bookingId',$bookingId);
        $userId = $this->Auth->getUser();
        
        $this->layout = 'booking_details';
        
        //Loading the models
        $this->loadModel('Place');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Pet');
        
        // Getting booking
        $booking = $this->Booking->find('first', array(
                'conditions' => array(
                    'Booking.id' => $bookingId,
                    'Booking.id_guest' => $userId,
                ),
        ));
		if(!$booking){
			$this->redirect('/bookings/requests/');
		}
        $place_id = $booking['Place']['id'];
        
        //place photo
        $placePhoto = $this->PlacePhoto->find('first', array(
            'conditions' => array(
                'OR' => array(
                    array(
                        'PlacePhoto.id_place' => $place_id,
                        'PlacePhoto.primary' => 'ye',
                    ),
                    array('PlacePhoto.id_place' => $place_id),
                ),
            ),
        ));
        $dogs_count = $booking['Booking']['dogs'];
        
        $guest_id = $booking['Booking']['id_guest'];
        $guestDogs = $this->Pet->find('all', array(
            'conditions' => array('Pet.id_user' => $guest_id), 'limit' => $dogs_count
        ));
        if($placePhoto){
            $path = $placePhoto['PlacePhoto']['location'];
        }else{
            $path = 'home.jpg';
        }
        $path = SYSTEM_PATH.'places/'.$path;

        $this->set('contact', $place_id);
        $booking['Booking']['place'] = $place_id;
        $booking['Booking']['path'] = $path;
        $this->set('data',$booking);
        $this->set('dogs', $guestDogs);
            
    }
    
    public function hostCheckout($id){
        $u_id = $this->Auth->getUser();
        $this->layout = 'booking_details';
        
        //loading the models
        $this->loadModel('Pet');
        $this->loadModel('Profile');
        $this->loadModel('Place');
        $this->loadModel('Recommendation');
        
        //get the booking record
        $booking = $this->Booking->findByid($id);
        if(!$booking){
            $this->redirect('/bookings/requests/');
        }
        $dogs_count = $booking['Booking']['dogs'];
    
        $drop = date('l, M j',strtotime($booking['Booking']['drop']));
        $pick = date('l, M j',strtotime($booking['Booking']['pick']));
        $created = date('F,d Y H:i:s',strtotime($booking['Booking']['created']));
        $now = date('F,d Y H:i:s');
        
        $guest_id = $booking['Booking']['id_guest'];
        $socials = $this->Socials->getFriends($guest_id);
        $this->set('socials', $socials);
        $guest_dogs = $this->Pet->find('all', array(
            'conditions' => array('Pet.id_user' => $guest_id), 'limit' => $dogs_count
        ));
        
        //set the place
        $this->set('place', $booking['Place']);
        
        //get the guest profile
        $profile = $this->Profile->findByid_user($guest_id);
        $this->set('profile', $profile['Profile']);
        $this->set('user', $profile['User']);
        
        $avatar = ((isset($profile['Profile']['photo_path']) && !empty($profile['Profile']['photo_path'])) ? 
                $profile['Profile']['photo_path'] : 'default_avatar.png'
        );
        
        //get the guest's place
        $guestPlace = $this->Place->findByid_user($guest_id);
        $this->set('guestPlace', $guestPlace['Place']);
        
        //get guest's reviews
        $recommend = $this->Recommendation->AllForUser($guest_id);
        $this->set('recommend', $recommend);
        if($recommend){
            $this->set('recommendCount', count($recommend));
        }else{
            $this->set('recommendCount', 0);
        }
        
        $subtottal = $dogs_count * $booking['Booking']['nights'] * $booking['Booking']['nigthly_rate'];
        $dogVacayFee = $this->_getDogVacayFee($subtottal);
        $additionalFee = $this->_getAdditionalFee();
        //$created = strtotime($booking['Booking']['created']);
        $data = array(
            'drop'      => $drop,
            'pick'      => $pick,
            'created'   => $created,
            'now'       => $now,
            'booking'   => $booking,
            'guest_dogs' => $guest_dogs,
            'guestId'   => $guest_id,
            'avatar'    => $avatar,
            'bookingId' => $id,
            'dogVacayFee'=> $dogVacayFee,
            'additionalFee'=> $additionalFee,
            'subtottal' => $subtottal
        );
        $this->set($data);
       
        
    }
    
    public function hostAccept($bookingId = null){
        if(is_null($bookingId) || !is_numeric($bookingId)){
            $this->redirect('/places/');
        }
        $userId = $this->Auth->getUser();
        
        $this->layout = 'booking_details';
        //loading the models
        $this->loadModel('Place');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Pet');
        $this->loadModel('Profile');
        
         $booking = $this->Booking->find('first', array(
                'conditions' => array(
                    'Booking.id' => $bookingId,
                    'Booking.id_guest' => $userId,
                    'Booking.status' => 'ACCEPTED',
                ),
        ));
        
         if(!$booking){
             $this->redirect('/places/');
         }
         
        //owner profile
        $ownerProfile = $this->Profile->findByid_user($booking['Booking']['id_owner']);
        $this->set('ownerProfile', $ownerProfile);
         
         $place_id = $booking['Place']['id'];
        $this->set('placeId', $place_id);
        //place photo
        $placePhoto = $this->PlacePhoto->find('first', array(
            'conditions' => array(
                'OR' => array(
                    array(
                        'PlacePhoto.id_place' => $place_id,
                        'PlacePhoto.primary' => 'ye',
                    ),
                    array('PlacePhoto.id_place' => $place_id),
                ),
            ),
        ));
        if($placePhoto){
            $path = $placePhoto['PlacePhoto']['location'];
        }else{
            $path = 'home.jpg';
        }
        $path = SYSTEM_PATH.'places/'.$path;
        
        $booking['Booking']['place'] = $place_id;
        $booking['Booking']['path'] = $path;
        
        $guest_id = $booking['Booking']['id_guest'];
        $dogs_count = $booking['Booking']['dogs'];
        
        $guestDogs = $this->Pet->find('all', array(
            'conditions' => array('Pet.id_user' => $guest_id), 'limit' => $dogs_count
        ));
        $dogs = array();
        $apostropheDogs = array();
        if(is_array($guestDogs) && count($guestDogs)){
            foreach($guestDogs as $item){
                $dogs[] = $item['Pet']['pet_name'];
                $apostropheDogs[] = $item['Pet']['pet_name']."'s";
            }
        }
        if(count($dogs)){
            $dogs = implode(' and ', $dogs);
            $apostropheDogs = implode(' and ', $apostropheDogs);
        }else{
            $dogs = 'Your dog';
            $apostropheDogs = "Your dog's";
        }
        $booking['Booking']['pet_name'] = $dogs;
        $booking['Booking']['pet_name_apostrophe'] = $apostropheDogs;
        
        $subtottal = $dogs_count * $booking['Booking']['nights'] * $booking['Booking']['nigthly_rate'];
        $dogVacayFee = $this->_getDogVacayFee($subtottal);
        $additionalFee = $this->_getAdditionalFee();
        //$booking['Booking']['dogVacayFee'] = $dogVacayFee;
        $this->set('dogVacayFee',$dogVacayFee); 
        $this->set('additionalFee',$additionalFee); 
        $this->set('data', $booking);
        $this->set('dogs', $guestDogs);
    }
    public function hostStatus($bookingId = null){
        
        if(is_null($bookingId) || !is_numeric($bookingId)){
            $this->redirect('/places/');
        }
        $userId = $this->Auth->getUser();
        
        $this->layout = 'booking_details';
        //loading the models
        $this->loadModel('Place');
        $this->loadModel('PlacePhoto');
        $this->loadModel('Pet');
        $this->loadModel('Profile');
        
        $booking = $this->Booking->find('first', array(
                'conditions' => array(
                    'Booking.id' => $bookingId,
                    'Booking.id_guest' => $userId,
                    'Booking.status !=' => 'ACCEPTED',
                ),
        ));
		
		if(!$booking){
             $booking_accept = $this->Booking->find('first', array(
                'conditions' => array(
                    'Booking.id' => $bookingId,
                    'Booking.id_guest' => $userId,
                    'Booking.status ' => 'ACCEPTED',
                ),
			));
			if($booking_accept){
				$this->redirect('/bookings/hostAccept/'.$bookingId);
			}else{
                $this->redirect('/bookings/requests/');
			}           
        }
		
        if($booking["Booking"]['status'] == 'PENDING'){
            $pageTitle = 'Your Reservation Is Pending.';
        }elseif($booking["Booking"]['status'] == 'DECLINED'){
            $pageTitle = 'Your Reservation Is Declined.';
        }elseif($booking["Booking"]['status'] == 'COMPLETED'){
            $pageTitle = 'Your Reservation Is Completed.';
        }elseif($booking["Booking"]['status'] == 'STARTED'){
            $pageTitle = 'Your Reservation Is Started.';
        }else{
            $pageTitle = '';
        }
        $this->set('pageTitle', $pageTitle);
         
        //owner profile
        $ownerProfile = $this->Profile->findByid_user($booking['Booking']['id_owner']);
        $this->set('ownerProfile', $ownerProfile);
         
         $place_id = $booking['Place']['id'];
        $this->set('placeId', $place_id);
        //place photo
        $placePhoto = $this->PlacePhoto->find('first', array(
            'conditions' => array(
                'OR' => array(
                    array(
                        'PlacePhoto.id_place' => $place_id,
                        'PlacePhoto.primary' => 'ye',
                    ),
                    array('PlacePhoto.id_place' => $place_id),
                ),
            ),
        ));
        if($placePhoto){
            $path = $placePhoto['PlacePhoto']['location'];
        }else{
            $path = 'home.jpg';
        }
        $path = SYSTEM_PATH.'places/'.$path;
        
        $booking['Booking']['place'] = $place_id;
        $booking['Booking']['path'] = $path;
        
        $guest_id = $booking['Booking']['id_guest'];
        $dogs_count = $booking['Booking']['dogs'];
        
        $guestDogs = $this->Pet->find('all', array(
            'conditions' => array('Pet.id_user' => $guest_id), 'limit' => $dogs_count
        ));
        $dogs = array();
        $apostropheDogs = array();
        if(is_array($guestDogs) && count($guestDogs)){
            foreach($guestDogs as $item){
                $dogs[] = $item['Pet']['pet_name'];
                $apostropheDogs[] = $item['Pet']['pet_name']."'s";
            }
        }
        if(count($dogs)){
            $dogs = implode(' and ', $dogs);
            $apostropheDogs = implode(' and ', $apostropheDogs);
        }else{
            $dogs = 'Your dog';
            $apostropheDogs = "Your dog's";
        }
        $booking['Booking']['pet_name'] = $dogs;
        $booking['Booking']['pet_name_apostrophe'] = $apostropheDogs;
        
        $subtottal = $dogs_count * $booking['Booking']['nights'] * $booking['Booking']['nigthly_rate'];
        $dogVacayFee = $this->_getDogVacayFee($subtottal);
        $additionalFee = $this->_getAdditionalFee();
        //$booking['Booking']['dogVacayFee'] = $dogVacayFee;
        $this->set('dogVacayFee',$dogVacayFee); 
        $this->set('additionalFee',$additionalFee); 
         
        $this->set('data', $booking);
        $this->set('dogs', $guestDogs);
    }
    
    public function requests(){
        $this->layout = 'User_Account_Page';
        $userId = $this->Auth->getUser();
        
        $userDb = $this->Dashboard->getData();
        $this->loadModel('Place');
        
        $this->paginate = array(
                'limit' => 10,
                'order' => array('Booking.created' => 'desc'),
                'conditions' => array('data' => array(
                'id' => $userId,
                        )),
                );
        $bookings = $this->paginate('Booking');
        //var_dump($bookings);die;
        $place = $userDb['Place'];
        //$bookings = $this->Booking->findAllBookingsForPlace($u_id);
        $this->set('bookings', $bookings);       
        $this->set('userId', $userId);       
    } 
    
    public function charge($bookingId = null){
        if(is_null($bookingId) || !is_numeric($bookingId)){
            die('Invalid booking id');
        }
        
        $userId = $this->Auth->getUser();
        
        $booking = $this->Booking->find('first', array(
            'conditions' => array(
                'Booking.id' => $bookingId,
                'Booking.id_guest' => $userId,
                'Booking.status' => 'STARTED',
            ),
        ));
        if(!$booking){
            die('Selected booking is not started and not in process');
        }
        
        if($booking['Booking']['paymentMethod'] === 'credit-card'){
            //booking exist and has status STARTED, so charge the card and set the status to COMPLETED
            $transactionId = $this->AuthorizeNet->chargeCard($booking['Booking']['transactionId']);
            if(is_null($transactionId)){
                //failure
                die('Charging credit card failed, Authorize.net message: '.$this->AuthorizeNet->responseText);
            }else{
                //success
                $booking['Booking']['status'] = 'COMPLETED';
                $res = $this->Booking->save($booking);
                die('Credit card successfully charged');
            }
        }elseif($booking['Booking']['paymentMethod'] === 'paypal'){
            die('Please use credit card option instead for now');
        }else{
            die('Invalid payment option');
        }
    }
    
    public function hostResponse(){
        if(isset($this->data) && isset($this->data['Booking']['response_type'])){
           if($this->data['Booking']['response_type'] == 'accept'){
               $this->_processAccept();
           }elseif($this->data['Booking']['response_type'] == 'decline'){
               $this->_processDecline();
           }elseif($this->data['Booking']['response_type'] == 'other'){
               $this->_processOther();
           }else{
               $this->Session->write('Note.error', "Soemthing went wrong with Your request, please try once again");
               $this->redirect('/bookings/requests/');
           }
        }else{
            $this->Session->write('Note.error', "Soemthing went wrong with Your request, please try once again");
            $this->redirect('/bookings/requests/');
        }
    }
    /**
     * Processes data submitted for booking detail, authorizes the funds needed
     * @param array $place The place data
     * @param array $placeTerms The terms data
     * @param array $guestProfile The guest profile data
     * @param array $booking [optional] The booking data only supplied 
     * if we are updating the booking, defaults to null (new booking)
     * @return boolean Redirects on success returns false otherwise
     * @throws Exception If any validation fails
     */
    protected function _processCheckout($place, $placeTerms, $guestProfile, $booking = null){
        $u_id = $this->Auth->getUser();
        if(is_null($booking)){
            $booking = $this->data['Booking'];
            $newBooking = true;
        }else{
            $booking = array_merge($booking['Booking'], $this->data['Booking']);
            $newBooking = false;
        }
        //flag that indicates payment respnse
        $paymentSuccessfull = false;
        
        //payment amounts
        $ratePerDay = $placeTerms['PlaceTerms']['nigthly_rates'];
        $tax = 0;//for now
        $shipping = 0;//for now
        
        if(trim($booking['paymentMethod']) === 'credit-card'){
           
            //credit card processing
            if(
                !isset($booking['first_name']) || empty($booking['first_name']) || 
                !isset($booking['last_name']) || empty($booking['last_name']) || 
                !isset($booking['full_address']) || empty($booking['full_address']) || 
                !isset($booking['city']) || empty($booking['city']) || 
                !isset($booking['state']) || empty($booking['state']) || 
                !isset($booking['postal_code']) || empty($booking['postal_code']) || 
                !isset($booking['card_type']) || empty($booking['card_type']) || 
                !isset($booking['card_number']) || empty($booking['card_number']) || 
                //!is_numeric($booking['card_number']) ||
                !isset($booking['security_code']) || empty($booking['security_code']) || 
                !is_numeric($booking['security_code']) ||
                !isset($booking['expire_month']) || empty($booking['expire_month']) || 
                !is_numeric($booking['expire_month']) || 
                !isset($booking['expire_year']) || empty($booking['expire_year']) || 
                !is_numeric($booking['expire_year']) || 
                !is_numeric($booking['dogs']) || $booking['dogs'] <= 0
            ){
                throw new Exception('Please fill in all the fields in order to complete reservation');
            }
            if(strtotime($booking['drop']) > strtotime($booking['pick'])){
                throw new Exception('Invalid Drop off / Pick up date(s)');
            }
            $nights = $this->Date->num_days($booking['drop'], $booking['pick']);
            if($nights <= 0){
                throw new Exception('Your Dog(s) should stay in vacation for at least one day');
            }
            
            $subtotal = $ratePerDay*$nights*$booking['dogs'];
            
            $dogVacayFee = $this->_getDogVacayFee($subtotal);
            
            $total = $subtotal + $dogVacayFee;
            $desc = 'Dog Vacay Reservation';
            
           /* $billinginfo = array(
                "fname" => $booking['first_name'], 
                "lname" => $booking['last_name'], 
                "address" => $booking['full_address'], 
                "city" => $booking['city'], 
                "state" => $booking['state'], 
                "zip" => $booking['postal_code'], 
                "country" => "USA", //for now
            );
            $shippinginfo = array(
                "fname" => $booking['first_name'], 
                "lname" => $booking['last_name'], 
                "address" => $booking['full_address'], 
                "city" => $booking['city'], 
                "state" => $booking['state'], 
                "zip" => $booking['postal_code'], 
                "country" => "USA", //for now
            );
            
            $result = $this->AuthorizeNet->chargeCard(
                Configure::read('AUTHORIZE_LOGIN_ID'), 
                Configure::read('AUTHORIZE_TRANSACTION_KEY'), 
                $booking['card_number'], $booking['expire_month'], $booking['expire_year'], 
                $booking['security_code'], Configure::read('PAYMENT_LIVE'), 
                $total, $tax, $shipping, $desc, $billinginfo, 
                $guestProfile['User']['email'], $guestProfile['Profile']['phone'], $shippinginfo
            );
            
            //for now only success
            $result[1] = 1;
            $result[7] = md5(microtime());
            
            if(isset($result[1]) && ((int)$result[1] === 1 || (int)$result[1] === 4)){
                //success
                $transactionId = $result[7];
                $paymentSuccessfull = true;
            }else{
                //failure
                $responseText = ((isset($result[4]) && $result[4] )? $result[4] : 
                    'An error was encountered while we were processing Your reservation payment');
                throw new Exception($responseText);
            }*/
            
            if(!$newBooking){
                //existing booking need to void first
                $res = $this->AuthorizeNet->cancelPayment($booking['transactionId']);
            }
            
            //authorize the total amount
            $transactionId = $this->AuthorizeNet->authorizeFunds(
                $total, $booking['card_number'], $booking['security_code'], 
                $booking['expire_month'], $booking['expire_year']
            );
            //if not approved
            if(is_null($transactionId)){
                //failure
                $responseText = (($this->AuthorizeNet->responseText)? $this->AuthorizeNet->responseText : 
                    'An error was encountered while we were processing Your reservation payment');
                throw new Exception($responseText);
            }else{
                //success
                $paymentSuccessfull = true;
            }
        }else{
            //paypal
            throw new Exception('Please use Credit Card option for now');
        }
        if($paymentSuccessfull === true){
            if(isset($this->data['Booking']['update']) && $this->data['Booking']['update'] == '1'){
                $current = $this->Place->findByid_user($u_id);
                $userPlace['full_address'] = $this->data['Booking']['full_address'];
                $userPlace['city'] = $this->data['Booking']['city'];
                $userPlace['state'] = $this->data['Booking']['state'];
                $userPlace['zip'] = $this->data['Booking']['postal_code'];
                if($current){
                    $toSave = array_merge($current['Place'], $userPlace);
                    $this->Place->save($toSave);
                    $this->Dashboard->cleanUp();
                }else{
                    $this->Place->create();
                    $userPlace['id_user'] = $u_id;
                    $this->Place->save($userPlace);
                    $this->Dashboard->cleanUp();
                    //$this->redirect('/newprofiles/');
                }
            }
            $booking['id_place'] = $place['Place']['id'];
            $booking['id_owner'] = $place['Place']['id_user'];
            $booking['id_guest'] = $guestProfile['User']['id'];
            
            $booking['drop'] = $this->Date->toMYSQL($booking['drop']);
            $booking['pick'] = $this->Date->toMYSQL($booking['pick']);
            //always set status to pending once the booking was modified
            $booking['status'] = 'PENDING';
            
            $booking['transactionId'] = $transactionId;
            $booking['nights'] = $nights;
            $booking['nigthly_rate'] = $ratePerDay;
            $booking['total_payout'] = $total;
            $booking['cancellation'] = '';//for now
            //add the booking record
            $res = $this->Booking->save(array(
                'Booking' => $booking,
            ));
            $bookingId = $this->Booking->id;
            
            $this->_newBookingNotify($booking, $guestProfile);
            
            //set the notice and redirect
            $this->Session->write('Note.ok', "Your Reservation request was successfully processed and is awaiting for host's confirmation");
            $this->redirect('/bookings/completeCheckout/'.$bookingId.'/');
        }
        
        return false;
    }

    /**
     * Returns the amount to be charged as Dog Vacay fee
     * @return int The fee amount
     */
    protected function _getDogVacayFee($subtottal){
        //for now just return
        $fee = Configure::read('dogVacayFee');
        $return = $subtottal/100*$fee;
        return $return;
    }
    
    /**
     * Returns the amount to be charged as Dog Vacay fee
     * @return int The fee amount
     */
    protected function _getAdditionalFee(){
        //for now just return
        $fee = $this->_additionalFee;
        return $fee;
    }
    /**
     * Sends an email and adds a message to host's inbox abount new reservation request
     * @param array $booking An array of booking data
     * @param array $guestProfile An array of guest progile data
     */
    protected function _newBookingNotify($booking, $guestProfile){
        $guestId = $booking['id_guest'];
        $ownerId = $booking['id_owner'];

        $messageText = "";
        $messageText.= $guestProfile['User']['first_name']." ".$guestProfile['User']['last_name'][0].
                " has made a reservation request to You. <br />
                Please review Your requests and respnd to the request!<br />";
        $messageText.= "Please contact us if You have questions";
        
        $this->Mailer->mailAndMessageToUser($ownerId, $messageText);
    }


    // Process host accept of booking
    protected function _processAccept(){
        $data = $this->data['Booking'];
        $bookingId = $data['booking_id'];
        $bookingData = $this->Booking->findByid($bookingId);
        if(!$bookingData){
            $this->Session->write('Note.error', "Soemthing went wrong with Your request, please try once again ");
            $this->redirect('/bookings/requests/');
        }
        $bookingData['Booking']['status'] = "ACCEPTED";
        $this->Booking->save($bookingData);
        $questId = $bookingData['Booking']['id_guest'];
        $ownerId = $bookingData['Booking']['id_owner'];
        $this->loadModel('Profile');
        $owner = $this->Profile->findByid_user($ownerId);
        $messageText = "Congratulations ! <br />";
        $messageText.= $owner['User']['first_name']." ".$owner['User']['last_name'][0]." has accepted Your booking <br />";
        if(isset($data['accept_description']) && strlen($data['accept_description']) > 0){
            $messageText.="with following message <br />";
            $messageText.= $data['accept_description']." <br /> ";
        }
        if(isset($data['mag']) && $data['mag'] == '1'){
            $messageText.= $owner['User']['first_name']." also wants to schedule Meet and Greet with You <br />";
        }
        $messageText.= "Please contact us if You have questions";
        $this->Mailer->mailAndMessageToUser($questId, $messageText);
        $this->Session->write('Note.ok', "You have successfully accepted booking request");
        $this->redirect('/bookings/requests/');
    }
    
    protected function _processDecline(){
        $data = $this->data['Booking'];
        $bookingId = $data['booking_id'];
        $bookingData = $this->Booking->findByid($bookingId);
        if(!$bookingData){
            $this->Session->write('Note.error', "Soemthing went wrong with Your request, please try once again");
            $this->redirect('/bookings/requests/');
        }
        $bookingData['Booking']['status'] = "DECLINED";
        $this->Booking->save($bookingData);
        $questId = $bookingData['Booking']['id_guest'];
        $ownerId = $bookingData['Booking']['id_owner'];
        $this->loadModel('Profile');
        $owner = $this->Profile->findByid_user($ownerId);
        $messageText = "We are sorry ! <br />";
        $messageText.= $owner['User']['first_name']." ".$owner['User']['last_name'][0]." has declined Your booking request<br />";
        if($data['not_available'] == '1' || $data['full_capacity'] == '1' || $data['other'] == '1'){
            $messageText.= "With followong reason(s)<br />";
            if($data['not_available'] == '1'){
                $messageText.= "I'm not avaliable at that time <br />";
            }
            if($data['full_capacity'] == '1'){
                $messageText.= "I'm already at full capacity <br />";
            }
            if($data['other'] == '1'){
                $messageText.= "Other reason <br />";
            }
        }
        if(isset($data['decline_description']) && strlen($data['decline_description']) > 0){
            $messageText.="with following message <br />";
            $messageText.= $data['decline_description']." <br /> ";
        }
        $messageText.= "Please contact us if You have q`uestions";
        $this->Mailer->mailAndMessageToUser($questId, $messageText);
        $this->Session->write('Note.ok', "You have declined booking request");
        $this->redirect('/bookings/requests/');
    }
    
    protected function _processOther(){
        $data = $this->data['Booking'];
        $bookingId = $data['booking_id'];
        $bookingData = $this->Booking->findByid($bookingId);
        if(!$bookingData){
            $this->Session->write('Note.error', "Soemthing went wrong with Your request, please try once again");
            $this->redirect('/bookings/requests/');
        }
        $this->Booking->save($bookingData);
        $questId = $bookingData['Booking']['id_guest'];
        $ownerId = $bookingData['Booking']['id_owner'];
        $this->loadModel('Profile');
        $owner = $this->Profile->findByid_user($ownerId);
        $messageText = $owner['User']['first_name']." ".$owner['User']['last_name'][0]." wants to dicuss Your booking request<br />";
        if(isset($data['other_offer']) && strlen($data['other_offer']) > 0){
            if($data['other_offer'] <= 15){
                $this->Session->write('Note.error', "Please enter a valid rate amount greater than $15");
                $this->redirect('/bookings/hostCheckout/'.$bookingData['Booking']['id']);
            }else{
                $messageText.= $owner['User']['first_name']." has a new offer of ".$data['other_offer']."$ <br />";
            }
        }
        if($data['more_info'] == '1'){
            $messageText.= "also needs more information <br />";
        }
        if(isset($data['other_description']) && strlen($data['other_description']) > 0){
            $messageText.="with following message <br />";
            $messageText.= $data['other_description']." <br /> ";
        }
        $messageText.= "Please contact us if You have questions";
        $this->Mailer->mailAndMessageToUser($questId, $messageText);
        $this->Session->write('Note.ok', "You have answered to booking request");
        $this->redirect('/bookings/requests/');
    }
}
?>