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

require_once ('Mage/Customer/controllers/AccountController.php');

class Icemax_AESocialLogin_ApiController extends Mage_Customer_AccountController {

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     *
     * This is a clone of the one in Mage_Customer_AccountController
     * with two added action names to the preg_match regex to prevent
     * redirects back to customer/account/login when using AE
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
        if (!preg_match('/^(xdcomm|login|duplicate|create|login|logoutSuccess|forgotpassword|forgotpasswordpost|confirm|confirmation)/i', $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    public function indexAction() {
        $this->_redirect('customer/account/index');
    }

    /**
     * Login/Registation process
     */
    public function loginAction() {
        $session = $this->_getSession();
        
        $redirect_url = $this->_getRefererUrl();
        if (!empty($redirect_url) && $this->_isUrlInternal($redirect_url)) {
            $session->setBeforeAuthUrl($redirect_url);
        }
        
        // Redirect if user is already authenticated
        if ($session->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        $token = $this->getRequest()->get('accessToken');
        $auth_info = Mage::helper('aesociallogin/apicall')->AuthCall($token);

        if (!empty($auth_info->data->ID)) {
            $customer = Mage::helper('aesociallogin/identifiers')->get_customer_by_service($auth_info->services[0]->ID);

            if ($customer === false) {
                $customer = Mage::helper('aesociallogin/identifiers')->get_customer($auth_info->data->ID);

                $email     = !empty($auth_info->data->Email)     ? $auth_info->data->Email     : '';
                $firstName = !empty($auth_info->data->FirstName) ? $auth_info->data->FirstName : '';
                $lastName  = !empty($auth_info->data->Surname)   ? $auth_info->data->Surname   : '';

                if ($customer === false && $email) {
                    $customer = Mage::helper('aesociallogin/identifiers')->get_customer_by_email($email);
                }

                $profile   = Mage::helper('aesociallogin')->buildProfile($auth_info);
                Mage::getSingleton('aesociallogin/session')->setIdentifier($profile);
            }

            if ($customer === false) {

                $isSeamless = ('1' == Mage::getStoreConfig('aesociallogin/options/seamless'));
                if ($isSeamless && $email && $firstName && $lastName) {
                    $customer = Mage::getModel('customer/customer')->setId(null);
                    $customer->getGroupId();
                    $customer->setFirstname($firstName);
                    $customer->setLastname($lastName);
                    $customer->setEmail($email);

                    $password = md5('Icemax_AESocialLogin' . Mage::helper('aesociallogin')->rand_str(12));

                    $_SERVER['REQUEST_METHOD'] = 'POST';
                    $this->_request->setPost(array(
                        'email'        => $email,
                        'password'     => $password,
                        'confirmation' => $password
                    ));
                    Mage::register('current_customer', $customer);

                    $this->_forward('createPost');
                } else {
                    $this->loadLayout();
                    $block = Mage::getSingleton('core/layout')->getBlock('customer_form_register');
                    if ($block !== false) {
                        $form_data = $block->getFormData();

                        $form_data->setEmail($email);
                        $form_data->setFirstname($firstName);
                        $form_data->setLastname($lastName);
                    }

                    $this->renderLayout();
                }
                return;
            } else {
                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                $this->_loginPostRedirect();
            }
        } else {
            $session->addWarning('Could not retrieve account info. Please try again.');
            $this->_redirect('customer/account/login');
        }
    }

    public function createPostAction() {
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
            $email     = $this->getRequest()->getPost('email');
            $firstname = $this->getRequest()->getPost('firstname');
            $lastname  = $this->getRequest()->getPost('lastname');
            Mage::getSingleton('aesociallogin/session')->setEmail($email)->setFirstname($firstname)->setLastname($lastname);
            $this->_redirect('aesociallogin/api/duplicate');
        }

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
        $block->setUsername(Mage::getSingleton('aesociallogin/session')->getEmail());
        $block->getFormData()->setEmail(Mage::getSingleton('aesociallogin/session')->getEmail());
        $block->getFormData()->setFirstname(Mage::getSingleton('aesociallogin/session')->getFirstname());
        $block->getFormData()->setLastname(Mage::getSingleton('aesociallogin/session')->getLastname());
        $this->renderLayout();
    }

    public function loginPostAction() {
        parent::loginPostAction();
    }

    protected function _loginPostRedirect() {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            if ($profile = Mage::getSingleton('aesociallogin/session')->getIdentifier()) {
                $customer = $session->getCustomer();
                Mage::helper('aesociallogin/identifiers')->save_identifier($customer->getId(), $profile);
                Mage::getSingleton('aesociallogin/session')->setIdentifier(false);
            }
        }
        $redirect_url = $session->getBeforeAuthUrl(true);

        if (!empty($redirect_url)) {
            $this->_redirectUrl($redirect_url);
            return;
        }
        parent::_loginPostRedirect();
    }

}
