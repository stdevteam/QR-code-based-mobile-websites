<?php
	class paypalsController extends AppController{
		var $name = 'Paypals';
		
		function index(){
			$this->layout = 'User_Master_Page';
			//handshake
			$u_id = $this->Auth->getUser();
			$accounts = $this->Paypal->findByid_user($u_id);
                        if(!empty($accounts)){
				$this->set('accounts', $accounts);
			}
			
			if(!empty($this->data)){
                                if($this->data['Paypal']['type'] == 'check'){
                                   $this->data['Paypal']['check'] = 1;
                                   $this->data['Paypal']['paypal'] = $accounts['Paypal']['paypal'];
                                }
                                unset($this->data['Paypal']['type']);
                                $this->data['Paypal']['id'] = $accounts['Paypal']['id'];
				$this->Paypal->save($this->data);
                                $this->redirect('/paypals/index');
			}
			
		}
                
                function history(){
                    $this->layout = 'User_Master_Page';
                }
	}
?>