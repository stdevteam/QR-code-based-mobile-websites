<?php

class OffersController extends AppController{

    var $components = array('Email','Dashboard','Auth');
    
    public function save(){
        $data=$this->params['form'];
        $data['from_id']=$this->Auth->getUser();
        $this->loadModel('Place');
        $listing=$this->Place->findByid_user($data['from_id']);
        $data['listing_id']=$listing['Place']['id'];
        $start_date=explode('/',$data['start_date']);
        $data['start_date']=$start_date[2].'-'.$start_date[0].'-'.$start_date[1];
        $this->Offer->save($data);
        $this->loadModel('Thread');
        $this->Thread->addOffer($data['t_id']);
        $this->Session->write('MessageSent','offer');
        echo $this->Offer->id;        
        exit();
    }
}
?>
