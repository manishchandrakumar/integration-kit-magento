<?php

class Onegini_Onegini_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Returns whether the Enabled config variable is set to true
     *
     * @return bool
     */
    public function isOneginiEnabled() {
        if (Mage::getStoreConfig('onegini/options/enable') == '1'
            && strlen(Mage::getStoreConfig('onegini/options/clientId')) > 1
            && strlen(Mage::getStoreConfig('onegini/options/clientSecret') > 1))
            return true;

        return false;
    }

    /**
     * Returns random alphanumber string
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
        $chars_length = (strlen($chars) - 1);

        $string = $chars{rand(0, $chars_length)};

        for ($i = 1; $i < $length; $i = strlen($string)) {
            $r = $chars{rand(0, $chars_length)};

            if ($r != $string{$i - 1})
                $string .= $r;
        }

        return $string;
    }

    /**
     * Returns the url of skin directory containing scripts and styles
     *
     * @return string
     */
    public function _baseSkin() {
        return Mage::getBaseUrl('skin') . "frontend/onegini";
    }

    public function buildOneginiProfile($access_token) {
        return array('identifier' => $access_token->profile->reference_id);
    }

}
