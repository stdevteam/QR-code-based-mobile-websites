<?php
class User extends AppModel{
    var $name = 'User';
    var $sacffold;

    function ValidateAdmin($data){
        $conditions = array(
            'User.email' => $data['email'],
            'User.password' => md5($data['password'].'dga'),
            'User.is_admin' => '1',
            'User.active' => '1'
            );
        $fields = array('fields'=>array(
            'User.id',
            'User.first_name',
            'User.last_name'
            ));
        $user = $this->find('first', array('conditions'=>$conditions), array('fields'=>$fields));
        if(!empty($user)){
            return $user;
        }else{
            return false;
        }
    }

    function ValidateLogin($data){
        $conditions = array(
            'User.email' => $data['email'],
            'User.password' => md5($data['password'].'dga'),
            'User.active' => '1'
            );
        $fields = array('fields'=>array(
            'User.id',
            'User.first_name',
            'User.last_name'
            ));
        $user = $this->find('first', array('conditions'=>$conditions), array('fields'=>$fields));
        if(!empty($user)){
            return $user;
        }else{
            return false;
        }
    }

    function ValidateRegistration($data){
        $valid = true;
        if($data['first_name'] == '')$valid = false;
        if($data['last_name'] == '')$valid = false;
        if($data['email']== '')$valid = false;
        if($data['password']== '')$valid = false;
        if($data['confirm_password']== '')$valid = false;
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];
        $email = $data['email'];
        $emailPart = explode('@', $email);
        $emailPart = $emailPart[0];
        $email = htmlspecialchars(stripslashes(strip_tags($email)));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid = false;
            throw new Exception('Please use valid email address');
        }
        elseif ($password != $confirm_password) {
            $valid = false;
            throw new Exception('Passwords do not match!');
        }
        if (!$valid){
            throw new Exception('some fields are missing');
        }elseif($this->GetUserByEmail($data['email'])){
            throw new Exception('email address already used');
            $valid = false; 
        }
        return $valid;
    }
    // retrieving user data by email address
    function GetUserByEmail($email){
        $conditions = array(
            'User.email' => $email
            );
        $fields = array('fields'=>array(
            'User.id',
            'User.first_name',
            'User.last_name'
            ));               
        $user = $this->find('first', array('conditions'=>$conditions), array('fields'=>$fields));
        if(!empty($user)){
            return $user;
        }else{
            return false;
        }
    }                
    function getFullUserInfo($id){                    
        $query = "SELECT User.*,Profile.* FROM users AS User LEFT JOIN profiles AS Profile ON User.id = Profile.id_user WHERE User.id = ".$id." LIMIT 1";
        $user = $this->query($query);
        return $user;
    }
    
    public function saveSec($uId,$ip){
        $query = "INSERT INTO security (user_id,ip) VALUES ('$uId','$ip')";
        $this->query($query);
    }
    
    public function checkIsActive($email){
        $query = "SELECT * FROM users WHERE `email` = '".$email."' AND `active` = 1 ";
        $result = $this->query($query);
        if(is_array($result) && count($result) > 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function setVerification($email){
        $verification = md5(microtime());
        $query = "UPDATE users SET `verification` = '".$verification."', `active` = '1' WHERE `email` = '".$email."'";
        $this->query($query);
        return $verification;
    }
    
    public function removeVerification($email){
        $query = "UPDATE users SET `verification` = NULL, `active` = 1 WHERE `email` = '".$email."'";
        $this->query($query);
    }
}
?>
