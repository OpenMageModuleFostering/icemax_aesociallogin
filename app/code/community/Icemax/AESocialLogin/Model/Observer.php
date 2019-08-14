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

class Icemax_AESocialLogin_Model_Observer {

    public function addIdentifier($observer) {
        if ($profile = Mage::getSingleton('aesociallogin/session')->getIdentifier()) {
            Mage::helper('aesociallogin/identifiers')
                ->save_identifier($observer->getCustomer()->getId(), $profile);
            Mage::getSingleton('aesociallogin/session')->setIdentifier(false);
        }
    }

    public function removeIdentifiers($observer) {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        Mage::helper('aesociallogin/identifiers')->delete_all_identifiers($customer);
    }

    public function onConfigSave($observer) {
        if (Mage::getStoreConfig('aesociallogin/vars/apikey') != Mage::getStoreConfig('aesociallogin/options/apikey')) {
            Mage::helper('aesociallogin/apicall')->apiRefreshSave();
        }
    }

}
