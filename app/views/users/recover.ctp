 <!--contentstuff-->
<div class="contentstuff">
    <div class="features">
    <?php echo $form->create('User', array('action' => 'recover')); ?>
        <div class="signup">
            <div style="text-align:center; font-size: 15px;">
                Please enter Your email address to recover password
            </div>
            <br />
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
                        
                        <?php //echo $form->submit('Login');
                        echo $form->submit('', array(
                            'label' => false,
                            'value'=> false, 
                            'style' => 'border:none;  background: url(\'/img/appimages/submit_btn.png\')  no-repeat top left; padding: 20px 89px; position: absolute; top: 120px; left: 300px;'
                            )); 
                        ?>		
                        
                        <?php /* <p><input name="" type="checkbox" value="" /> Remember me next time</p><br class="clear" /> */ ?>
                        
                        <?php echo $form->end(); ?> 
                        <img class="intlc" alt="" src="/img/appimages/guest_topleftcrv.jpg"/>
                        <img class="intrc" alt="" src="/img/appimages/guest_toprightcrv.jpg"/>
                        <img class="inblc" alt="" src="/img/appimages/guest_botleftcrv.jpg"/>
                        <img class="inbrc" alt="" src="/img/appimages/guest_botrightcrv.jpg"/>
                    </div>
                    
                </div>
            </div>                            
        </div>                                       	  
    </div>
</div>
<!--contentstuff-->


