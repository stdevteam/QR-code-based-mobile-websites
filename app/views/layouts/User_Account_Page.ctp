<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Dog Vacay | Dog boarding just got awesome!</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" /> 
    <meta name="copyright" content="" />
    <meta name="resource-type" content="document,images" />
    <meta name="Description" content="DogVacay" />
    <meta name="Keywords" content="" />
    <meta name="robots" content ="index,follow" />
    <meta name="Reply-to" content="" /> 
    <meta name="revisit-after" content="21 day" />
    <meta name="distribution" content="global" /> 
    <meta name="rating" content="general" /> 

    <link REL="SHORTCUT ICON" HREF="http://dogvacay.com/img/appimages/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="/css/layout.css" />
    <link rel="stylesheet" href="/css/anythingslider.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="/css/lightbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/slider.js"></script>
    <script type="text/javascript" src="/js/jquery.validate.min.js"></script>
    <script src="/js/jquery.lightbox.js" type="text/javascript"></script>
        <script src="/js/cufon.js" type="text/javascript"></script>
        <script src="/js/Arvo_400-Arvo_700.font.js" type="text/javascript"></script>
        <script src="/js/Cabin_400-Cabin_700.font.js" type="text/javascript"></script>
        <!--[if gte IE 9]> <script type="text/javascript"> Cufon.set('engine', 'canvas'); </script> <![endif]-->
    <script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
    
    <script type="text/javascript">
        function toggle(nameDiv) {
            var ele = document.getElementById(nameDiv);

            if(ele.style.display == "none"){ 
                ele.style.display = "block";  
            }else{
                ele.style.display = "none"; 
            }
        }

        jQuery(document).ready(function($){
            if(typeof $(".lightbox").lightbox != 'undefined'){
                $(".lightbox").lightbox();
            }
        });
    </script>
    <script type="text/javascript">
            //Cufon.replace('h1', { fontFamily: 'Arvo' });
            /*Cufon.replace('.div_menu a', { fontFamily:'Arvo', hover:'true'});
            Cufon.replace('.div_menu font', { fontFamily:'Arvo', hover:'true',fontSize:'19px'});*/
            
            Cufon.replace('.div_menu a', { fontFamily:'Arvo', hover:'true' ,fontSize:'19px'});
            Cufon.replace('.div_menu font', { fontFamily:'Arvo', hover:'true'});
            
            //Cufon.replace('.font1', { fontFamily: 'Arvo' });
            //Cufon.replace('.font2', { fontFamily: 'Cabin' });
            //Cufon.replace('.div_signup', { fontFamily: 'Arvo' });
            //Cufon.replace('.needhelp_light', { fontFamily: 'Arvo' });
            //Cufon.replace('.needhelp_dark', { fontFamily: 'Arvo' });
            //Cufon.replace('.ss_review', { fontFamily: 'Cabin' });
            //Cufon.replace('.ss_price', { fontFamily: 'Cabin' });
            Cufon.replace('.div_footer p', { fontFamily: 'Cabin' });
            //Cufon.replace('.lb_font1', {fontFamily:'Cabin'});
            //Cufon.replace('.lb_font2', {fontFamily:'Cabin'});    
    </script>
    <script type="text/javascript" src="/js/jquery.anythingslider.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/anything.js" charset="utf-8"></script>

    <?php /* <script type="text/javascript" src="/js/random.js" charset="utf-8"></script> */ ?>


    <?php /* <script src="/js/jquery4.js" type="text/javascript"></script> */ ?>
    <script src="/js/jquery.skinned-select.js" type="text/javascript"></script>
    <script src="/js/account.main.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/jquery.jgrowl.js"></script>
    <link rel="stylesheet" href="/css/jquery.jgrowl.css" type="text/css" media="screen" />
    <?php echo $this->element('noteJg'); ?>
    <?php
    echo '  '; 
    if(isset($includes)){
            echo $includes;
    }
    ?>
    <?php echo $scripts_for_layout;?>
    
    <style type="text/css">
    .ac_results li img {
        float: left;
        margin-right: 5px;
    }
    </style>
    <style type='text/css'>
        #calendar {
                width: 500px;
                margin: 0 auto;
                text-align: center;
                font-size: 14px;
                font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
        }
    </style>
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-26177225-1']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
  
</head>

<body>
    <div id="container">
	<div id="header">
    	<div id="logo">
            <a href="/">
                <?php /* <img src="/images/logo.jpg" alt="" title="" />  */ ?>
                <img src="/images/logo_dogvacay.png" class="logo-img" alt="" title="" />
            </a>
        </div>
        
        <div id="sign-in">
            <?php 
            if($this->Session->check('User.id')){ ?>
                <a href="/messages/inbox/" style="margin-left:3px;">Dashboard</a> | 
                <a href="/users/logout/">Logout</a>
            <?php
            }else{ ?>
                <a href="/users/login" style="margin-left:18px;">Sign In</a> | 
                <a href="/users/add">Sign Up</a>
            <?php
            } ?>
        </div>
        <?php echo $this->element('topnav'); ?> 
        
	</div>

        <div class="clear"></div>

 
	<div id="main">
            <?php echo $content_for_layout; ?>
        </div>
    </div>

    <div class="div_footer">
                <p>
                    <a href="/contents/about">About</a> &nbsp;| &nbsp;
                    <a href="/blog/">Blog</a> &nbsp;| &nbsp;
                    <a href="/contents/contact">Contact</a> &nbsp;| &nbsp;
                    <a href="/contents/Contact">FAQ</a> &nbsp;| &nbsp;
                    <!--<a href="/contents/help">Help</a> &nbsp;| &nbsp;-->
                    <a href="/contents/TermsAndPrivacy">Terms &amp; Privacy</a> &nbsp;| &nbsp;
                    <a href="http://facebook.com/dogvacay">Facebook</a> &nbsp;| &nbsp;
                    <a href="http://twitter.com/dogvacay">Twitter</a>
                </p>
                <p>Copyright &copy; 2011DogVacay All Rights Reserved.</p>
            </div>
    <?php /*<div class="foo">
        <div class="footer">
            <div style="padding-top:20px;font-wieght:bold;">
                <a href="/contents/about" class="seablue">About</a> |
	        <a href="/contents/contact" class="seablue">Contact</a> |
	        <a href="/blog" class="seablue">Blog</a> |
	        <a href="/contents/TermsAndPrivacy" class="seablue">Terms &amp; Privacy</a> |
	        <a href="/contents/help" class="seablue">Help</a> | 
	        <a href="#" class="seablue">Crate-Free Boarding</a> | 
                <a href="/contents/Contact" class="seablue">FAQ</a> | 
                <a href="http://facebook.com/dogvacay" class="seablue">Facebook</a> | 
                <a href="http://twitter.com/dogvacay" class="seablue">Twitter</a> | 
	        <a href="#" class="seablue">Other</a>

                <br /><br />
                <span style="font-size:11px;" class="seablue">
                    Copyright &copy; 2011 DogVacay. All Rights Reserved
                </span>
            </div>                    
        </div>
    </div>
     */?>
    
</body>
</html>
