<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * @category    Icemax
 * @package     Icemax_AESocialLogin
 * @copyright   Copyright (c) 2015 Icemax, Inc.
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Icemax_AESocialLogin_Block_Accountdata extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {

        $html = $this->_getHeaderHtml($element);

        $html .= $this->_getFieldHtml($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldHtml($fieldset) {

        if (Mage::helper('aesociallogin')->isAESocialLoginEnabled() === false)
            return '<p>Module not enabled. Please set "Enabled" to "Yes" and enter your API key above.</p>';

        $content = '<p><a href="' . Mage::helper('adminhtml')->getUrl('aesocialloginadmin/adminhtml_refresh/refresh') . '">Click Here to refresh</a></p>';

        $value = Mage::getStoreConfig('aesociallogin/vars/connection_info');
        if ($value == 1) {
            $content .= '<p>The API key is correct. Connection works good.</p>';
        } else {
            $content .= '<p>Something wrong with the connection.</p>';
            if ($value) {
                $content .= '<p>Error: '.$value.'</p>';
            }
        }

        return $content;
    }

}
