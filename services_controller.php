<?php
    class ServicesController extends AppController{
        public $name = 'Services';
        public $components = array(
                    'Auth','Dashboard'            
            );
        
        public function MyServices(){
            $u_id = $this->Auth->GetUser();
            $userData = $this->Dashboard->getData();
            $this->data = $userData;
            $no_ser = $this->checkServices($userData, true);
            $this->set('no_ser',(!$no_ser)?'checked':'');
            
             $this->data['Service']['user_id'] = $u_id;
                    $this->Service->save($this->data); 
                    //$SerId = $this->Service->id;
                    $this->Dashboard->updateListing();
            $this->SaveServices($this->data);
        }
        protected function checkServices($userData,$return = null) { 
            $data = $userData['Service'];
            
            if($return){
            if ( $data['walking_rate'] != 0 || $data['walking_radius'] != 0 || $data['walking_bulk'] != 0 
                || $data['other'] != 0 || $data['idb'] != 0 || $data['pfa'] != 0 ) {
                return true;
                }else{
                    return false;
                }
            }

        }
        public function SaveServices(){
            $u_id = $this->Auth->GetUser();
            var_dump($this->data);die;
            if(!$this->data){                
                $current = $this->Service->findByuser_id($u_id);
                $toSave = array_merge($current['Service'],$this->data['Service']);
                $this->data['Service'] = $toSave;
                
                $this->checkServices($this->data);
                $this->Profile->save($this->data);                
            }
        }
    }
?>
