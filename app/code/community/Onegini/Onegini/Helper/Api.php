<?php

class Onegini_Onegini_Helper_Api extends Mage_Core_Helper_Abstract {

    public function getOneginiClientId() {
        return Mage::getStoreConfig('onegini/options/clientId');
    }

    public function getOneginiClientSecret() {
        return Mage::getStoreConfig('onegini/options/clientSecret');
    }

    public function getAccessToken($code) {

        $post_params = array();
        $headers = array();

        Mage::log("Code: " . $code);

        $post_params["grant_type"] = "authorization_code";
        $post_params["code"] = $code;
        $post_params["redirect_uri"] = "http://magento.innovation-district.com/onegini/auth/callback";

        $headers["Authorization"] = $this->getBasicAuthCredentials();

        $result = "Onegini: no result";
        try {
            $result = $this->postOneginiCall("token", $post_params, $headers);
        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;
    }

    private function getBasicAuthCredentials() {
        return "Basic " . base64_encode($this->getOneginiClientId() . ":" . $this->getOneginiClientSecret());
    }

    private function postOneginiCall($method, $post_params, $headers) {
        $onegini_base = "https://beta.onegini.me";
        //$onegini_base = "http://localhost:8989";

        if ($method == "token") {
            $method_fragment = "oauth/token";
        }
        else {
            throw Mage::exception('Mage_Core', "method [$method] not understood");
        }

        $url = "$onegini_base/$method_fragment";
        $method = 'POST';

        return $this->oneginiCall($url, $method, $post_params, $headers);
    }

    private function oneginiCall($url, $method = 'GET', $postParams = null, $headers = null) {
        try {
            $http = new Varien_Http_Client($url);
            $http->setHeaders($headers);
            if ($method == 'POST')
                $http->setParameterPost($postParams);

            $response = $http->request($method);
            try {
            $body = $response->getBody();
            } catch (Exception $e) {
                Mage::log("Chunking error.... strange.... lets fetch Raw Body instead");
                $body = $response->getRawBody();
            }

            Mage::log('OAuth response body: ' . $body);

            try {
                $result = json_decode($body);
            } catch (Exception $e) {
                Mage::log('Exception.....');
                throw Mage::exception('Mage_Core', $e);
            }

            if ($result) {
                return $result;
            }
            else {
                throw Mage::exception('Mage_Core', "something went wrong");
            }

        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }
    }

    public function getPrimaryEmail($access_token) {
        if (isset($access_token->profile) && isset($access_token->profile->email_addresses)) {
            foreach ($access_token->profile->email_addresses as $email) {
                if ($email->primary) {
                    return $email->value;
                }
            }
        }
        throw Mage::exception('Mage_Core', "Email not found in access token response");
    }

    public function getFirstName($access_token) {
        return $access_token->profile->name->first_name;
    }

    public function getLastName($access_token) {
        return $access_token->profile->name->last_name;
    }

    private function getAddress($access_token) {
        return $access_token->profile->addresses;
    }

    public function hasAddress($access_token) {
        return isset($access_token->profile->addresses);
    }

    public function getAddressStreet($access_token) {
        $address = $this->getAddress($access_token);

        $street = "";
        if (isset($address[0]->street_name)) {
            $street = $address[0]->street_name;
        }

        $house_number = "";
        if (isset($address[0]->house_number)) {
            $house_number = $address[0]->house_number;
        }

        $addition = "";
        if (isset($address[0]->house_number_addition)) {
            $addition = $address[0]->house_number_addition;
        }

        return $street . " " . $house_number . $addition;
    }

    public function getAddressCity($access_token) {
        $address = $this->getAddress($access_token);
        return $address[0]->city;
    }

    public function getAddressCountryCode($access_token) {
        $address = $this->getAddress($access_token);
        return $address[0]->country_code;
    }

    public function getAddressPostalCode($access_token) {
        $address = $this->getAddress($access_token);
        return $address[0]->postal_code;
    }

    public function getPrimaryPhoneNumber($access_token) {
        if (isset($access_token->profile) && isset($access_token->profile->phone_numbers)) {
            foreach ($access_token->profile->phone_numbers as $phone_number) {
                if ($phone_number->primary) {
                    return $phone_number->value;
                }
            }
        }
    }
}
