<div id="content-container">
        <div id="content">
                <div class="c-elements">		
                        <div class="box-element">
                                <div class="box-head-light">Mobile Site Add's for Project: <?php echo $ProjectName; ?><a href="" class="collapsable"></a></div>
                                <div class="box-content no-padding">
                                    <?php echo $form->create('Projects', array('url' => 'mobile/'.$projectId)); ?>
                    <fieldset>
                        <section>
                            <div class="section-left-s">
                                <label for="text_field">Active</label>
                            </div>                                  
                            <div class="section-right">
                                <div class="section-input">
                                  <?php echo $this->Form->input('active',array(
                                            'type'  => 'radio',
                                            'options' =>array(
                                                '1' => 'Yes',
                                                '0' => 'No'
                                                ),
                                            'legend'=>false
                                        )) ?>
                                  </div>
                            </div>
                            <div class="clearfix"></div>
                        </section>
                        <section>
                            <div class="section-left-s">
                            <label for="select">Template</label> 
                            </div>                                  
                            <div class="section-right">                                        
                                <div class="section-input i-transform">
                                     <?php
                                        echo $this->Form->input('template', array(
                                                'type' => 'select',
                                                'empty' => 'Please Select',
                                                'options' => array(
                                                    'Template 1' => 'Template 1', 
                                                    'Template 2' => 'Template 2'
                                                ),
                                                'label' => false,
                                                'class' => "form-select",
                                               
                                        )); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </section>
                        <section>
                            <div class="section-left-s">
                                <label for="text_field">Facebook Page</label>
                            </div>                                  
                            <div class="section-right">
                                <div class="section-input">
                                     <?php echo $form->input('fbPage', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text'
                                        )); 
                                    ?>
                                  </div>
                            </div>
                            <div class="clearfix"></div>
                        </section>    
                        <section>
                            <div class="section-left-s">
                               <label for="textarea">Twiter Page</label>
                            </div>                                  
                            <div class="section-right">           
                               <div class="section-input">
                                   <?php echo $form->input('twitPage', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text',
                                        )); 
                                    ?>
                               </div> 
                            </div>
                            <div class="clearfix"></div>
                        </section>
                        <section>
                            <div class="section-left-s">
                               <label for="textarea">Phone number</label>
                            </div>                                  
                            <div class="section-right">           
                               <div class="section-input">
                                   <?php echo $form->input('phNumber', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text',
                                        )); 
                                    ?>
                               </div> 
                               
                            </div>
                            <div class="clearfix"></div>
                        </section>
                        <section>
                            <div class="section-left-s">
                               <label for="textarea">Do not Show Phone Number</label>
                            </div>                                  
                            <div class="section-right">           
                               <div class="section-input">
                                <?php 
                        echo $this->Form->input('showNumber',array(
                            'type'  => 'checkbox',     
                            'label' => false
                        )) ?>
                               </div> 
                               
                            </div>
                            <div class="clearfix"></div>
                        </section>
                        <section>
                             <?php 
                      echo $form->submit('Submit', array(
                           'class'=> 'i-button',
                           'label' => false,
                           'value'=> false, 
                           )); 
                      ?>	
                            <div class="clearfix"></div>
                        </section>
                    </fieldset>
                                    <?php echo $this->Form->end(); ?>
                                </div>
                        </div>
                </div>
        </div>
</div>