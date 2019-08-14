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

class Icemax_AESocialLogin_Helper_Apicall extends Mage_Core_Helper_Abstract {

    public function getAESocialLoginApiKey() {
        return Mage::getStoreConfig('aesociallogin/options/apikey');
    }

    public function getAESocialLoginEndpoint() {
        return Mage::getStoreConfig('aesociallogin/options/endpoint');
    }

    public function apiRefreshSave() {
        try {
            $connection_info = false;

            $api_rp = $this->apiLoginsCall();

            if (is_array($api_rp) && !empty($api_rp[0]->ID)) {
                $connection_info = 1;
            }
            if (!empty($api_rp->error->message)) {
                $connection_info = $api_rp->error->message;
            }

            Mage::getModel('core/config')
                ->saveConfig('aesociallogin/vars/connection_info', $connection_info)
                ->saveConfig('aesociallogin/vars/apikey', Mage::getStoreConfig('aesociallogin/options/apikey'));
            Mage::getConfig()->reinit();

            return true;
        } catch (Exception $e) {

            Mage::getModel('core/config')
                ->saveConfig('aesociallogin/vars/connection_info', $connection_info)
                ->saveConfig('aesociallogin/vars/apikey', Mage::getStoreConfig('aesociallogin/options/apikey'));
            Mage::getConfig()->reinit();

            Mage::getSingleton('adminhtml/session')->addWarning('Could not retrieve account info. Please try again');
        }

        return false;
    }

    public function apiLoginsCall() {

        $requestParams = array();
        $requestParams["apiKey"] = $this->getAESocialLoginApiKey();

        try {
            $result = $this->apiCallInit("logins", $requestParams, 'GET');
        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;
    }

    public function AuthCall($token) {

        $requestParams = array();

        $requestParams["accessToken"] = $token;
        $requestParams["apiKey"]      = $this->getAESocialLoginApiKey();
        $requestParams["details"]     = 1;

        try {
            $result = $this->apiCallInit("auth_info", $requestParams, 'POST');
        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;
    }

    private function apiCallInit($api_method, $requestParams, $method = 'GET') {

        $api_base = $this->getAESocialLoginEndpoint();

        if ($api_method == "auth_info") {
            $method_fragment = "api/auth";
        }
        elseif ($api_method == "logins") {
            $method_fragment = "api/logins";
        }
        else {
            throw Mage::exception('Mage_Core', "method [$method] not understood");
        }

        $url = "$api_base/$method_fragment";

        return $this->apiCall($url, $method, $requestParams);
    }

    private function apiCall($url, $method = 'GET', $requestParams = null) {

        try {

            $http = new Varien_Http_Client($url);
            $http->setAdapter(new Varien_Http_Adapter_Curl());
            $http->setHeaders(array("Accept-encoding" => "identity"));
            if ($method == 'POST')
                $http->setParameterPost($requestParams);
            if ($method == 'GET')
                $http->setParameterGet($requestParams);
            $response = $http->request($method);

            $body = $response->getBody();
            //print_r($body);print_r($url);print_r($response);print_r($method);die();
            try {
                $result = json_decode($body);
            } catch (Exception $e) {
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

}
