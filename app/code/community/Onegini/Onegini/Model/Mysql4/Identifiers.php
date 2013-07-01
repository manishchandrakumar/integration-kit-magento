<?php

class Onegini_Onegini_Model_Mysql4_Identifiers extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('onegini/identifiers', 'onegini_identifier_id');
    }

}
