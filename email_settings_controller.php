<?php
class EmailSettingsController extends AppController{
    public $components = array('Auth');
    var $name = 'EmailSettings';

    function index(){
        $this->layout = 'User_Master_Page';
        //handshake
        $u_id = $this->Auth->getUser();
        $this->set('u_id', $u_id);
        $settings = $this->EmailSetting->findByid_user($u_id);
        if(!empty($settings) && empty($this->data['EmailSetting'])){
            $this->data['EmailSetting'] = $settings;
        }elseif(!empty($settings) && !empty($this->data['EmailSetting'])){
            $this->data['EmailSetting']['id']=$settings['EmailSetting']['id'];
        }
        //update email settings
        if(!empty($this->data)){
            $this->EmailSetting->save($this->data['EmailSetting']);
            if(array_key_exists(0, $this->params['pass'])){
                if($this->params['pass'][0]	!= null){
                    echo '<SCRIPT>alert(\'Email settings saved\');</SCRIPT>';
                }
            }
        }
        $url='/users/settings';
        $this->redirect($url);
    }
}
?>