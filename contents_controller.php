<?php
class ContentsController extends AppController {

    public $components = array('Email','Mailer', 'Geocoder');
    public $name = 'Contents';

    public function home() {
        $address = 'Enter City or Zip';
        $ip = $this->Geocoder->ip_lookup($_SERVER['REMOTE_ADDR']);
        if (!empty($ip['geoplugin_city']) && !empty($ip['geoplugin_region'])) {
            $address = $ip['geoplugin_city'] . ', ' . $ip['geoplugin_region'];
        }

        $this->layout = 'blank_for_test';
        $id = $this->Content->findPageId('Home');
        if ($id) {
            $content = $this->Content->findArticlesForPage($id['Content']['id']);
            $this->set('contentForHomePage', $content);
        } else {
            $this->set('contentForHomePage', false);
        }
        /* $sliderItems = $this->Content->find('all',array(
          'conditions' => array('type' => 'slider','status' => 'published',),
          'fields' => array('slider_text','image','listing_id','badge'),
          'limit' => 6
          )); */
        /*  $sql = "SELECT contents.image,contents.slider_text,contents.badge,contents.listing_id,places.title,place_terms.nigthly_rates FROM contents  
          LEFT JOIN places ON places.id = contents.listing_id
          LEFT JOIN place_terms ON place_terms.id_place = places.id
          WHERE contents.type = 'slider'
          AND contents.status = 'published'
          AND places.approved = 'yes'
          ORDER BY places.completeness ASC
          LIMIT 6";
         * 
         */
        $sliderItems = $this->Content->sliderItems();
        $this->set('sliderItems', $sliderItems);
        $this->set('geo_lookup', $address);
    }

    public function about() {
        $this->layout = 'User_Master_Page';
        $id = $this->Content->findPageId('About');
        $content = $this->Content->findArticlesForPage($id['Content']['id']);
        $this->set('content', $content);
        
        //set the meta
        $this->_setMetaData(
            'DogVacay is a growing community of dog lovers. 
             We\'re based in Southern California but will be expanding nationally. 
             Join our community mailing list.', 
             'DogVacay, dog lovers community'
        );
    }

    public function contact() {
        $this->layout = 'User_Master_Page';
        $id = $this->Content->findPageId('Contact');
        $content = $this->Content->findArticlesForPage($id['Content']['id']);
        foreach ($content as $key => $item) {
            if ($this->Content->hasChild($item['articles']['id'])) {
                $content[$key]['articles']['children'] = $this->Content->findArticlesForParent($item['articles']['id']);
                foreach ($content[$key]['articles']['children'] as $index => $article) {
                    if ($this->Content->hasChild($article['articles']['id'])) {
                        $content[$key]['articles']['children'][$index]['articles']['children'] = $this->Content->findArticlesForParent($article['articles']['id']);
                        foreach($content[$key]['articles']['children'][$index]['articles']['children'] as $itemId => $item){
                            $content[$key]['articles']['children'][$index]['articles']['children'][$itemId]['articles']['text'] = str_ireplace('[dogvacay_fee]', Configure::read('dogVacayFee'), $content[$key]['articles']['children'][$index]['articles']['children'][$itemId]['articles']['text']);
                            //var_dump($content[$key]['articles']['children'][$index]['articles']['children'][$key]['articles']['text']);die;
                        }
                    }
                }
            }
        }
        $this->set('content', $content);
        
        //set the meta
        $this->_setMetaData(
            'Need some assistance? Check out our FAQs, pick up the phone, or send us an email.', 
            'contact us, send an email to DogVacay'
        );
    }

    public function help() {
        $this->layout = 'User_Master_Page';
    }

    public function TermsAndPrivacy() {
        $this->layout = 'User_Master_Page';
        
        $this->_setMetaData(
            'Dog Vacay provides an online platform that connects dog owners with dog lovers who have 
             selected to host dogs in their home, and/or offer other dog related services.', 
            'privacy, DogVacay terms, conditions'
        );
    }

    public function pages() {
        
    }

    public function email() {
        $error = false;
        $message = false;
        if (!empty($this->data)) {
            $email = $this->data['Content']['email'];
            $email = htmlspecialchars(stripslashes(strip_tags($email)));
            if ( filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid =  true;
            } else {
                $valid = false; //if email address is an invalid format return false
            }
            if($valid){
                $existing = $this->Content->query("SELECT * FROM email_capture WHERE `email` = '" . $email . "' AND `referer` = 'about' ");
                if (count($existing) == 0) {
                    $result = $this->Content->query("INSERT INTO email_capture (`email`, `capture_at`, `referer`) VALUES('" . $email . "','" . date('Y-m-d H:i:s') . "', 'about' )");
                    if ($result) {
                        if ($this->Mailer->aboutPageEmail($email)) {
                            $message = "Your email address successfully added , Thank You";
                        } else {
                            $error = "We can't send an email at this time, please try again later";
                        }
                    } else {
                        $error = "Please try again later";
                    }
                } else {
                    $error = "Your email address already added to our database";
                }
            }else{
                $error = "Please fill in valid email address";
            }
        }
        if ($error) {
            $this->Session->write('error', $error);
        }
        if ($message) {
            $this->Session->write('message', $message);
        }

        $this->redirect('/contents/About');
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
    
    function setCookie(){
        if(!isset($_COOKIE['beta'])){
            setcookie("beta", "yes_new", (time()+31104000), '/', ".dogvacay.com", false);
        }
        die;
        
    }
}
?>
