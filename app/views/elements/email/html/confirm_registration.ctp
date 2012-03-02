<?php

/**
 * from:    noreply@dogvacay.com
 * bcc:     concierge@dogvacay.com
 * to:      $email
 * subject: Please confirm your email address to activate your account at DogVacay.com
 */

?>
<p>Hello <?php echo $contentForEmail['first_name']; ?></p>

<p>Thanks for joining Dog Vacay! To protect your privacy, please confirm your email address:</p>

<a href="<?php echo $url; ?>">CLICK HERE TO CONFIRM</a><br /><br />

<p>We look forward to seeing you on DogVacay.com.</p>

<p>Woof!</p>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
