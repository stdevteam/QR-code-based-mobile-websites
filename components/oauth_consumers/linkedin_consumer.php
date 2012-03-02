<?php

class TwitterConsumer extends AbstractConsumer {
    public function __construct() {
        parent::__construct(Configure::read("LN_CON_KEY"), Configure::read("LN_CON_SECRET"));
    }
}
?>
