<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    
    <title>Infinity - HTML5 & CSS3 Premium Full Featured App Framework Theme</title>
    <meta name="description" content="">
    <meta name="author" content="">
    
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href='http://fonts.googleapis.com/css?family=Ubuntu+Condensed|Ubuntu' rel='stylesheet' type='text/css'>   
    <link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>
    
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
    
    <!-- CSS: implied media=all -->
    <link rel="stylesheet" href="/css/table.css">
    <link rel="stylesheet" href="/css/fullcalendar.css">
    <link rel="stylesheet" href="/css/simplemodal.css">
    <link rel="stylesheet" href="/css/jquery.gritter.css">
    <link rel="stylesheet" href="/css/jquery.wysiwyg.css">
    <link rel="stylesheet" href="/css/chosen.css">
    <link rel="stylesheet" href="/css/jquery-ui-1.8.16.custom.css">
    <link rel="stylesheet" href="/css/elfinder.min.css">
    <link rel="stylesheet" href="/css/jqtransform.css">
    <link rel="stylesheet" href="/css/style.css">
    <!-- end CSS-->
    
    <!-- CSS Media Queries for Standard Devices -->
    <!--[if !IE]><!-->
        <link rel="stylesheet" href="/css/devices/smartphone.css" media="only screen and (min-width : 320px) and (max-width : 767px)">
        <link rel="stylesheet" href="/css/devices/ipad.css" media="only screen and (min-width : 768px) and (max-width : 1024px)"> 
    <!--<![endif]-->
    
    <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
        
    <!-- All JavaScript at the bottom, except for Modernizr / Respond.
         Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
         For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
    <script src="js/libs/modernizr-2.0.6.min.js"></script>
</head>

<body>
    <div id="body-container">
        <div id="container">
            <?php if(isset($isLogedin) && $isLogedin != 0){ ?>
	        <header>
	            <?php echo $this->element('navigation') ?>
	            <?php echo $this->element('profile') ?>
	            <div class="clearfix"></div>
	        </header>
            <?php } ?>
	        <div id="main" role="main">
       
        <?php // echo $this->element('topnav'); ?>
        <?php echo $content_for_layout ?> 
    <!-- end CENTER -->
    	        </div>
	        <footer>
	            <span>Created By STDev</span>
	        </footer>
	    </div> <!--! end of #container -->
    
    </div> <!--! end of #body-container -->
    
    <!-- modal content -->
    <!-- JavaScript at the bottom for fast page loading -->
    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/mylibs/excanvas.min.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="/js/libs/jquery-1.6.2.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/libs/jquery-ui-1.8.16.custom.min.js"></script>
    
    <!-- scripts -->
    <script language="javascript" type="text/javascript" src="/js/mylibs/elfinder.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.flot.pie.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.flot.resize.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.flot.stack.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.flot.crosshair.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.dataTables.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.tools.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/fullcalendar.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.gritter.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.simplemodal.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.autogrowtextarea.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.wysiwyg.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/controls/wysiwyg.image.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/controls/wysiwyg.link.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/controls/wysiwyg.table.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.idTabs.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.validate.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/chosen.jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.jqtransform.js"></script>
    <script language="javascript" type="text/javascript" src="/js/mylibs/jquery.ba-hashchange.min.js"></script>
    <script defer src="js/init.js"></script>
    <script defer src="js/bootstrap.js"></script>
    <!-- end scripts-->
    
    <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
         chromium.org/developers/how-tos/chrome-frame-getting-started -->
    <!--[if lt IE 7 ]>
      <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
      <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
    <![endif]-->
  
</body>
</html>

