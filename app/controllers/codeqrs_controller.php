<?php

class CodeqrsController extends AppController{
    public $name = 'Codeqrs';
    public $components = array('Cookie','Auth','Dashboard','QrCode');
    
    public function view($idProject){
         if(!$idProject || $idProject == 0){
             $this->redirect('/projects/view');
         } 
           
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
        $this->loadModel('Project');
        $lastProject = $this->Dashboard->lastProject();
        if(isset($lastProject) && $lastProject != 0){
            $this->set('lastProject',$lastProject);
        }else{
            $prjs = $this->Project->find('first',array('conditions' => array('userID' => $isloggined), 'order' => array('id DESC')));
            if($prjs && $prjs != '' && is_array($prjs)){
                $this->set('lastProject',$prjs['Project']['id']);
            }else{
                $this->set('lastProject',false);
            }
        }
        $project = $this->Project->findByid($idProject);
        $this->set('projectName',$project['Project']['name']);
        $qrCodes = $this->Codeqr->find('all',array('conditions' => array(
            'projectId' => $idProject,
            'userId' => $isloggined,
        )));
        $this->set('qrCodes',$qrCodes);
        $this->set('idProject',$idProject);
        
    }
    public function add($idProject){
         if(!$idProject || $idProject == 0){
             $this->redirect('/projects/view');
         } 
         $this->set('idProject',$idProject);
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
         $this->loadModel('Project');
        $lastProject = $this->Dashboard->lastProject();
        if(isset($lastProject) && $lastProject != 0){
            $this->set('lastProject',$lastProject);
        }else{
            $prjs = $this->Project->find('first',array('conditions' => array('userID' => $isloggined), 'order' => array('id DESC')));
            if($prjs && $prjs != '' && is_array($prjs)){
                $this->set('lastProject',$prjs['Project']['id']);
            }else{
                $this->set('lastProject',false);
            }
        }
        if($this->data){
            $serialNum = $this->data['Codeqrs']['serialNo'];
            $uniq = $this->Codeqr->findBySerialno($serialNum);
           
            if($uniq || $serialNum == '' || $this->data['Codeqrs']['location'] == ''){
                $this->redirect('/codeqrs/add/'.$idProject);
            }else{
                $location = $this->data['Codeqrs']['location'];
                $mergeSn = $idProject.$isloggined.$serialNum;
                $hash = md5($mergeSn);
                $shortUrl = substr($hash,0,10);
                $this->data['Codeqrs']['projectId'] = $idProject;
                $this->data['Codeqrs']['userId'] = $isloggined;
                $this->data['Codeqrs']['shortUrl'] = $shortUrl;
                $this->Codeqr->save($this->data['Codeqrs']);
                $idQr = $this->Codeqr->id;
                $this->data['Codeqrs']['imagePath'] = $idQr.".png";
                $text = FULL_BASE_URL."/".$shortUrl;
                $path = WWW_ROOT.'/img/qrcodes/'.$idQr.".png";
                $this->QrCode->generate($text,$path);
                $this->Codeqr->save($this->data['Codeqrs']);
                $this->redirect('/codeqrs/view/'.$idProject);
            }
        }
        
       
    }
}