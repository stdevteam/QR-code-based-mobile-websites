<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Dog Vacay</title>
<link href="<?php echo FULL_BASE_URL; ?>/css/appcss/style.css" rel="stylesheet" type="text/css" />
<script src="<?php echo FULL_BASE_URL; ?>/js/cufon.js" type="text/javascript"></script>
<script src="<?php echo FULL_BASE_URL; ?>/js/Kabel_Md_BT_400.font.js" type="text/javascript"></script>
<script type="text/javascript">
	Cufon.replace('.menu ul li', {hover:{color:'#c8e176'}});
	Cufon.replace('.menu2 ul li', {hover:{color:'#82b000'}});
	Cufon.replace('.nav ul li', {hover:{color:'#82b000'}});
	Cufon.replace('.sco ul.accept_tab li');
	Cufon.replace('h1, h2, h3, h4, h5, h6');
	
</script>
<script src="<?php echo FULL_BASE_URL; ?>/js/jquery-latest.js" type="text/javascript"></script>
<script type="text/javascript">
	$(function(){
			   
			function hideshow(id){
				//alert(id);
			$('.common').hide();
			$(id).show();
			}   
			   
		   $('#maintab a').click(function(e){
			var id_tad = $(this).attr('rel');
			$('#maintab li').removeClass('active');
			$(this).parent('li').attr('class', 'active');
			hideshow('#'+id_tad);
			e.preventDefault();
		 });
		   
		   });
</script>
</head>

<body>
<div class="wrapper"> 
  <!--contentstuff-->
  
  <div class="contentstuff">
    <div class="features">
      <div class="main">
        <div class="receive_stuf">
          <h3>HELLO <?php echo $user_name; ?><br />
            <span>You have a new message from <?php echo $sender_name; ?>!</span></h3>
        </div>
        <img src="<?php echo FULL_BASE_URL; ?>/img/appimages/topcrv_left.jpg" alt="" class="mtlc" /> <img src="<?php echo FULL_BASE_URL; ?>/img/appimages/topcrv_right.jpg" alt="" class="mtrc" /> <img src="http://dogvacay.com/img/appimages/botcrv_left.jpg" alt="" class="mblc" /> <img src="http://dogvacay.com/img/appimages/botcrv_right.jpg" alt="" class="mbrc" /> </div>
      <div class="comment_stuf">
        <textarea name="" cols="1" rows="1" readonly="readonly" ><?php echo $text; ?></textarea>
        <a href="#"><img src="<?php echo FULL_BASE_URL; ?>/img/appimages/btn_respond.jpg" class="btn_respond" alt=" " /></a>
        <div class="text_area">
          <h4>CHEERS:<br />
            <span>The Dog Vacay team</span></h4>
        </div>
      </div>
      <img src="<?php echo FULL_BASE_URL; ?>/img/appimages/top_leftcrv.jpg" alt="" class="tlc" /> <img src="<?php echo FULL_BASE_URL; ?>/img/appimages/top_rightcrv.jpg" alt="" class="trc" /> <img src="http://dogvacay.com/img/appimages/bot_leftcrv.jpg" alt="" class="blc" /> <img src="http://dogvacay.com/img/appimages/bot_rightcrv.jpg" alt="" class="brc" /> </div>
  </div>
  
  <!--contentstuff--> 
</div>
</body>
</html>
