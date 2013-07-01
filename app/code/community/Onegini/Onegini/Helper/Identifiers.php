<?php

class Onegini_Onegini_Helper_Identifiers extends Mage_Core_Helper_Abstract {

    /**
     * Assigns a new identifier to a customer
     *
     * @param int $customer_id
     * @param string $identifier
     */
    public function save_identifier($customer_id, $profile) {

        /**
         * Make the save
         *
         */
        try {
            Mage::getModel('onegini/identifiers')
                ->setIdentifier($profile['identifier'])
                ->setProfileName($profile['profile_name'])
                ->setCustomerId($customer_id)
                ->save();
        } catch (Exception $e) {
            echo "Could not save: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Gets a customer by identifier
     *
     * @param string $identifier
     * @return Mage_Customer_Model_Customer
     */
    public function get_customer($identifier) {
        $customer_id = Mage::getModel('onegini/identifiers')
            ->getCollection()
            ->addFieldToFilter('identifier', $identifier)
            ->getFirstItem();

        $customer_id = $customer_id->getCustomerId();
        if ((int) $customer_id > 0) {
            $customer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('entity_id', $customer_id)
                ->getFirstItem();
            return $customer;
        }

        return false;
    }

    public function get_identifiers($customer_id) {
        if ((int) $customer_id > 0) {
            $identifiers = Mage::getModel('onegini/identifiers')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customer_id);

            return $identifiers;
        }

        return false;
    }

    public function delete_identifier($id) {
        $customer_id = Mage::getSingleton('customer/session')
            ->getCustomer()
            ->getId();

        $identifier = Mage::getModel('onegini/identifiers')
            ->getCollection()
            ->addFieldToFilter('onegini_identifier_id', $id)
            ->getFirstItem();
        if ($identifier->getCustomerId() == $customer_id) {
            try {
                $identifier->delete();
            } catch (Exception $e) {
                echo "Could not delete: $e";
            }
        }
    }

    public function delete_all_identifiers($customer) {
        $customer_id = $customer->getId();
        if ((int) $customer_id > 0) {
            $identifiers = $this->get_identifiers($customer_id);
            foreach ($identifiers as &$identifier) {
                try {
                    $identifier->delete();
                } catch (Exception $e) {
                    echo "Could not delete: $e";
                }
            }
        }
    }

}
