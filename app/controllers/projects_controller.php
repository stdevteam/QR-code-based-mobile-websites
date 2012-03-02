<?php

class ProjectsController extends AppController{
    public $name = 'Projects';
    public $components = array('Cookie','Email', 'Mailer','Auth','Dashboard','RequestHandler');
    
    public function view(){
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
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
       $projects = $this->Project->find('all',array('conditions' => array('userID' => $isloggined)));
       $this->set('projects',$projects);
    }
    public function add(){
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
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
            $this->data['Projects']['userID'] = $isloggined;
            //var_dump($this->data);die;
            if($this->data['Projects']['hidIp'] && $this->data['Projects']['hidIp'] != 0){
                $this->Project->id = $this->data['Projects']['hidIp'];
            }
            $this->Project->save($this->data['Projects']);
            $lastProject = $this->Project->Id;
            $this->Session->write('lastproject',$lastProject);
            $this->redirect('/projects/view');
        }
        
       
    }
    
     public function edit($idP){
         if(!$idP || $idP == 0){
             $this->redirect('/projects/view');
         } 
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
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
        $project = $this->Project->findById($idP);
       // var_dump($project);die;
        $this->data['Projects']['name'] = $project['Project']['name'];
        $this->data['Projects']['description'] = $project['Project']['description'];
        $this->data['Projects']['hidIp'] = $idP;
     }
     
     public function mobile($idP){
          if(!$idP || $idP == 0){
             $this->redirect('/projects/view');
         }
         $project = $this->Project->findByid($idP);
         $this->set('ProjectName',$project['Project']['name']);
        $this->set('projectId',$idP);
        $isloggined = $this->Auth->checkUser();
        $this->set('isLogedin',$isloggined);
        $this->layout = 'User_Master_Page';
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
        $this->loadModel('MobileSite');
        $have = $this->MobileSite->find('first', array('conditions' => array('userId' => $isloggined, 'projectId' => $idP)));

            if(!isset($this->data) || !is_array($this->data) || $this->data == ''){
                if($have){
                    $this->data['Projects']['fbPage'] = $have['MobileSite']['fbPage'];
                    $this->data['Projects']['twitPage'] = $have['MobileSite']['twitPage'];
                    $this->data['Projects']['phNumber'] = $have['MobileSite']['phNumber'];
                }
            }else{
                if($have){
                    $this->MobileSite->id = $have['MobileSite']['id'];
                }
                 $this->data['Projects']['userId'] = $isloggined;
                 $this->data['Projects']['projectId'] = $idP;
                 $this->MobileSite->save($this->data['Projects']);
            }
     }
    
}
?>
