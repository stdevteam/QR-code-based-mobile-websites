<?php
class FileUploaderComponent extends Object{
    /**
     * Reference to current controller
     * @var AppController
     */
    public $controller;
    /**
     * An array of allowed extensions
     * @var array
     */
    protected $_allowedExtensions;
    /**
     * A file size limit in bytes
     * @var int
     */
    protected $_sizeLimit;
    /**
     * File data
     * @var array 
     */
    protected $_file;
    protected $_errors = array();

    /**
     * Component statup
     * @param AppController $controller Reference to current controller
     * @param array $settings An array of settings
     */
    public function initialize(&$controller, $settings = null){
        $this->controller=&$controller;
        if(!is_null($settings) && is_array($settings)){
            if(isset($settings['ext']) && is_array($settings['ext'])){
                $this->setAllowedExtensions($settings['ext']);
            }else{
                $this->setAllowedExtensions(array('pdf'));
            }
            if(isset($settings['limit']) && is_integer($settings['limit'])){
                $this->setSizeLimit($settings['limit']);
            }else{
                $this->setSizeLimit(5242880);//5mb
            }
        }
    }
    /**
     * Processes uploaded file and moves it to disk
     * @param string $uploadPath The path to upload to
     * @return string A json encoded string to be sent in response to ajax call
     */
    public function upload($uploadPath){
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = $this->getAllowedExtensions();
        // max file size in bytes
        $sizeLimit = $this->getSizeLimit();

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($uploadPath);
        // to pass data through iframe you will need to encode all html tags
        return $result;
    }
    /**
     * Uploads a file from $_FILES array and returns its name saved on disk
     * @param string $model The model the file is uploaded for
     * @param string $name The file input name
     * @param string $uploadDirectory The path to upload file to
     * @return string The file for saved file 
     * 
     */
    public function uploadFromFiles($model, $name, $uploadDirectory){
        $this->_errors = array();
        
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = $this->getAllowedExtensions();
        // max file size in bytes
        $sizeLimit = $this->getSizeLimit();
       
        $file = $this->_getFileData($model, $name);
        if(is_null($file)){
            return '';
        }
        $this->_file = $file;
        
        //process validation and upload
        if(!is_writable($uploadDirectory)){
            $this->_errors['Server error. Upload directory is not writable.'];
            return '';
        }
        
        $size = $this->_file['size'];
        
        if($size == 0){
            $this->_errors['File is empty'];
            return '';
        }
        
        if($size > $this->getSizeLimit()){
            $this->_errors['File is too large'];
            return '';
        }
        
        $pathinfo = pathinfo($this->_file['name']);
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if(!in_array(strtolower($ext), $this->getAllowedExtensions())){
            $these = implode(', ', $this->getAllowedExtensions());
            $this->_errors['File has an invalid extension, it should be one of '. $these . '.'];
            return '';
        }
        
        
        /// don't overwrite previous files that were uploaded

            
        
        if(move_uploaded_file($this->_file['tmp_name'], $uploadDirectory . $filename . '.' . $ext)){
            return $filename.'.'.$ext;
        }else{
            $this->_errors['Could not save uploaded file.' .'The upload was cancelled, or server error encountered'];
            return '';
        }        
    }
    /**
     * Outputs the response of file upload processing
     * @param array $response An array containing the response
     */
    public function outputResponse($response){
        echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
    }
    
    
    /**
     * Returns a list of allowed extensions
     * @return array
     */
    public function getAllowedExtensions(){
        return $this->_allowedExtensions;
    }
    /**
     * Sets the list of allowed extensions
     * @param array $allowedExtensions
     * @return FileUploaderComponent Returns itself for method chaining
     */
    public function setAllowedExtensions($allowedExtensions){
        $this->_allowedExtensions = $allowedExtensions;
        return $this;
    }

    /**
     * Returns file size limit in bytes
     * @return int
     */
    public function getSizeLimit() {
        return $this->_sizeLimit;
    }
    /**
     * Sets the file size limit in bytes
     * @param int $sizeLimit 
     * @return FileUploaderComponent Returns itself for method chaining
     */
    public function setSizeLimit($sizeLimit){
        $this->_sizeLimit = $sizeLimit;
        return $this;
    }
    
    /**
     * Gets an array errors or an empty array if no error occured
     * @return array
     */
    public function getErrors(){
        return $this->_errors;
    }
    /**
     * Sets the errors
     * @param array $errors 
     */
    public function setErrors($errors){
        $this->_errors = $errors;
    }

        
    /**
     * Returns info about a particular files
     * @param string $model The model name
     * @param string $name The name of file input
     * @return array|null An array of data or null if no data found
     */
    protected function _getFileData($model, $name){
        $result = array();
        if(
            !isset($_FILES['data']) || !isset($_FILES['data']['name']) || 
            !isset($_FILES['data']['name'][$model][$name])
        ){
            return null;
        }
        
        if($_FILES['data']['name'][$model][$name]){
            $result['name'] = $_FILES['data']['name'][$model][$name];
        }else{
            return null;
        }
        if($_FILES['data']['type'][$model][$name]){
            $result['type'] = $_FILES['data']['type'][$model][$name];
        }else{
            return null;
        }
        if($_FILES['data']['tmp_name'][$model][$name]){
            $result['tmp_name'] = $_FILES['data']['tmp_name'][$model][$name];
        }else{
            return null;
        }
        if(!empty($_FILES['data']['error'][$model][$name]) || $_FILES['data']['error'][$model][$name] === 0){
            $result['error'] = $_FILES['data']['error'][$model][$name];
        }else{
            return null;
        }
        if($_FILES['data']['size'][$model][$name]){
            $result['size'] = $_FILES['data']['size'][$model][$name];
        }else{
            return null;
        }
        
        return $result;
    }
}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if(isset($_GET['qqfile'])){
            $this->file = new qqUploadedFileXhr();
        }elseif (isset($_FILES['qqfile'])){
            $this->file = new qqUploadedFileForm();
        }else{
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array(
                'success'=>true,
                'filename' => $filename.'.'.$ext,
            );
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}
?>