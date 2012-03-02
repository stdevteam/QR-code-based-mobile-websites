<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DogVacay</title>
    <style type="text/css">
    *{margin:0; padding:0;}
    .clr{clear:both;}
    .frame{width:736px; margin:auto;}
    .error{width:736px; float:left; font-family:Arial, Helvetica, sans-serif; font-size:12px;}
    .error img{float:right; padding:10px 0 0 10px;}
    .error h1{color:#7EAE00; padding-bottom:10px;}
    .error h2{padding-bottom:10px; font-weight:normal; }
    .error ul{float:left;  margin:0; padding-left:30px; padding-bottom:10px; width:459px;}
    .error ul li{padding-bottom:3px; font-weight:bold;}
    .error ul li a{text-decoration:none; font-size:14px;}
    </style>
    </head>

    <body>
        <div class="frame">
            <div class="error">
                <img src="/img/appimages/sad_puppy.jpg" alt="" /><br /><br /><br /><br /><br /><br />
                <h1><span>Why the sad puppy face?</span></h1>
                <h2>The <a href="#">URL</a> you are requesting cannot be found…</h2> 
                <ul>
                    <li>Did you mean to type 
                        <a href="<?php echo FULL_BASE_URL; ?>"><u>dogvacay.com?</u></a>
                    </li> 
                    <li>Do you want to search for 
                        <a href="/places/"><u>boarding</u></a> or 
                        <a href="/contents/about/"><u>other services?</u></a>
                    </li>
                    <li>
                        <a href="/contents/contact/"><u>Contact us</u></a> to let us know what you think
                    </li>
                </ul>
                <h2>Your friends at 
                    <a href="<?php echo FULL_BASE_URL; ?>">dogvacay.com</a>
                </h2>
            </div>
        </div>
    </body>
</html>
