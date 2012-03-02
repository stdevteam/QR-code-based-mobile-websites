<?php

/**
 * from:    concierge@dogvacay.com
 * bcc:      
 * to:      $email
 * subject: Password recovery information for DogVacay.com
 */

?>
<p>Hello <?php echo $user['User']['first_name']; ?></p>

<p>You have requested password recovery from Dog Vacay. Contact 
<a href="<?php echo FULL_BASE_URL; ?>/contents/contact">Dog Vacay Support</a> 
if this request was not authorized.</p>

<p>To recover Your password visit this 
<a href="<?php echo FULL_BASE_URL; ?>/users/password/<?php echo $user['User']['verification']; ?>">link</a></p>

<p>Or just copy and paste this URL in Your browser's address bar 
<?php echo FULL_BASE_URL; ?>/users/password/<?php echo $user['User']['verification']; ?></p>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
