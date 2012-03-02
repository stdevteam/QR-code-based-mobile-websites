<?php
class CheckComponent extends Object{
    public function initialize(&$controller, $settings = array()){
        $this->controller =& $controller;
        $this->_set($settings);
    }
    /**
     * Checks for emails, phone numbers or websites within the text specified and 
     * returns true only if none of them was found
     * @param int $to Recipient user id
     * @param int $from Sender user id
     * @param string $text The text to process
     * @return bool Returns true if no invalid data is found, false otherwise
     */
    public function checker($to, $from, $text=null){
        return true;

        //check whether there is after meet and greet message sent or not
        $this->controller->loadModel('Thread');
        if(!$this->controller->Thread->isMagSent($to, $from)){
            //check and replace for exepting email's, phone numbers anw websites
            $originalText = $text;
            $patterns = array();
            $patterns[] = '/[a-zA-Z0-9._-]+(\s+|-)?+\@+(\s+|-)?+[a-zA-Z0-9]+(\s+|-)?+(\.|dot)+(\s+|-)?+[a-zA-Z.]{1,5}/';
            $patterns[] = '/((((ht|f)tp(s?)):\/\/){1}+\S+)/';
            $patterns[] = '/((www\.){1}+\S+)/';
            $patterns[] = '/[a-zA-Z0-9._-]+(\.|dot)+[a-zA-Z.]{1,3}/';
            $patterns[] = '/((\(\d{3}\))|(\d{3}-))\d{3}-\d{4}/';
            $patterns[] = '/(( (\()?+ \d{3,4} (\))?+ ) [\s-.+\/\\\\]?+ ) \d{3,4} [\s-.+\/\\\\]?+ \d{3,4}/x';
            $patterns[] = '/\d{5,}/';
            $patterns[] = '/(\+)?+\d{1,2} [\s-.+\/\\\\]?+ \d{2,4} [\s-.+\/\\\\]?+ \d{2,4} ([\s-.+\/\\\\]?+ \d{2,4})?+ ([\s-.+\/\\\\]?+ \d{2,4})?+ /x';
            $replaceTo = ' [you add unresolved simbol] ';
            $newText = preg_replace($patterns,$replaceTo,$text);
            if($newText != $originalText){
                return false;
            }else{
                return true;
             }
        }else{
            return true;
        }
    }
    
    public function filterText($to, $from, $text = null){
        return $text; 

        //check whether there is after meet and greet message sent or not
        $this->controller->loadModel('Thread');
        if(!$this->controller->Thread->isMagSent($to, $from)){
            //check and replace for exepting email's, phone numbers anw websites
            $originalText = $text;
            
            $emailPatters = array();
            $emailPatters[] = '/[a-zA-Z0-9._-]+(\s+|-)?+\@+(\s+|-)?+[a-zA-Z0-9]+(\s+|-)?+(\.|dot)+(\s+|-)?+[a-zA-Z.]{1,5}/';
            $emailReplace = ' [email address removed for privacy] ';
            
            $numberPatterns = array();
            $numberPatterns[] = '/((\(\d{3}\))|(\d{3}-))\d{3}-\d{4}/';
            $numberPatterns[] = '/(( (\()?+ \d{3,4} (\))?+ ) [\s-.+\/\\\\]?+ ) \d{3,4} [\s-.+\/\\\\]?+ \d{3,4}/x';
            $numberPatterns[] = '/\d{5,}/';
            $numberPatterns[] = '/(\+)?+\d{1,2} [\s-.+\/\\\\]?+ \d{2,4} [\s-.+\/\\\\]?+ \d{2,4} ([\s-.+\/\\\\]?+ \d{2,4})?+ ([\s-.+\/\\\\]?+ \d{2,4})?+ /x';
            $numberReplace = ' [phone number removed for privacy] ';
            
            $urlPatterns = array();
            $urlPatterns[] = '/((((ht|f)tp(s?)):\/\/){1}+\S+)/';
            $urlPatterns[] = '/((www.){1}+\S+)/';
            $urlPatterns[] = '/[a-zA-Z0-9._-]+(\.|dot)+[a-zA-Z.]{1,3}/';
            $urlReplace = ' [website removed for privacy] ';
            
            $text = preg_replace($emailPatters, $emailReplace, $text);
            $text = preg_replace($numberPatterns, $numberReplace, $text);
            $text = preg_replace($urlPatterns, $urlReplace, $text);
            
            return $text;
        }else{
            return $text;
        }
    }
    public function checkPhone($u_ph){
           if(preg_match("/^(?:[-0-9 ]*|[-0-9 ]*\([-0-9 ]*\)[-0-9 ]*)$/", $u_ph) == 0){
             
           return false;
       }else{
           return true;
       }
      
    }

}
