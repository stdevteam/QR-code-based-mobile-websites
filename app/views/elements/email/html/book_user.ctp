<?php

/**
 * from:    noreply@dogvacay.com
 * bcc:     concierge@dogvacay.com
 * to:      $email
 * subject: Your booking information from DogVacay.com
 */

?>
<p>Hello <?php echo $user_name; ?></p>

<p>Congratulations on finding a real home for your dog to stay while you're
away.  <?php echo $puppy_name; ?> thanks you! Please review your booking and
contact your host, <?php echo $host_name; ?>, to finalize the details of your
dog's stay. If you'd like, you may also arrange for an informal meeting in
advance.  As always, if you have any questions you can reach us at
help@dogvacay.com or 888-681-DOGS.</p>

<p>Thanks for using DogVacay.com!</p>
     
<div>
<p>Boarding Details</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Order Number: <?php echo $order; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Drop-Off: <?php echo $drop_date; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Pick-Up: <?php echo $pick_date; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Dogs: <?php echo $dogs_quantity; ?>

<p>Host Contact Information</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Name: <?php echo $host_name; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Phone: <?php echo $host_phone; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Email: <?php echo $host_mail; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Address: <?php echo $address; ?>

<p>Receipt & Policies</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Total Charge: $<?php echo $total_charge; ?> (includes nightly fees, any extra charges, and Dog Vacay booking fee)
<br />&nbsp;&nbsp;&nbsp;&nbsp;Payment: Your payment will be held in escrow until the day after you drop off your dog.
<br />&nbsp;&nbsp;&nbsp;&nbsp;Cancellation Policy: <?php echo $cancellation; ?>
</div>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
