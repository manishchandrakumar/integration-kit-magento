<?php

class Onegini_Onegini_Block_Auth extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    function auth_element() {
        $client_id = Mage::getStoreConfig('onegini/options/clientId');
        $auth_url = "https://beta.onegini.me/oauth/authorize?response_type=code&redirect_uri=http://http://ec2-54-247
        -20-26.eu-west-1.compute.amazonaws.com/onegini/auth/callback&client_id=";
        $auth_url .= $client_id;
        return '<div><a href="' . $auth_url . '"></a></div>';
    }

    protected function _toHtml() {
        $content = '';
        if (Mage::getSingleton('customer/session')->isLoggedIn() == false)
            $content = $this->auth_element();
        return $content;
    }

}
