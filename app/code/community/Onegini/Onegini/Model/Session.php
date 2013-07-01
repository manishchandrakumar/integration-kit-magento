<?php

class Onegini_Onegini_Model_Session extends Mage_Core_Model_Session_Abstract {

    public function __construct() {
        $namespace = 'onegini';
        $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

        $this->init($namespace);
        Mage::dispatchEvent('onegini_session_init', array('onegini_session' => $this));
    }

}
