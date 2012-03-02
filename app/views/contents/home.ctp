<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="description" content="Better than a kennel! Find a trusted home for your dog to stay while you are away. We offer a 100% money-back satisfaction guarantee, 24/7 support" />
        <title>Dog Vacay | Dog boarding just got awesome!</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="SHORTCUT ICON" href="http://dogvacay.com/img/appimages/favicon.ico"/>
        <!--<link rel="stylesheet" href="css/style.css" type="text/css">-->
        <!--[if !IE]> -->
            <link rel="stylesheet" href="/css/appcss/new.css?v=15" title="contemporary" type="text/css" />
        <!-- <![endif]-->

        <!--[if IE]>
            <link rel="stylesheet" href="/css/appcss/new.css?v=15" title="contemporary" type="text/css" />
        <![endif]-->
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
        
        <script src="/js/cufon.js" type="text/javascript"></script>
        <script src="/js/Arvo_400-Arvo_700.font.js" type="text/javascript"></script>
        <script src="/js/Cabin_400-Cabin_700.font.js" type="text/javascript"></script>
        
        <!--[if gte IE 9]> <script type="text/javascript"> Cufon.set('engine', 'canvas'); </script> <![endif]--> 
        
        <script src="/js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
        <link href="/css/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
        
        <link href="/css/anythingslider.css" rel="stylesheet" type="text/css" />
        <script src="/js/jquery.anythingslider.min.js" type="text/javascript"></script>
        
        <script type="text/javascript" src="/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
        
        <script type="text/javascript" src="http://a.vimeocdn.com/js/froogaloop2.min.js"></script>
        <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e88f12051923b41"></script>
        
        <script type="text/javascript">
        //<![CDATA[
            jQuery(document).ready(function($){
                $(function() {
                    $( "#inputDate,#pickDate" ).datepicker({
                        minDate: 0,
                        showOtherMonths: true,
                        selectOtherMonths: true,
                        nextText: '>>',
                        prevText: '<<'
                    });
                });    
                var myDate = new Date();
                var prettyDate =(myDate.getMonth()+1) + '/' + myDate.getDate() + '/' +
                myDate.getFullYear();
                var startDate;
                var endDate;                              
                $("#inputDate").val(prettyDate);
                $("#pickDate").val(prettyDate);
                
                var s = $('#slider')
                s.addClass('anythingBase');
                s.parent().addClass('anythingWindow');
                s.parent().parent().addClass('anythingSlider')
                .find('.anythingControls, .arrow, .cloned').show();
                    
                $('#slider').anythingSlider({
                    appendControlsTo    : $('.others'),
                    easing              : 'easeInOutBack',
                    appendBackTo        : $('.prev'),
                    appendStartStopTo   : $('#pause_play'),
                    startText           : "",   // Start button text
                    stopText            : "",
                    buildNavigation     : false,
                    autoPlay            : true,
                    delay               : 8500,
                    resumeDelay         : 11111111111111111,
                    buildStartStop      : false,
                    infiniteSlides      : false,
                    appendForwardTo     : $('.next'),
                    onSlideComplete     : function(slider){
                                            // alert('Welcome to Slide #' + slider.currentPage);
                                          }
                  /*navigationFormatter : function(i, panel){
                                         // return '<img src="demos/images/th-slide-' + ['civil-1', 'env-1', 'civil-2', 'env-2'][i - 1] + '.jpg" />';
                                            }	*/
                });
                <?php 
                if(!isset($_COOKIE['video']) && isset($_COOKIE['beta'])){/* ?>
                    <?php setcookie("video", "yes_new", (time()+31104000), '/', ".dogvacay.com", false); ?>
                        
                    $('iframe.vimeo').each(function(){
                        Froogaloop(this).addEvent('ready', ready);
                    });
                    function ready(playerID){
                        // Add event listerns
                        // http://vimeo.com/api/docs/player-js#events
                        Froogaloop(playerID).addEvent('play', play);
                        //  Froogaloop(playerID).addEvent('seek', seek);

                        // Fire an API method
                        // http://vimeo.com/api/docs/player-js#reference
                        Froogaloop(playerID).api('play');
                        $("#pause_play").attr('class','button_play');
                    }
                    
                    function play(playerID){
                        $("#pause_play").attr('class','button_play');
                        var slider = $('#slider').data('AnythingSlider');
                        slider.startStop(stop);
                        return false;
                    }
                <?php
                */}else{ ?>
                    $('iframe.vimeo').each(function(){
                        Froogaloop(this).addEvent('ready', ready);
                    });
                    
                    function ready(playerID){
                        // Add event listerns
                        // http://vimeo.com/api/docs/player-js#events
                        Froogaloop(playerID).addEvent('play', play);
                        //     Froogaloop(playerID).addEvent('pouse', pause);    //for pousing when pressed slider control buttons
                        //  Froogaloop(playerID).addEvent('seek', seek);
                        // Fire an API method
                        // http://vimeo.com/api/docs/player-js#reference
                        //Froogaloop(playerID).api('play');
                    }
                    
                    function play(playerID){
                        $("#pause_play").attr('class','button_play');
                        var slider = $('#slider').data('AnythingSlider');
                        slider.startStop(stop);
                        return false;
                    }
                    /* function pouse(playerID){
                        $('div.controls span').click(function(){
                            alert('1');
                            Froogaloop(playerID).api('stop');
                            return false;
                        });
                    }*/  
                <?php
                } ?>
                    
                $('.others a').click(function(e){
                    e.preventDefault();
                    $("#pause_play").attr('class','button_play');
                    var slide = $(this).attr('href').substring(1);
                    $('#slider').anythingSlider(slide);
                    return false;
                });

                $('#pause_play').click(function(){
                    var slider = $('#slider').data('AnythingSlider');
                    slider.startStop(!slider.playing);
                    return false;
                });
                                                  
               //add click event
                $(".next").click(function() {
                    //calling next method
                });
                $(".prev").click(function() {
                    //calling previous method
                });
                
                $("#pause_play").click(function() {
                    if($("#pause_play").attr('class') == 'button_pause'){
                       $("#pause_play").attr('class','button_play');
                    }else{
                        if($("#pause_play").attr('class') == 'button_play'){
                            $("#pause_play").attr('class','button_pause');
                        }
                    }
                });
                
                /*
                $(".video2").click(function(){
                    var id = $(this).attr('id');
                    mcarousel.goto(id);                    
                    return false;                    
                });
                */
                $(".div_contents_movie").hover(function(){
                    $(".controls").show()
                },
                function(a){
                    var b=$(a.relatedTarget);
                    if($.inArray("div_contents_movie",b.parents())===-1){
                        $(".controls").hide()
                    }
                });

             });
             //]]>
        </script>
        <script type="text/javascript">
            Cufon.replace('h1', { fontFamily: 'Arvo'});
            Cufon.replace('h2', { fontFamily: 'Arvo', fontSize: '27px'});
            Cufon.replace('.div_menu a', { fontFamily:'Arvo', hover:'true' ,fontSize:'19px'});
            Cufon.replace('.div_menu font', { fontFamily:'Arvo', hover:'true'});
            Cufon.replace('.font1', { fontFamily: 'Arvo', fontSize: '21px' } );
            Cufon.replace('.font2', { fontFamily: 'Cabin' , fontSize: '21px'});
            Cufon.replace('.div_signup', { fontFamily: 'Arvo' });
            Cufon.replace('.needhelp_light', { fontFamily: 'Arvo' });
            Cufon.replace('.needhelp_dark', { fontFamily: 'Arvo' });
            Cufon.replace('.ss_review', { fontFamily: 'Cabin' });
            Cufon.replace('.ss_price', { fontFamily: 'Cabin' });
            Cufon.replace('.div_footer p', { fontFamily: 'Cabin' });
            Cufon.replace('.lb_font1', {fontFamily:'Cabin'});
            Cufon.replace('.lb_font2', {fontFamily:'Cabin'});    
            
            Cufon.replace('span.bullet-link', { fontFamily: 'Cabin', fontSize: '12px' });
        </script>
        <script type="text/javascript">
            //placeholders
            function processPlaceholders(){
                $('.placeholder').each(function(index){
                    $(this).data('placeholder', $(this).val());
                });
                
                $('.placeholder').focus(function(){
                    if($(this).val() == $(this).data('placeholder')){
                        $(this).val('');
                    }
                })
                .blur(function(){
                    if(jQuery.trim($(this).val()) == ''){
                        $(this).val($(this).data('placeholder'));
                    }
                });
            }
            
            jQuery(document).ready(function($){
                //placeholders
                processPlaceholders();
            });
        </script>
        <?php 
        if(!isset($_COOKIE['beta']) ){ ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($){
                    $.fancybox({
                        //'width'				 : 815,
                        //'height'			 : 7,
                        'enableEscapeButton': false,
                        'autoDimensions'    : true,
                        'autoScale'         : true,
                        'transitionIn'      : 'none',
                        'transitionOut'     : 'none',
                        'type'              : 'ajax',
                        'href'          : '/landings/emailCollector',
                        'hideOnContentClick': false,
                        'hideOnOverlayClick': false,
                        'showCloseButton'   : true,
                        'padding'           : 0,
                        'overlayOpacity'    : 0.7,
                        'overlayColor'      : '#000000',
                        'background-color'  : '#ffffff',
                        onComplete: function(){
                            if (window.addthis){
                                window.addthis = null;
                            }
                            jQuery.getScript('http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e9d076825ad5937&domready=1', function(script){
                                addthis.init();
                            });
                            
                            if($("#home-overlay").size() > 0){
                                $("#home-overlay").parents().find("#fancybox-outer").css({'backgroundColor': 'transparent'});
                                $("#home-overlay").parents().find("#fancybox-wrap").css({'padding': '0px'});
                            }
                            processPlaceholders();
                        },
                        // cookie for email capture close
                        'onCleanup' : function() {
                                            $.ajax({ url: '/contents/setCookie',
                                            type: 'post',
                                            success: function(output) {                                   
                                                            }
                                            });
                                    }

                    });
                });                
            </script>
       <script type="text/javascript">
    /*jQuery(document).ready(function ($){
        $('#fancybox-close').click(setBeta());
    });
    function setBeta(){
        alert('1');
        
        return true;
    }*/
