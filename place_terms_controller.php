<?php
class PlaceTermsController extends AppController{
		public $name = 'PlaceTerms';
                public $components = array('Auth','Dashboard','Email');
    function Define(){
        $prices = $this->data['PlaceTerm'];
        //$this->layout = 'User_Master_Page';
        $this->layout = 'User_Account_Page';
        $p_id = '';
        $u_id = '';

        //load progress data
        $p_id = $this->Dashboard->getListingId();
        $place = $this->Dashboard->getData();
        /*$this->loadModel('Place');
        $place = $this->Place->findByid($p_id);*/
        $cDetails = json_decode($place['Place']['c_details']);
        $this->set('completeness', $place['Place']['completeness']);
        $this->set('cDetails', $cDetails);
        $this->set('title', $place['Place']['title']);
        $this->loadModel('PlacePhoto');
        $this->loadModel('Place');
        if($place['Place']['completeness']=='100' && !in_array('Requested',$cDetails) ){
            $cDetails[] = 'Requested';            
            $cDetails = json_encode($cDetails);
            $place['Place']['c_details'] = $cDetails;
            $this->Place->save($place);                        
            $this->loadModel('User');
            $user = $this->User->findByid($u_id);
            $email = $user['User']['email'];
            //send approval notification
            $this->Mailer->adminApproveListing($p_id,$email);
            //send approval notification
            $this->Dashboard->cleanUp();
            $this->redirect('/places/afterlisting');
        }
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
        $u_id = $this->Auth->getUser();

        $detail = $this->PlaceTerm->query('SELECT places.id, places.title FROM places WHERE places.id = '.$p_id);
        $this->set('v_property_id', $p_id);
        $this->set('v_title', $detail[0]['places']['title']);
        //validating prices to check if they are decimal and moving on			
        try {
            if($prices){
                $this->ValidatePrices($prices);
                if(!empty($this->data)){
                    // prices not being saved in db //Now working :D
                    $this->data['PlaceTerm']['id_place'] = $p_id;
                    $this->data['PlaceTerm']['id_user'] = $u_id;
                    $this->PlaceTerm->save($this->data);
                    
                    //updating listing modified field
                    $this->Dashboard->updateListing();
                    //cleaning dasboard
                    $this->Dashboard->cleanUp();
                    $saved = true;
                    $this->set('saved', $saved);
                    //update overall progress bar                                                        
                    $this->loadModel('Place');
                    $place = $this->Place->findByid($p_id);
                    $cDetails = json_decode($place['Place']['c_details']);
                    if(!in_array('Prices', $cDetails)){
                        $cDetails[] = 'Prices';
                        $place['Place']['completeness'] = count($cDetails)*20;
                        $cDetails = json_encode($cDetails);
                        $place['Place']['c_details'] = $cDetails;
                        $this->Place->save($place);
                        $this->Dashboard->cleanUp();
                        if($place['Place']['completeness']=='100'){
                            $this->redirect('/places/afterlisting');
                        }else{
                            $this->redirect('/place_terms/Define/');
                        }
                    }elseif(!in_array('Requested', $cDetails)){
                        if($place['Place']['completeness']=='100'){
                            $this->redirect('/places/afterlisting');
                        }
                    }else{
                        if($place['Place']['completeness']=='100'){
                            $this->redirect('/place_terms/Define/');
                        }
                    }                  
                }
            }else{
                $this->data = $this->PlaceTerm->findByid_place($p_id);
                $saved = true;
                $this->set('saved', $saved);                
            }		
        }catch(Exception $error){
            $message = $error->getMessage();
            $this->Session->write('Note.error', "$message");            
        }
    }		
		
    // checking inputed prices ..
    protected function ValidatePrices($prices){
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
}
?>
