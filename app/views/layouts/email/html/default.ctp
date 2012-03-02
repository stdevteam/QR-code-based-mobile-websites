<?php
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.view.templates.layouts.email.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?php echo $title_for_layout;?></title>
</head>
<body style="background: #e7f8f9;">
	
<style type="text/css">
	blockquote, q { quotes: none; }
	blockquote:before, blockquote:after,
	q:before, q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	
	a{text-decoration: none;}
    p{ margin-bottom: 1em; }
	
	/* Generic styles applied to all places were applicable */
	.bold{font-weight: bold;}
	.italic{font-style: italic;}
	.underline{text-decoration: underline;}
	.no-underline{text-decoration: none;}

	.fs10{font-size: 10px;}
	.fs11{font-size: 11px;}
	.fs12{font-size: 12px;}
	.fs13{font-size: 13px;}
	.fs18{font-size: 18px;}
	.fs20{font-size: 20px;}
	.fs22{font-size: 22px;}
	
	.fl{float: left;}
	.fr{float: right;}
	.clear{clear: both;}

	.w20{width: 20px;}
	.w109{width: 109px;}
	.w106{width: 106px;}
	.w250{width: 250px;}
	.w280{width: 280px;}
	.w290{width: 290px;}
	.w300{width: 300px;}
	.w350{width: 350px;}
	.w400{width: 400px;}
	.w450{width: 450px;}
	.w500{width: 500px;}
	.w540{width: 540px;}
	.w600{width: 600px;}

	.mh85{max-height: 85px}
	.mh350{max-height: 350px}
	.mh250{max-height: 250px}

	.margintop5{margin-top: 5px;}
	.margintop10{margin-top: 10px;}
	.margintop15{margin-top: 15px;}
	.margintop20{margin-top: 20px;}
	.margintop30{margin-top: 30px;}
	.margintop50{margin-top: 50px;}

	.marginbot5{margin-bottom: 5px;}
	.marginbot10{margin-bottom: 10px;}
	.marginbot15{margin-bottom: 15px;}
	.marginbot20{margin-bottom: 20px;}
	.marginbot25{margin-bottom: 25px;}
	.marginbot30{margin-bottom: 30px;}
	.marginbot40{margin-bottom: 40px;}
	.marginbot50{margin-bottom: 50px;}

	.marginleft5{margin-left: 5px;}
	.marginleft10{margin-left: 10px;}
	.marginleft15{margin-left: 15px;}
	.marginleft20{margin-left: 20px;}
	.marginleft30{margin-left: 30px;}
	.marginleft50{margin-left: 50px;}

	.marginright5{margin-right: 5px;}
	.marginright10{margin-right: 10px;}
	.marginright15{margin-right: 15px;}
	.marginright20{margin-right: 20px;}
	.marginright30{margin-right: 30px;}
	.marginright50{margin-right: 50px;}

	.radius10{border-radius: 10px;-moz-border-radius: 10px;-webkit-border-radius: 10px;
	-o-border-radius: 10px;-ms-border-radius: 10px;}

	.radius5{border-radius: 5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;
	-o-border-radius: 5px;-ms-border-radius: 5px;}


	.profile_heading{font-size: 22px;margin-bottom: 5px;}

	.relative{position: relative;}

	.black{color: #000000;}
	
	.green{color: #51a1a1;}
	.pink{color: #d565a1;}
	.grey{color: #6c6c6c;}
	
	body{background: #e7f8f9;}
	
	.container{width: 600px;background: #e7f8f9;padding: 15px 30px 30px;margin: 0 auto;font-family: Tahoma,Arial,sans-serif;}
	.top{}
	.logo{width: 200px;font-size: 30px;color: #51a1a1;}
	.logo a{color: #51a1a1;text-decoration: none;border: none;}
	.logo-img{width: 200px;border: none;}
	
	.menu{padding-top: 55px;color: #37939a;font-size: 13px;}
	
	.menu a{color: #37939a;font-size: 15px;xfont-weight: bold;}
	
	.square{width: 598px;xborder: 1px solid #000000; }
	.inner-square{width: 556px;border: 1px solid #abcfcf;padding: 20px;background: #ffffff;color: #6c6c6c;font-size: 12px; }
</style>
<div class="container" style="width: 600px;background: #e7f8f9;padding: 15px 30px 30px;margin: 0 auto;font-family: Tahoma,Arial,sans-serif;">
	<div class="top marginbot10" style="margin-bottom: 10px;">
            <div class="fl logo" style="float: left; width: 200px;font-size: 30px;color: #51a1a1;">
                <a style="text-decoration: none; color: #51a1a1;text-decoration: none;border: none; " href="<?php echo FULL_BASE_URL; ?>" target="_blank" style="text-decoration: none;">
                    <img style="width: 200px;border: none;" src="<?php echo FULL_BASE_URL; ?>/img/appimages/logo_dogvacay.png" alt="Dogvacay.com" class="logo-img" />
                </a>
            </div>
            <div class="fr" style="float: right;">
                <div class="menu" style="padding-top: 55px;color: #37939a;font-size: 13px;">
                    <a style="text-decoration: none; color: #37939a;font-size: 15px;xfont-weight: bold;" href="<?php echo FULL_BASE_URL; ?>/users/login/" target="_blank" class="menu-item">
                        Sign In
                    </a>
                    &nbsp;|&nbsp;
                    <a style="text-decoration: none; color: #37939a;font-size: 15px;xfont-weight: bold;" href="<?php echo FULL_BASE_URL; ?>/contents/Contact/" target="_blank" class="menu-item">
                        Help
                    </a>
                    &nbsp;|&nbsp;
                    <a style="text-decoration: none; color: #37939a;font-size: 15px;xfont-weight: bold;" href="<?php echo FULL_BASE_URL; ?>/places/" target="_blank" class="menu-item">
                        Search
                    </a>
                    &nbsp;|&nbsp;
                    <a style="text-decoration: none; color: #37939a;font-size: 15px;xfont-weight: bold;" href="<?php echo FULL_BASE_URL; ?>/contents/contact/" target="_blank" class="menu-item">
                        Contact
                    </a>
                </div>
            </div>
            <div class="clear" style="clear: both;"></div>
        </div>
    <div class="square" style="width: 598px;">
        <div class="inner-square" style="width: 556px;border: 1px solid #abcfcf;padding: 20px;background: #ffffff;color: #6c6c6c;font-size: 12px;">
            <div style="margin: 2em auto;">
            <?php echo $content_for_layout;?>            
            </div>
            <hr />
            <p style="margin-bottom: 1em;">
                <a style="text-decoration: none; font-weight: bold; color: #51a1a1;" href="http://dogvacay.com/" target="_blank" class="green bold">DogVacay.com</a>
                &nbsp;<span class="green">|</span>&nbsp;
                <a style="text-decoration: none; font-weight: bold; color: #51a1a1;" href="http://twitter.com/dogvacay" target="_blank" class="green bold">@dogvacay</a>
                &nbsp;<span class="green">|</span>&nbsp;
                <a style="text-decoration: none; font-weight: bold; color: #51a1a1;" href="http://facebook.com/DogVacay" target="_blank" class="green bold">facebook</a>
                &nbsp;<span class="green">|</span>&nbsp;
                <a style="text-decoration: none; font-weight: bold; color: #51a1a1;" href="http://blog.dogvacay.com/" target="_blank" class="green bold">blog</a>
            </p>
            <p style="margin-top: 1em; color: #ccc;">
                This email was sent by <a style="color: #ccc; text-decoration: none;" href="<?php echo FULL_BASE_URL ?>">DogVacay.com</a> Email system
            </p>
        </div>
    </div>
</div>

</body>
</html>
