<?php
//process the scripts
ob_start(); ?>
<?php //password verification ?>
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
                <div class="signin">
                    <?php echo $this->element('note'); ?>
                    <div class="user">
                        <div class="in_whitebox user_login">
                            <div class="col">
                                <label>New Password:</label>
                                <div class="field">
                                    <img src="/img/appimages/desfield_leftcrv.jpg" alt="" />
                                    <span class="in">
                                        <?php 
                                                echo $form->input('password'	, array(
                                                        'id' => 'p1',
                                                        'label' => false,
                                                        'type' => 'password',
                                                        'autocomplete' => 'off',
                                                        'value' => '',
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
                                                        'type' => 'password',
                                                        'autocomplete' => 'off',
                                                        'value' => '',
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
                                    'style' => 'border:none;  background: url(\'/img/appimages/reset_btn.png\')  no-repeat top left;  padding: 20px 95px;',
                                    'value'=>'', 
                                )); 
                            ?>

                            <img class="intlc" alt="" src="/img/appimages/guest_topleftcrv.jpg" />
                            <img class="intrc" alt="" src="/img/appimages/guest_toprightcrv.jpg" />
                            <img class="inblc" alt="" src="/img/appimages/guest_botleftcrv.jpg" />
                            <img class="inbrc" alt="" src="/img/appimages/guest_botrightcrv.jpg" />
                        </div>
                        <div class="clear"></div>
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
