<?php

require_once ('Mage/Customer/controllers/AccountController.php');

class Onegini_Onegini_AuthController extends Mage_Customer_AccountController {

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     *
     * This is a clone of the one in Mage_Customer_AccountController
     * with two added action names to the preg_match regex to prevent
     * redirects back to customer/account/login when using onegini
     * authentication links. Rather than calling parent::preDispatch()
     * we explicitly call Mage_Core_Controller_Front_Action to prevent the
     * original preg_match test from breaking our auth process.
     *
     */
    public function preDispatch() {
        // a brute-force protection here would be nice

        Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!preg_match('/^(callback|duplicate|create|login|logoutSuccess|forgotpassword|forgotpasswordpost|confirm|confirmation)/i', $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
        else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    public function indexAction() {
        $this->_redirect('customer/account/index');
    }

    /**
     * onegini Callback
     */
    public function callbackAction() {

        if ($this->getRequest()->isGet()) {
            Mage::log("Request has type GET, should resend via POST");
            $code = $this->getRequest()->getParam('code');

            echo "<form action='' id='resend_access_grant' method='post'>";
            echo "<input type='hidden' name='code' value='$code'><input type='submit' value='continue'>";
            echo "</form>";
            echo "<script>document.getElementById('resend_access_grant').submit();document.getElementById('resend_access_grant').style.visibility='hidden';</script>";
            return;
        }

        $session = $this->_getSession();
        // Redirect if user is already authenticated
        if ($session->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        // TODO: validate state parameter
        $access_grant = $this->getRequest()->getParam('code');

        if (!isset($access_grant)) {
            $session->addError('Could not authenticate using onegini. Please try again.');
            $this->_redirect('customer/account/login');
        }
        $access_token = Mage::helper('onegini/api')->getAccessToken($access_grant);

        if (isset($access_token->access_token) && isset($access_token->expires_in) && $access_token->expires_in > 0) {
            $customer = Mage::helper('onegini/identifiers')->get_customer($access_token->profile->reference_id);
            //$customer = "";

            if ($customer === false) {

                $email = Mage::helper('onegini/api')->getPrimaryEmail($access_token);
                $firstName = Mage::helper('onegini/api')->getFirstName($access_token);
                $lastName = Mage::helper('onegini/api')->getLastName($access_token);
                $phoneNumber = Mage::helper('onegini/api')->getPrimaryPhoneNumber($access_token);
                $profile = Mage::helper('onegini')->buildOneginiProfile($access_token);
                Mage::getSingleton('onegini/session')->setIdentifier($profile);

                Mage::log("Email: " . $email);
                Mage::log("First name: " . $firstName);
                Mage::log("Last name: " . $lastName);

                if (Mage::helper('onegini/api')->hasAddress($access_token)) {

                    Mage::log("Address available");
                    $address = Mage::getModel('customer/address');

                    $street = Mage::helper('onegini/api')->getAddressStreet($access_token);
                    $postalCode = Mage::helper('onegini/api')->getAddressPostalCode($access_token);
                    $city = Mage::helper('onegini/api')->getAddressCity($access_token);
                    $countryCode = Mage::helper('onegini/api')->getAddresscountryCode($access_token);
                    Mage::log("Street: " . $street);
                    Mage::log("PostalCode: " . $postalCode);
                    Mage::log("City: " . $city);
                    Mage::log("Country: " . $countryCode);

                    $address->setFirstname($firstName);
                    $address->setLastname($lastName);
                    $address->setCity($city);
                    $address->setStreet($street);
                    $address->setPostcode($postalCode);
                    $address->setCountryId($countryCode);
                    $address->setIsDefaultBilling(true);
                    $address->setIsDefaultShipping(true);

                    if ($phoneNumber) {
                        $address->setTelephone($phoneNumber);
                    }
                }

                // TODO: Create an account merging process
                //$existing = Mage::getModel('customer/customer')
                //    ->getCollection()
                //    ->addFieldToFilter('email', $email)
                //    ->getFirstItem();

//                if (Mage::getStoreConfig('onegini/options/seamless') == '1'
//                    && $email && $firstName && $lastName) {

                    Mage::log("In Seamless registration");

                    $customer = Mage::getModel('customer/customer')->setId(null);
                    $customer->getGroupId();
                    $customer->setFirstname($firstName);
                    $customer->setLastname($lastName);
                    $customer->setEmail($email);

                    if (isset($address)) {
                        Mage::log("Setting address");
                        $customer->addAddress($address);
                    }

                    $password = md5('onegini_' . Mage::helper('onegini')->rand_str(12));
                    $_POST['password'] = $password;
                    $_POST['confirmation'] = $password;

                    Mage::register('current_customer', $customer);
                    $this->_forward('createPost');
                //* NON SEAMLESS REGISTRATION FEATURE DISABLED
//                }
//                else {
//                    Mage::log("Not in seamless registration, show the prefilled form");
//                    /*$this->loadLayout();
//                    $block = Mage::getSingleton('core/layout')->getBlock('customer_form_register');
//                    if ($block !== false) {
//                        $form_data = $block->getFormData();
//
//                        $form_data->setEmail($email);
//                        $form_data->setFirstname($firstName);
//                        $form_data->setLastname($lastName);
//                    }
//
//                    $this->renderLayout();*/
//                    $this->_redirect('onegini/auth/notsupported');
//                }
                return;
            } else {
                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                $this->_loginPostRedirect();
            }
        }
        else {
            echo "WRONG";
            $session->addWarning('Could not retrieve account info. Please try again.');
            $this->_redirect('customer/account/login');
        }
    }

    public function createPostAction() {
        Mage::log("in create post");

        $session = $this->_getSession();
        parent::createPostAction();

        $messages = $session->getMessages();
        $isError = false;

        foreach ($messages->getItems() as $message) {
            if ($message->getType() == 'error') {
                $isError = true;
            }
        }

        if ($isError) {
            $email = $this->getRequest()->getPost('email');
            $_POST['email'] = $email;
//            $firstname = $this->getRequest()->getPost('firstname');
//            $lastname = $this->getRequest()->getPost('lastname');
//          Mage::getSingleton('onegini/session')->setEmail($email)->setFirstname($firstname)->setLastname($lastname);

            Mage::log("Show error page");
            $this->_redirect('onegini/auth/duplicate');
        }

        Mage::log("returning");
        return;
    }

    public function duplicateAction() {
        $session = $this->_getSession();

        // Redirect if user is already authenticated
        if ($session->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $block = Mage::getSingleton('core/layout')->getBlock('customer_form_register');
        $block->setUsername(Mage::getSingleton('onegini/session')->getEmail());
        $block->getFormData()->setEmail(Mage::getSingleton('onegini/session')->getEmail());
        $block->getFormData()->setFirstname(Mage::getSingleton('onegini/session')->getFirstname());
        $block->getFormData()->setLastname(Mage::getSingleton('onegini/session')->getLastname());
        $this->renderLayout();
    }

    public function loginPostAction() {
        parent::loginPostAction();
    }

    protected function _loginPostRedirect() {
        Mage::log("in login post redirect");
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            if ($profile = Mage::getSingleton('onegini/session')->getIdentifier()) {
                $customer = $session->getCustomer();
                Mage::helper('onegini/identifiers')->save_identifier($customer->getId(), $profile);
                Mage::getSingleton('onegini/session')->setIdentifier(false);
            }
        }

        parent::_loginPostRedirect();
    }

    public function removeIdAction() {
        $session = $this->_getSession();
        $id = $this->getRequest()->getParam('identifier');

        Mage::helper('onegini/identifiers')->delete_identifier($id);
        $session->addSuccess('Provider removed');
        $this->_redirect('customer/account');
    }

}
