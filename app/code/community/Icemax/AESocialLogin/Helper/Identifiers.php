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

class Icemax_AESocialLogin_Helper_Identifiers extends Mage_Core_Helper_Abstract {

    /**
     * Assigns a new identifier to a customer
     *
     * @param int $customer_id
     * @param string $identifier
     */
    public function save_identifier($customer_id, $profile) {

        /**
         * Make the save
         *
         */
        try {
            Mage::getModel('aesociallogin/identifiers')
                ->setIdentifier($profile['identifier'])
                ->setServiceIdentifier($profile['service_identifier'])
                ->setProvider($profile['provider'])
                ->setProfileName($profile['profile_name'])
                ->setCustomerId($customer_id)
                ->save();
        } catch (Exception $e) {
            echo "Could not save: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Gets a customer by identifier
     *
     * @param string $identifier
     * @return Mage_Customer_Model_Customer
     */
    public function get_customer($identifier) {
        $customer_id = Mage::getModel('aesociallogin/identifiers')
            ->getCollection()
            ->addFieldToFilter('identifier', $identifier)
            ->getFirstItem();
        $customer_id = $customer_id->getCustomerId();
        if ((int) $customer_id > 0) {
            $customer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('entity_id', $customer_id)
                ->getFirstItem();
            return $customer;
        }
        return false;
    }

    /**
     * Gets a customer by service identifier
     *
     * @param string $service_identifier
     * @return Mage_Customer_Model_Customer
     */
    public function get_customer_by_service($service_identifier) {
        $customer_id = Mage::getModel('aesociallogin/identifiers')
            ->getCollection()
            ->addFieldToFilter('service_identifier', $service_identifier)
            ->getFirstItem();
        $customer_id = $customer_id->getCustomerId();
        if ((int) $customer_id > 0) {
            $customer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('entity_id', $customer_id)
                ->getFirstItem();
            return $customer;
        }
        return false;
    }

    /**
     * Gets a customer by email
     *
     * @param string $email
     * @return Mage_Customer_Model_Customer
     */
    public function get_customer_by_email($email) {

        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('email', $email)
            ->getFirstItem();
            
        $customer_id = $customer->getId();
        if ((int) $customer_id > 0) {
            return $customer;
        }
        
        return false;
    }

    /**
     * Gets an identifiers by customer ID
     *
     * @param string $customer_id
     * @return Icemax_AESocialLogin_Model_Identifiers
     */
    public function get_identifiers($customer_id) {
        if ((int) $customer_id > 0) {
            $identifiers = Mage::getModel('aesociallogin/identifiers')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customer_id);

            return $identifiers;
        }

        return false;
    }

    /**
     * Delete an identifier
     *
     * @param int $id
     */
    public function delete_identifier($id) {
        $customer_id = Mage::getSingleton('customer/session')
            ->getCustomer()
            ->getId();

        $identifier = Mage::getModel('aesociallogin/identifiers')
            ->getCollection()
            ->addFieldToFilter('appreciationengine_identifier_id', $id)
            ->getFirstItem();
        if ($identifier->getCustomerId() == $customer_id) {
            try {
                $identifier->delete();
            } catch (Exception $e) {
                echo "Could not delete: $e";
            }
        }
    }

    /**
     * Delete all identifiers
     *
     * @param object $customer
     */
    public function delete_all_identifiers($customer) {
        $customer_id = $customer->getId();
        if ((int) $customer_id > 0) {
            $identifiers = $this->get_identifiers($customer_id);
            foreach ($identifiers as &$identifier) {
                try {
                    $identifier->delete();
                } catch (Exception $e) {
                    echo "Could not delete: $e";
                }
            }
        }
    }

}
