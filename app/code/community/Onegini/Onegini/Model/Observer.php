<?php

class Onegini_Onegini_Model_Observer {

    public function addIdentifier($observer) {
        if ($profile = Mage::getSingleton('onegini/session')->getIdentifier()) {
            Mage::helper('onegini/identifiers')
                ->save_identifier($observer->getCustomer()->getId(), $profile);
            Mage::getSingleton('onegini/session')->setIdentifier(false);
        }
    }

    public function removeIdentifiers($observer) {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        Mage::helper('onegini/identifiers')->delete_all_identifiers($customer);
    }
}
