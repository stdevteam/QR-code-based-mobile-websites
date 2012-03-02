<div class="div_contents_about" style=" padding-bottom:80px;  ">

 		
			<div class="div_howitworks" >
				<div class="howitworks_left">
					<p class="p1">
					<font class="f2"><?php  echo $content[0]['articles']['name']; ?></font><br>
					</p>

					<hr>

					<p class="p2"><br> 
                                            <?php echo $content[0]['articles']['text']; ?>
					</p>	
						<p class="p2">
                                                    <br />
                                                    <?php if($this->Session->check('error')){
                                                        echo '<p style="color: red;" class="error">'.$this->Session->read('error').'</p>';
                                                        $this->Session->delete('error');
                                                     } ?>
                                                    <?php if($this->Session->check('message')){ 
                                                        echo '<p class="message" style="color: green;">'.$this->Session->read('message').'</p>';
                                                        $this->Session->delete('message');
                                                     } ?>
                                                <br /> <br /> 
						Join the discussion: 
						<div class="email_div">
                                                    <?php echo $form->create('Content',array(
                                                        'url' => array('controller' => 'landings','action' => 'subscribe'),
                                                    )); ?>
						<?php // <input class="email_input" id="inputEmail" name="email" type="text" value="Enter email" /> ?>
						<?php 
                                                echo $form->input('email', array(
                                                    'type' => 'text',
                                                    'value' => '',
                                                    'label' => false,
                                                    'class' => 'email_input'
                                                ));
                                                ?>
                                                <?php echo $form->submit('', array(
                                                        'label' => false,
                                                        'value'=> false, 
                                                        'style' => 'border:none;  background: url(\'/img/appimages/submit_btn.png\')  no-repeat top left; padding: 20px 89px; position: absolute; bottom: -70px; left: 206px;'
                                                )); ?>
                                                <?php echo $form->end(); ?>
                                                </div>
						</p>
				</div>
				<div class="howitworks_right">
					<p class="p1">
					<font class="f2"><?php echo $content[1]['articles']['name']; ?></font><br>
					</p>

					<hr>
					<p class="p2"><br> 
                                            <?php echo $content[1]['articles']['text']; ?>
					</p>

					<p class="p1"><br>
					<font class="f2">Learn More</font><br>
					</p>

					<hr>

					<p class="p2"><br>
					<a href="/HowItWorks">How It Works</a>
					<br><a href="/BenefitsAndSafety">Benefits and Safety</a>
					<br><a href="/WhyHost">Why Host</a>
					<br><a href="/contents/About">About</a>
					<br><a href="/contents/Contact">FAQ/Contact</a>
					 
					</p>


				</div>
			</div>
 
		</div>  
		<?php echo $this->Session->flash(); ?>
		 
