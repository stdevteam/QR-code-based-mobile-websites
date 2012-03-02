<?php echo $this->element('dashboard_menu_new'); ?>
<div id="left-side">
    <div class="email-settings">
        <?php /*
        <h1 class="seablue">Emails Settings</h1>
            <?php 
            echo $form->create(
                    'EmailSetting',array(
                        'action' => 'index',
                        'inputDefaults' => array(
                            'label' => false, 
                            'div' => false
                            )
                        )
                    ); 
            ?>
        Notify me when: <br />                  
        <div class="my-skinnable-select1">
            <?php 
            echo $form->input(
                'received_review', array(
                    'type'  => 'checkbox',
                    'class' => 'checkbox'
                    )
                ); 
            ?>
            I receive a message from another person
        </div>
        <br class="clear" />
        <div class="my-skinnable-select1">
            <?php 
            echo $form->input(
                'upcoming_reservation', array(
                    'type'  => 'checkbox',
                    'class' => 'checkbox'
                    )
                ); 
            ?>
            My outstanding reservation request is accepted or declined. 
        </div>
        <br class="clear" />
        <div class="my-skinnable-select1">
        <?php 
        echo $form->input(
                'newsletter', array(
                    'type'  => 'checkbox',
                    'class' => 'checkbox'
                    )
                ); 
        ?>
            DogVacay has periodic offers and deals on <span class="pinklink">really cool destinations </span>
        </div>
        <br class="clear" />
        <div class="my-skinnable-select1">
            <?php 
            echo $form->input(
                'news', array(
                    'type'  => 'checkbox',
                    'class' => 'checkbox'
                    )
                ); 
            ?>
            DogVacay has <span class="pinklink">company news</span>, as well as periodic emails 
        </div>   
        <div class="right">
            <?php 
            echo $form->submit(
                'Save Email Settings', array(
                    'value'=> false, 
                    'style' => 'float:right;',
                    'class' => 'inputf'
                    )
                ); 
            ?>
        </div>
        
        <br class="clear" />               
        <hr class="hrr" />
        <?php echo $form->end(); ?>
         * */?>         
        <?php echo $this->element('note'); ?>
        <?php 
        echo $form->create(
                'User', array(
                    'action' => 'settings',
                    'inputDefaults' => array(
                        'label' => false, 
                        'div' => false
                        )
                    )
                ); 
        ?>
        <div class="left">
            <h1 class="seablue">Change Password</h1>  
            <span class="seablue">Old Password:</span>
                <?php
                echo $form->input(
                    'oldPassword', array(	
                        'type'  => 'password',
                        'class' => 'inputss',
                        'autocomplete' => 'off',
                        'value'  => '',
                        'style' => 'margin-left:33px;'
                        )
                    ); 
                ?>
            <br />
            <span class="seablue">News Password:</span>
                <?php 
                echo $form->input(
                    'newPassword', array(	
                        'type'  => 'password',
                        'class' => 'inputss',
                        'autocomplete' => 'off',
                        'value'  => '',
                        'style' => 'margin-left:19px;'
                        )
                    ); 
                ?>
            <br />
            <span class="seablue">Confirm Password:</span>
                <?php 
                echo $form->input(
                    'confirmPassword', array(	
                        'type'  => 'password',
                        'autocomplete' => 'off',
                        'value'  => '',
                        'class' => 'inputss'
                        )
                    ); 
                ?>
            <br />
        </div>
        <br class="clear" />
        <div class="right">                    
            <?php 
            echo $form->submit(
                'Change Password', array(
                    'value' => false, 
                    'style' => 'float:right;',
                    'class' => 'inputf'
                    )
                ); 
            ?>
        </div>
        <?php echo $form->end(); ?>
    </div>
    <br class="clear" />
    <hr class="hrr" />
<!-- EMAIL SETTINGS -->  
<!-- end-->
</div>
<?php //echo $this->element('right_panel_default'); ?>