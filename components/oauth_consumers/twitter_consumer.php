<?php

class TwitterConsumer extends AbstractConsumer {
    public function __construct() {
        parent::__construct(Configure::read("TW_CON_KEY"), Configure::read("TW_CON_SECRET"));
    }
}
?>
