<?php

/**
 * from:    noreply@dogvacay.com
 * bcc:     concierge@dogvacay.com
 * to:      $email
 * subject: Request for Meet & Greet at DogVacay.com
 */

?>
<p>Hello <?php echo $toUser['User']['first_name']." ".$toUser['User']['last_name']; ?></p>

<p>I want to schedule a meet and greet with You!</p>
 
<div>
<p>Details:</p>
<br />Boarding dates: from <?php echo $data['start_date'] . ' to ' . $data['end_date']; ?>
<br />Preferred days and times for Meet and Greet; <?php echo $data['preffered_days']; ?>
<br />Number of dogs: <?php echo $data['dogs']; ?>
<?php if (isset($data['notes'])): ?>
<br />Notes: <?php echo $data['notes']; ?>
<?php endif; ?>
</div>

<p>Thanks,</p>
<p><?php echo $byUser['User']['first_name']." ".$byUser['User']['last_name'][0]; ?></p>

<p>Reply: <?php echo FULL_BASE_URL; ?>/messages/read/<?php echo $t_id; ?></p>
