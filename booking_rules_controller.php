<?php
class BookingRulesController extends AppController{
    public $name = 'BookingRules';
    public function MyRules(){
        $this->layout = 'User_Master_Page';
        //handshake
        $u_id = '';
        if($this->Session->read('User.id')==null){
            $this->redirect('/users/login');
        }else{
            $u_id = $this->Session->read('User.id');
        }

        //set for the view
        $this->set('u_id', $u_id);

        //retrieve if the user have already defined the rules
        $MyRules = $this->BookingRule->findByid_user($u_id);

        //if so load those to data
        if(!empty($MyRules)){
            $this->data = $MyRules;
        }else{
            //if rules are not defined save them
            if(!empty($this->data)){
                    $this->BookingRule->save($this->data);
            }
        }
    }
}
?>