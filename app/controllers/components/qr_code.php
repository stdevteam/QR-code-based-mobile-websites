<?php 
App::import('Vendor', 'phpqrcode', array('file' => 'phpqrcode.php'));

class QrCodeComponent extends Object {
 
    public function generate($text,$path){
        return   QRCode::png($text,$path);
    }
}
?>