</script>
        <?php
        } ?> 
        
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
        <div id="div_center">
            <a href="<?php echo FULL_BASE_URL; ?>"><img src="/images/logo_dogvacay.png" class="img_logo" alt="" /></a>
            <div class="div_signup">
                <div class="div_signuptext"   >
                    <p style="margin-top:8px;">
                    <?php 
                    if($this->Session->check('User.id')){
                    ?>
                        <a href="/messages/inbox/">Dashboard</a>
                        <font color="#B5BABD">&nbsp;| &nbsp;</font>
                        <a href="/users/logout/">Logout</a>
                    <?php 
                    }else{ ?>
                        <a href="/users/add" >Sign up</a>
                        <font color="#B5BABD">&nbsp;| &nbsp;</font>
                        <a href="/users/login">Sign in</a>
                    <?php } ?>
                    </p>
                </div>
                <div class="div_sharebar"   > 
                    <!-- AddThis Button BEGIN -->
                    <!--
                    <div class="addthis_toolbox addthis_default_style "  style=" width:155px;   ">
                    <a class="addthis_button_preferred_1"></a>
                    <a class="addthis_button_preferred_2"></a>
                    <a class="addthis_button_preferred_3"></a>
                    <a class="addthis_button_preferred_4"></a>
                    <a class="addthis_button_compact"></a>
                    <a class="addthis_counter addthis_bubble_style"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e9d076825ad5937"></script>
                     -->
                    <font class="needhelp_dark">Need help?</font>
                    <font class="needhelp_light"> 888-681-DOGS</font>						
                    <!-- AddThis Button END -->
                </div>
            </div>
            <?php echo $this->element('topnav'); ?>	
            <div class="clear"></div>
            <div class="div_contents">
                <div class="div_contents_left">
                    <div class="search_h2">
                        <h2>
                            Find a real home
                            <br />
                            for your dog to stay
                            <br />
                            while you're away
                        </h2>
                    </div>
                    <div class="search_form">
                        <form action="/places/" class="form_homepage">
                            <div class="search_div">
                                <input id="search" class="search_input placeholder" name="search" type="text" value="<?php echo $geo_lookup; ?>" />
                            </div>
                            <?php /* <p class="p1">
                                Drop Off &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
                                Pick Up &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Dogs
                            </p>
                            <div class="pickup_div">
                                <input class="pickup_input" id="inputDate" name="drop" type="text" />
                            </div>

                            <div class="pickup_div">
                                <input class="pickup_input" id="pickDate"  name="pick" type="text" onblur="if(this.value=='') this.value='mm/dd/yy';" onfocus="if(this.value=='mm/dd/yy') this.value='';" value="mm/dd/yy"/>
                            </div>

                            <div class="dogs_div">
                                <select class="dogs_input" id="Number_of_dogs" name="dogs">
                                    <option class="dogs_input" selected="selected" value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4+</option>
                                </select>
                            </div>   */ ?>                     

                            <input name="input" class="search_btn" type="submit" value="" />
                        </form>
                    </div>
                    <br />
                    <div class="contents_left_links">
                        <div>
                            <span class="bullet-icon family fl"></span>
                            <span class="bullet-link fl marginleft15">Experienced host families</span><br />
                            <div class="clear"></div>
                        </div>
                        <div>
                            <span class="bullet-icon photo fl"></span>
                            <span class="bullet-link fl marginleft15">Photo updates of your dog's activities</span><br />
                            <div class="clear"></div>
                        </div>
                        <div>
                            <span class="bullet-icon payments fl"></span>
                            <span class="bullet-link fl marginleft15">Convenient online scheduling &amp; payments</span><br />
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                
                <div class="div_contents_movie"  >
                    <!--<div id="slideshow_container">-->
                    <div class="feature" style=" margin-left:0px;">
                        <div id="slider_items" style=" border:7px solid white;">
                            <ul id="slider" >                                 
                                <li>
                                    <div class="item">
                                    <?php /*    <iframe class="vimeo" id="player1" src="http://player.vimeo.com/video/29766265?api=1&amp;player_id=player1&amp;title=0&amp;byline=0&amp;portrait=0" width="575" height="322" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe> */?>
                                        <iframe class="vimeo" id="player1" src="http://player.vimeo.com/video/29766265?api=1&amp;player_id=player1&amp;title=0&amp;byline=0&amp;portrait=0" width="530" height="320" frameborder="0" ></iframe>
                                    </div>
                                </li>
                                <?php foreach($sliderItems as $item){?>
                                <li>
                                    <div class="item">
                                        <a href="/places/<?php echo $item['contents']['listing_id'] ?>" class="image_link rounded_top">
                                            <img src="<?php echo SYSTEM_PATH;  ?>slider/<?php echo $item['contents']['image']; ?>" alt="" style="width:530px; height:250px;" />
                                        </a>
                                <?php if($item['contents']['badge'] != '0'){ ?>
                                        <img src="/img/badges/<?php echo $item['contents']['badge']; ?>.png" class="badge" style="position: absolute;right: -5px;top: -2px;" alt="" />
                                <?php } ?>
                                        <div class="slideshow_item_details rounded_bottom" >
                                            <?php /* <img src="/img/appimages/man_img.png"  alt="" width="39" height="40" /> */ ?>
                                            <div class="slideshow_item_details_text rounded_more" style="background-color:#B9E6E9; ">
                                                <div class="ss_details_top" style="overflow:hidden; font:19px cabin; font-weight:bold; color:#AB4179; text-align:left; margin-top:0px; padding-top:15px; padding-left:15px;">
                                                    <span class="ss_name"> <?php /*href="<?php echo $item['contents']['listing_id']; ?>" */ ?>
                                                        <?php echo $item['contents']['slider_text']; ?>
                                                    </span>
                                                </div>
				<?php /*?>	    <div class="ss_details_bottom" style="overflow:hidden; font:15px cabin; text-align:left; padding-bottom:15px; padding-left:15px;">
						<span class="ss_price"><?php echo $item['places']['title'] ?> from <?php echo $item['place_terms']['nigthly_rates']; ?>$/night</span>
                                                <?php if($item['contents']['badge'] !== '0'){ ?>
                                                <span class="ss_review"><?php  echo ucwords(str_replace( "_", "  ", $item['contents']['badge']));?></span>
                                                <?php } ?>
					    </div>                                
                                 */ ?>
                                                <div class="ss_details_bottom" style="overflow:hidden; font:15px cabin; text-align:left; padding-bottom:15px; padding-left:15px;">
                                                    <span class="ss_price"><?php echo $item['contents']['text'] ?> </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                              <?php } ?>
                            </ul>
                        </div>
                        <div class="controls" style="display:none;">
                            <span class="prev">&nbsp;</span>
                            <span class="button_pause" id="pause_play">&nbsp;</span>
			    <span class="next">&nbsp;</span>
			</div>
                      <?php /*  <div class="others" >
                        <?php    
                        $count = count($sliderItems);
                        for($i = 1; $i<= $count; $i++){
                            $key = $count - $i;
                        ?>
                            <a href="#<?php echo $key + 2;?>">
                                <img class="video2"  src="/img/slider/<?php echo $sliderItems[$key]['contents']['image']; ?>"  alt=""/>
                            </a>
                    <?php } ?>
                            <a href="#1"><img class="video2" id="0" src="/img/appimages/video_thumb.jpg" alt="" /></a>            
                        </div> */?>
                    </div>
                    <!--<div id="slideshow_controls" class="rounded_top" style="display:none;"><a class="ss_button_icon" href="javascript:void(0);" id="ss_button_prev"></a><a class="ss_button_icon ss_button_pause" href="javascript:void(0);" id="ss_button_pause_play"></a> <a class="ss_button_icon" href="javascript:void(0);" id="ss_button_next"></a></div>-->
                </div>
                
                <div class="div_help">
                    <font class="font1">Need some help?</font> &nbsp;<br />
                    <font class="font2">Call a Dog Vacay Concierge at <font class="font3">888-681-DOGS</font></font>                    
                </div>
                <div class="clear"></div>
            </div>
            <!--<img src="img/appimages/banner_image.jpg" style="position:relative; margin-bottom:-50px; margin-top:50px;">-->
            <div class="div_banner">
                <?php /* <img src="img/appimages/banner_24.png" alt=""/>
                <img src="img/appimages/banner_photo.png" alt=""/>
                <img src="img/appimages/banner_liability.png" alt=""/>
                <img src="img/appimages/banner_emergency.png" alt=""/> */ ?>
                <img src="img/appimages/banner_guarantee.png" alt=""/>
            </div>	
            <div class="cms">
                <?php echo $contentForHomePage[0]['articles']['text']; ?> 
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
                <p>
                    Copyright 2011 Dog Vacay, Inc.
                </p>
            </div>
        </div>
    </body>
    <?php echo $this->Session->flash(); ?>
</html>
