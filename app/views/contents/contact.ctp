<div class="div_contents_about">
    <div class="div_howitworks">
        <div class="contact_left">
            <p class="p1">
                <font class="f2"><?php echo $content[0]['articles']['name']; ?></font><br>
            </p>          
            <hr>        
            <?php
            $counter = 0;
            foreach($content[0]['articles']['children'] as $section){ 
                ?>
                <p class="p2"><br />
                    <font class="f1"><?php echo $section['articles']['name']; ?></font><br /><br />                
                    <?php 
                    foreach($section['articles']['children'] as $item){
                        $counter++;
                    ?>                                            
                    <h2>
                        <a href="javascript:toggle('A<?php echo $counter ?>')">
                            <?php echo $item['articles']['name']; ?>
                        </a>
                    </h2>
                    <div id="A<?php echo $counter ?>" style="display:none">
                        <p class="p3">
                           <?php echo $item['articles']['text']; ?>
                        </p>
                    </div>
                    <?php } ?>
                </p>
            <?php } ?>
        </div>
        <div class="contact_right">
            <p class="p1">
                <font class="f2"><?php echo $content[1]['articles']['name']; ?></font><br />
            </p>
            <hr />
            <p class="p2"><br />
                <?php echo $content[1]['articles']['text']; ?>
            </p>          
            <p class="p1"><br />
                <font class="f2">Learn More</font><br />
            </p>
            <hr />
            <p class="p2"><br />
                <a href="/HowItWorks">How It Works</a><br />
                <a href="/BenefitsAndSafety">Benefits and Safety</a><br />
                <a href="/WhyHost">Why Host</a><br />
                <a href="/contents/About">About</a><br />
                <a href="/contents/Contact">FAQ/Contact</a>
            </p>
        </div>
    </div>
</div>