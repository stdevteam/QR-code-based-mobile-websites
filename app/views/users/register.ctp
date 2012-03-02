<script type="text/javascript">
    function match(){
            var p1 = $("#p1").val(),
            p2 = $("#p2").val(),
            $matchCont = $("#match");
            if(p1 == p2){
                $matchCont.html('<h3><font color="green">Passwords match</font></h3>');
            }else{
                $matchCont.html('<h3><font color="red">Passwords don\'t match</font></h3>');
            }
    }
    jQuery(document).ready(function(){
        $("#p1").focus(function(){
            $(".confirm-pass").slideDown('slow');
        });
    });
</script>
 
 <?php /*
<script type="text/javascript">
    function show_fb(){
        document.getElementById('fb_form').innerHTML = 
            '<iframe src="http://www.facebook.com/plugins/registration.php?' +
                'client_id=108625585906255&'+
                'redirect_uri=http://dogvacay.com/app/users/facebook_add/&'+
                'fields=['+
                '	{\'name\':\'name\'},'+
                '	{\'name\':\'email\'},'+
                '	{\'name\':\'location\'},'+
                '	{\'name\':\'password\'},'+
                "{'name':'IntrestedIn', 'description':'Intrested In',  'type':'select', 'options':{'1':'Board my pet(s) at someone place', '2':'Board someone else pet(s) at my place', '3':'Watch someone else pets(s) at their home'}, 'default':'1'},"+
                '	{\'name\':\'captcha\'}'+
                '] "'+
                'scrolling="auto "'+
                'frameborder="no "'+
                'style="border:none "'+
                'allowTransparency="true "'+
                'width="100% "'+
                'height="530 ">'+
                '</iframe>';
    }
</script>
<?php
$cont = ob_get_clean();
$this->addScript($cont,false);
?>

<div class="div_center" >
    <?php echo $form->create('User'); ?>

    <br />&nbsp;<br />&nbsp;

    <div class="contentstuff">
        <div class="features">
            <div class="signup">
                <div class="facebookSignUp">
                    Create an account with Facebook
                </div>
                <h1>
                    <a href="<?php if(isset($fb_login)){ echo $fb_login; } ?>">
                        <img src="/img/appimages/fb_icon2.png" alt="" />
                    </a>
                </h1>
                <br class="clear" />
                <div id="fb_form"></div>
                <p>or</p>
                <div class="signin">
                    <?php echo $this->element('note'); ?>
                    <div class="user">
                        <div class="in_whitebox user_login">
                            <div class="col">
                                <label>First Name:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                            echo $form->input('first_name', array(
                                                    'label' => false,
                                                    'type' => 'text'
                                            ));
                                        ?>
                                    </span>
                                    <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                                </div>
                            </div>
                            <div class="col">
                                <label>Last Name:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                            echo $form->input('last_name', array(
                                                    'label' => false,
                                                    'type' => 'text'
                                            ));
                                        ?>
                                    </span>
                                    <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                                </div>
                            </div>
                            <div class="col">
                                <label>Email Address:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                                echo $form->input('email', array(
                                                        'label' => false,
                                                        'type' => 'text'
                                                ));
                                        ?>
                                    </span>
                                    <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                                </div>
                            </div>

                            <div class="col">
                                <label>Password:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                                echo $form->input('password'	, array(
                                                        'id' => 'p1',
                                                        'label' => false,
                                                        'type' => 'password'
                                                ));
                                        ?>
                                    </span>
                                    <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                                </div>
                            </div>

                            <div class="col confirm-pass" style="display: none;">
                                <label>Confirm Password:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                                echo $form->input('confirm_password'	, array(
                                                        'id' => 'p2',
                                                        'onKeyUp' => 'javascript:match()',
                                                        'label' => false,
                                                        'type' => 'password'
                                                ));
                                        ?>
                                    </span>
                                    <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                                </div>
                            </div>

                            <center><div id="match" ></div></center>
                            <br class="clear" /> 
                            <?php 
                                echo $form->submit('',array(
                                    'label' => '',
                                    'style' => 'border:none;  background: url(\'/img/appimages/create_btn.png\')  no-repeat top left;  padding: 20px 95px;',
                                    'value'=>'', 
                                )); 
                            ?>

                            <?php /* <p><input name="" type="checkbox" value="" /> Remember me next time</p><br class="clear" /> */ ?>
                       <?php /*     <div class="marginleft5 remember-me">
                                <div class="field marginright5">
                                    <?php 
                                        echo $form->input('remember', array(
                                            'type' => 'checkbox', 
                                            'label' => false
                                        )); 
                                    ?>
                                </div>
                                <label class="remember">Remember me next time</label>
                            </div>
                            <img class="intlc" alt="" src="/img/appimages/guest_topleftcrv.jpg" />
                            <img class="intrc" alt="" src="/img/appimages/guest_toprightcrv.jpg" />
                            <img class="inblc" alt="" src="/img/appimages/guest_botleftcrv.jpg" />
                            <img class="inbrc" alt="" src="/img/appimages/guest_botrightcrv.jpg" />
                        </div>
                        <div class="clear"></div>
                        <div class="no_account">
                            <h2>Already a Dog Vacay member?</h2>
                            <a href="/users/login" class="signin_btn"><img src="/img/appimages/signin_btn.png" alt="" /></a><br class="clear" /><br />
                            <p>
                               By clicking on "Sign up" or "Connect with facebook", you confirm that you 
                               accept the <a href="/contents/TermsAndPrivacy">Terms of service.</a>
                            </p>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <!--contentstuff-->
    <?php echo $form->end(); ?>
</div>
 */ ?>

<div class="contentstuff">
    <div class="features">
    <?php echo $form->create('User'); ?>
        <div id="login-container">
            <div id="login" class="i-box">
                <div class="login-title"><h1>Vending QR Registration</h1></div>
                    <fieldset>
                        <section>
                            <span class="in">
                                <label>First Name:</label>
                                <?php 
                                    echo $form->input('first_name', array(
                                                'label' => false,
                                                'type' => 'text'
                                            ));
                                 ?>
                            </span>
                            <span class="in">
                                 <label>Last Name:</label>
                                <?php 
                                    echo $form->input('last_name', array(
                                                'label' => false,
                                                'type' => 'text'
                                            ));
                                 ?>
                            </span>
                            <span class="in">
                                 <label>Email:</label>
                                <?php 
                                    echo $form->input('email', array(
                                                'label' => false,
                                                'type' => 'text'
                                                ));
                            ?>
                            </span>
                            <span class="in">
                                 <label>Password:</label>
                                <?php 
                                    echo $form->input('password'	, array(
                                                'id' => 'p1',
                                                'label' => false,
                                                'type' => 'password'
                                                ));
                                ?>
                            </span>
                            <span class="in">
                                 <label>Confirm Password:</label>
                                <?php 
                                    echo $form->input('confirm_password'	, array(
                                                'id' => 'p2',
                                                 'onKeyUp' => 'javascript:match()',
                                                 'label' => false,
                                                 'type' => 'password'
                                                ));
                                ?>
                            </span>
                            <br class="clear" /> 
                            <?php 
                                echo $form->submit('Create Account',array(
                                            'label' => '',
                                            'value'=>'', 
                                     )); 
                            ?>
                          </section>
                      </fieldset>
            </div>
        </div>
        <?php echo $form->end(); ?> 
    </div>
</div>
<!--contentstuff-->
