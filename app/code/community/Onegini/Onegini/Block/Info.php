<?php

class Onegini_Onegini_Block_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {

        $html = $this->_getHeaderHtml($element);

        $html.= $this->_getFieldHtml($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldHtml($fieldset) {
        $content = '<p>onegini for Magento ' . Mage::getConfig()->getModuleConfig("onegini")->version . '</p>';
        $content.= '<p>This extension is developed by <a href="http://www.onegini.com/" target="_blank">onegini</a>. Please refer to our <a href="http://www.onegini.com/documentation/plugins-modules/magento" target="_blank">Documentation</a> on how to install and configure this extension.</p>';
        $content.= '<p>Copyright &copy ' . date("Y") . ' <a href="http://www.onegini.com/" target="_blank">onegini B.V.</a></p>';

        return $content;
    }

}
