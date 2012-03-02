 <!--contentstuff-->
<div class="contentstuff">
    <div class="features">
    <?php echo $form->create('User', array('action' => 'login')); ?>
        <div id="login-container">
            <div id="login" class="i-box">
                <div><a href="/users/register" class="i-button">Register</a></div>
                <div class="login-title"><h1>Vending QR Login</h1></div>
                  <?php //  <form name="login-form" id="login-form" action="/#dashboard" method="get"> ?>
                     <fieldset>
                         <section>
                             <span class="in">
                                 <?php echo $form->input('email', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text'
                                        )); 
                                    ?>
                             </span>
                             <?php //     <input class="i-text required" type="text" name="username" placeholder="Username"></input> ?>
                          </section>
                          <section>
                              <span class="in">
                                  <?php echo $form->input('password', array(	
                                        'type' => 'password',
                                        'label' => false,
                                        'class' => 'i-text'
                                        )); 
                                    ?>
                              </span>
                                   <?php //     <input class="i-text required" type="password" name="password" placeholder="Password"></input> ?>
                           </section>
                      </fieldset>
                      <a href="#">Forgot your password?</a>
                      <?php 
                      echo $form->submit('Login', array(
                           'class'=> 'i-button',
                           'label' => false,
                           'value'=> false, 
                           )); 
                      ?>		       
                      <?php // <input class="i-button" type="submit" value="Login" /> ?>
                      <?php echo $form->end(); ?> 
                 </div>
            </div>
            
          <?php /* ?>
        <div class="signup">
            <div class="facebookSignIn">
                Sign In Using Facebook
            </div>
            <h1>
                <a href="<?php if(isset($fb_login)){ echo $fb_login; } ?>"><img src="/img/appimages/fb_icon2.png" alt="" /></a>
            </h1>
            <br class="clear" />
            <p>or</p>
            <div class="signin">
                    <?php echo $this->element('note'); ?>
                <div class="user">
                    <div class="in_whitebox user_login">
                      
                        <div class="col">
                            <label>Email:</label>
                            <div class="field">
                                <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                <span class="in">
                                    <?php echo $form->input('email', array(	
                                        'type' => 'text',
                                        'label' => false
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
                                    <?php echo $form->input('password', array(	
                                        'type' => 'password',
                                        'label' => false
                                        )); 
                                    ?>
                                </span>
                                <img src="/img/appimages/desfield_rightcrv.jpg" alt="" />
                            </div>
                        </div><br class="clear" />                     
                        <?php //echo $form->submit('Login');
                        echo $form->submit('', array(
                            'label' => false,
                            'value'=> false, 
                            'style' => 'border:none;  background: url(\'/img/appimages/login_btn.png\')  no-repeat top left; padding: 20px 89px; position: absolute; top: 120px; left: 300px;'
                            )); 
                        ?>		
                        <a href="/users/recover/" class="forgot">Forgot Password?</a><br class="clear" />                                        
                        <?php /* <p><input name="" type="checkbox" value="" /> Remember me next time</p><br class="clear" /> */ ?>
                 <?php /* ?>       <div class="marginleft10 remember-me">
                            <div class="field marginright10">
                            <?php 
                            echo $form->input('remember', array(
                                    'type' => 'checkbox', 
                                    'label' => false
                                    )
                                ); 
                            ?>
                            </div>
                            <label class="remember">Remember me next time</label>
                        </div>
                        <?php echo $form->end(); ?> 
                        <img class="intlc" alt="" src="/img/appimages/guest_topleftcrv.jpg"/>
                        <img class="intrc" alt="" src="/img/appimages/guest_toprightcrv.jpg"/>
                        <img class="inblc" alt="" src="/img/appimages/guest_botleftcrv.jpg"/>
                        <img class="inbrc" alt="" src="/img/appimages/guest_botrightcrv.jpg"/>
                    </div>
                    <div class="no_account">
                        <h2>Need an account?</h2>
                        <a href="/users/add" class="cret_btn">
                            <img src="/img/appimages/create_btn.png" alt="" />
                        </a><br class="clear" />                       
                    </div>
                </div>
            </div>                            
        </div>    
        <?php */ ?>
    </div>
</div>
<!--contentstuff-->


