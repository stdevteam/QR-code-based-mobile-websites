<?php

/**
 * from:    noreply@dogvacay.com
 * bcc:     concierge@dogvacay.com
 * to:      $email
 * subject: [Dog Vacay] $subject
 */

?>
<p>Hello <?php echo $user_name; ?></p>

<p>You have a new message from <?php echo $sender_name; ?>!</p>

<div><?php echo $text; ?></div>

<p>Reply: <?php echo FULL_BASE_URL; ?>/messages/inbox</p>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
