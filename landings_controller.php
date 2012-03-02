<?php

class LandingsController extends AppController
{

    var $uses = null;
    public $components = array('Mailer');

    public function index($page = 'index') {
        // set the page first, so we have an accurate record of what URL was ATTEMPTED
        $page = (255 > strlen($page))
                ? $page
                : '';
        $this->set('referer', $page);

        // check if the actual page template exists
        if (!file_exists(VIEWS . 'landings' . DS . strtolower($page) . '.ctp')) {
            $page = 'index';
        }
        $this->render($page);
    }

    public function submit() {
        // default error response
        $response = "Something's not right. Please try again, thanks!";

        if (isset($this->data)) {
            // store the form data
            $info = array(
                    'email' => $this->data['email'],
                    'capture_at' => date('Y-m-d H:i:s'),
                    'referer' => $this->data['referer'],
                    'json' => json_encode($this->data),
                    );
            $this->loadModel('CaptureData');
            $this->CaptureData->set($info);

            // send feedback to the user if all is well
            if ($this->CaptureData->save()) {
                $response = "We've received your request for information. Thank you!";
            }
            else {
                foreach ($this->CaptureData->validationErrors as $field => $error) {
                    $response .= '\\n' . $error;
                }
            }
        }

        // write response feedback to the user
        $this->Session->setFlash($response, 'alert');
        $this->redirect(array('action' => 'index', $this->data['referer']));
    }
    
    public function emailCollector(){
        if(!empty($this->data)){
            $email = $this->data['email_capture']['email'];
            if(trim($email) != ''){
                
                $this->data['email_capture']['referer'] = 'home'; 
                $this->loadModel('CaptureData');
                $this->CaptureData->set($this->data['email_capture']);
                
                $exist = $this->CaptureData->findByemail($email);
                $response = "We've received your request for information. Thank you!";
                if($exist){
                    setcookie("beta", "yes_new", (time()+31104000), '/', ".dogvacay.com", false);
                }elseif($this->CaptureData->save()){
                    setcookie("beta", "yes_new", (time()+31104000), '/', ".dogvacay.com", false);
                    $this->Mailer->aboutPageEmail($email);
                }else{
                    $response = '';
                    foreach ($this->CaptureData->validationErrors as $field => $error) {
                        $response .= $error . '\\n';
                    }
                }                
            }else{
                //$this->Session->write('Note.error','Please insert an email address');
                //$this->redirect('/');
                $response = 'Please insert an email address';
            }
            $this->Session->setFlash($response, 'alert');
            $this->redirect('/');
        }        
    }
    
    public function subscribe(){
        $response = 'Please insert an email address';
        if(!empty($this->data)){
            $email = $this->data['Content']['email'];
            if(trim($email) != ''){
                
                $this->data['Content']['referer'] = 'about'; 
                $this->loadModel('CaptureData');
                $this->CaptureData->set($this->data['Content']);
                $exist = $this->CaptureData->findByemail($email);
                $response = "We've received your request for information. Thank you!";
                if(!$exist){
                    if($this->CaptureData->save()){
                        $this->Mailer->aboutPageEmail($email);
                    }else{
                        $response = '';
                        foreach ($this->CaptureData->validationErrors as $field => $error) {
                            $response .= $error . '\\n';
                        }
                    }
                }
            }
        }
        $this->Session->setFlash($response, 'alert');
        $this->redirect('/contents/About/');
    }
    
    

}
