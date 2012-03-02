<?php

/**
 * from:    noreply@dogvacay.com
 * bcc:     concierge@dogvacay.com 
 * to:      $email
 * subject: Good News! Someone has booked your place at DogVacay.com
 */

?>
<p>Hello <?php echo $host_name; ?></p>

<p>Congratulations! We wanted to let you know that <?php echo $user_name; ?>
has just booked a dog stay at your place for <?php echo $puppy_name; ?>. Please
review the booking information below and get in touch with the dog owner to
finalize the details. You may also want to arrange a "meet and greet to
introduce <?php echo $puppy_name; ?> to your home and your dogs, to confirm
that it's a good fit.</p>

<p>As always, if you have any questions you can reach us at help@dogvacay.com
or 888-681-DOGS.</p>

<p>Thanks for using DogVacay.com!</p>

<div>
<p>Boarding Details</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Order Number: <?php echo $order; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Drop-Off: <?php echo $drop_date; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Pick-Up: <?php echo $pick_date; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Dogs: <?php echo $dogs_quantity; ?>

<p>Dog Owner Contact Information</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Name: <?php echo $dog_owner_name; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Phone: <?php echo $dog_owner_phone; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Email: <?php echo $dog_owner_mail; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Address: <?php echo $dog_owner_address; ?>

<p>Dog Information</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Number of Dogs: <?php echo $dogs_quantity; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Name(s): <?php echo $puppy_name; ?>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Information: <?php echo $pet_info; ?>

<p>Receipt & Policies</p>
<br />&nbsp;&nbsp;&nbsp;&nbsp;Total Charge: $<?php echo $total_charge; ?> (includes nightly fees, any extra charges, and Dog Vacay booking fee)
<br />&nbsp;&nbsp;&nbsp;&nbsp;Payment: Your payment will be held in escrow until the day after you drop off your dog.
<br />&nbsp;&nbsp;&nbsp;&nbsp;Cancellation Policy: <?php echo $cancellation; ?>
</div>

<p>Thanks,</p>
<p>The Dog Vacay Team</p>
