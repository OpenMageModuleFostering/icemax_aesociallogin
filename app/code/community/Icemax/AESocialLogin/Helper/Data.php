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

class Icemax_AESocialLogin_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Returns whether the Enabled config variable is set to true
     *
     * @return bool
     */
    public function isAESocialLoginEnabled() {
        if (Mage::getStoreConfig('aesociallogin/options/enable') == '1' && strlen(Mage::getStoreConfig('aesociallogin/options/apikey')) > 0)
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
        return Mage::getBaseUrl('skin') . "frontend/icemax";
    }

    /**
     * Build profile array which contain information about current customer
     *
     * @param object $auth_info
     * @return array
     */
    public function buildProfile($auth_info) {

        if (!empty($auth_info->data->Username))
            $profile_name = $auth_info->data->Username;

        else if (!empty($auth_info->data->Email))
            $profile_name = $auth_info->data->Email;

        else
            $profile_name = $auth_info->services[0]->Service;

        return array('provider'           => $auth_info->services[0]->Service,
                     'identifier'         => $auth_info->data->ID,
                     'service_identifier' => $auth_info->services[0]->ID,
                     'profile_name'       => $profile_name
        );
    }
}
