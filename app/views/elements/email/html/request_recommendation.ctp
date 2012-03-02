<?php

/**
 * from:    concierge@dogvacay.com
 * bcc:     concierge@dogvacay.com
 * to:      $email
 * subject: Invitation to review $sender on DogVacay.com
 */

?>
<?php if(isset($custom_message)){
    echo 'Message from '.$sender_name.'<br /><i>'.$custom_message.'</i><br /><br />';
} ?>
<?php if(isset($receiver_name)){
    echo $receiver_name.',<br /><br />';
} ?>

<p><?php echo $sender_name; ?> is using Dog Vacay to build their business and
has indicated that you have worked with them before. Please take a moment to
tell us about your experience by <a href="<?php echo $url; ?>"><u>completing a
short review</u></a>–it should take less than 1 minute.</p>

<p>Reviews are important for developing the reputation of our hosts – please be
as accurate as possible so that dog owners can make the best decisions about
caring for their loved ones.</p>

<p>For more information on Dog Vacay or to sign up for a free account please visit us at:</p>

<br /><br /><a href="<?php echo FULL_BASE_URL; ?>/?utm_source=Host&utm_medium=Email&utm_campaign=Review%2Brequest">DogVacay.com</a>
<br /><br /><a href="<?php echo $url; ?>"><span style=" font-size: 22px; font-weight: bold">Write Review</span></a>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
