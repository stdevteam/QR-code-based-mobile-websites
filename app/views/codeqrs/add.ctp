<div id="content-container">
        <div id="content">
                <div class="c-elements">		
                        <div class="box-element">
                                <div class="box-head-light">Data Table<a href="" class="collapsable"></a></div>
                                <div class="box-content no-padding">
                                    <?php echo $form->create('Codeqrs', array('url' => '/Codeqrs/add/'.$idProject )); ?>
                                        <?php // <form method="post" action="" class="i-validate"> ?>
                    <fieldset>
                        <section>
                            <div class="section-left-s">
                                <label for="text_field">Serial Number</label>
                            </div>                                  
                            <div class="section-right">
                                <div class="section-input">
                                     <?php echo $form->input('serialNo', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text'
                                        )); 
                                    ?>
                                    
                                  <?php // <input type="text" name="text_field" id="text_field" class="i-text required"></input> ?>
                                  </div>
                            </div>
                            <div class="clearfix"></div>
                        </section>    
                        <section>
                            <div class="section-left-s">
                                <label for="text_field">Location</label>
                            </div>                                  
                            <div class="section-right">
                                <div class="section-input">
                                     <?php echo $form->input('location', array(	
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'i-text'
                                        )); 
                                    ?>
                                   
                                  <?php // <input type="text" name="text_field" id="text_field" class="i-text required"></input> ?>
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
                           <?php // <input type="submit" name="submit" id="" class="i-button no-margin" value="Submit" /> ?>
                            <div class="clearfix"></div>
                        </section>
                    </fieldset>
                </form>
                                </div>
                        </div>
                </div>
        </div>
</div>