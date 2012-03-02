<?php 
//get the Anet vendor
App::import('Vendor', 'anet_php_sdk', array('file' => 'AuthorizeNet.php'));

/**
 * Component that handles all the Authorize.net related stuff
 */
class AuthorizeNetComponent extends Object {
    /**
     * True if an error was encountered while request, false otherwise
     * @var bool
     */
    public $error = false;
    /**
     * Response code provided upon request 
     * @var string
     */
    public $responseCode = null;
    /**
     * The text that explains the reason of transaction result, will hold error message on failure
     * @var string
     */
    public $responseText = null;


    public function startup(&$controller) {
        //initialize the constants needed by Authrize.net SDK
        define("AUTHORIZENET_API_LOGIN_ID", Configure::read('AUTHORIZE_LOGIN_ID'));
        define("AUTHORIZENET_TRANSACTION_KEY", Configure::read('AUTHORIZE_TRANSACTION_KEY'));
        define("AUTHORIZENET_SANDBOX", !Configure::read('PAYMENT_LIVE'));
    }
    
    public function initialize(&$controller) {}
    /**
     * Method to authorize funds on user's credit card to be captured later
     * @param float $amount The amount to authorize
     * @param int $cardNum The number of the card
     * @param int $cardCode Security code of the card 3 or 4 digits
     * @param int $expMonth Card expiration month
     * @param int $expYear Card expiration year
     * @return string|null Returns the transaction id if the request was approved, null otherwise.
     * If null is teruned use $component->responseCode and $component->responseText to find the problem, 
     * the latter one will hold error message
     */
    public function authorizeFunds($amount, $cardNum, $cardCode, $expMonth, $expYear){
        $auth = new AuthorizeNetAIM;
        //set the amount
        $auth->amount = number_format($amount, 2);

        //set Invoice Number:
        $auth->invoice_num = time();
        //set card number
        $auth->card_num = $cardNum;
        //set card expiration date
        $auth->exp_date = $this->_getExpirationDate($expMonth, $expYear);
        //set card security code
        $auth->card_code = $cardCode;

        // Authorize Only:
        $response  = $auth->authorizeOnly();
        
        //set the response code and text
        $this->responseCode = $response->response_code;
        $this->responseText = $response->response_reason_text;

        if($response->approved){
            $auth_code = $response->transaction_id;
            if(AUTHORIZENET_SANDBOX){
                //in sandbox mode transaction id is always 0, so assign a random value
                $auth_code = md5(microtime());
            }
            return $auth_code;
        }else{
            return null;
        }
    }
    /**
     * Given a transaction id chargs a card prevoisly authorized for money presence
     * @param string $transactionId The transaction id that was returned after funds authorization
     * @param float $amount [optional] ONLY give this param if amount of money is lesser 
     * than the orignial amount authorized
     * @return string|null Returns the transaction id on success or null otherwise
     */
    public function chargeCard($transactionId, $amount = false){
        if(AUTHORIZENET_SANDBOX){
            //in sandbox mode transaction id is always 0, so assign wathever has passed change it to 0
            $transactionId = "0";
        }

        // Now capture:
        $capture = new AuthorizeNetAIM;
        $capture_response = $capture->priorAuthCapture($transactionId, $amount);
        
        //set the response code and text
        $this->responseCode = $capture_response->response_code;
        $this->responseText = $capture_response->response_reason_text;

        if($capture_response->approved){
            $auth_code = $capture_response->transaction_id;
            if(AUTHORIZENET_SANDBOX){
                //in sandbox mode transaction id is always 0, so assign a random value
                $auth_code = md5(microtime());
            }
            return $auth_code;
        }else{
            return null;
        }
    }
    /**
     * Cancels a funds authorization  by transaction id 
     * (if payment already made this method is no longer applicable)
     * @param string $transactionId The transaction id to void
     */
    public function cancelPayment($transactionId){
        if(AUTHORIZENET_SANDBOX){
            //in sandbox mode transaction id is always 0, so assign wathever has passed change it to 0
            $transactionId = "0";
        }
        
        // Now void:
        $void = new AuthorizeNetAIM;
        $void_response = $void->void($transactionId);
        
        if($void_response->approved){
            //check weather a transaction id is returned once we move to live
            /*$auth_code = $void_response->transaction_id;
            if(AUTHORIZENET_SANDBOX){
                //in sandbox mode transaction id is always 0, so assign a random value
                $auth_code = md5(microtime());
            }*/
            return true;
        }else{
            if(AUTHORIZENET_SANDBOX){
                //in sandbox mode transaction id is always 0, so we have no valid transaction id, always return true
                return true;
            }
            
            return false;
        }
    }
    /**
     * Formats and returns the expiration date of card to suite Authorize.net
     * @param int $month The expiration month
     * @param int $year The expiration year
     * @return string The expiration string
     */
    protected function _getExpirationDate($month, $year){
        $expiration = '04/15';
        $expiration = "{$month}/";
        if(strlen($year) == 4){
            $year = substr($year, -2);
        }
        $expiration .= $year;
        
        return $expiration;
    }
}
?>