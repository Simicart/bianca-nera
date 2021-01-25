<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface Customer
 * @api
 */
interface CustomerInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const CUSTOMER_ID =         "CustomerID";
    const FIRST_NAME =          "FirstName";
    const LAST_NAME =           "LastName";
    const HOME_PHONE =          "HomePhone";
    const MOBILE_PHONE =        "MobilePhone";
    const BIRTH_DATE =          "BirthDate";
    const EMAIL =               "Email";
    const POINTS =              "Points";
    const PR_POINTS =           "PrPoints";
    const BRAND =               "BranchEnNames";
    /**#@-*/

    /**
     * Set first name
     *
     * @param string $value
     * @return $this
     */
    public function setFirstname($value);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set last name
     *
     * @param string $value
     * @return $this
     */
    public function setLastname($value);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob();

    /**
     * Set date of birth
     *
     * @param string $value
     * @return $this
     */
    public function setDob($value);

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @param string $value
     * @return $this
     */
    public function setEmail($value);

    /**
     * Get brand name
     *
     * @return string
     */
    public function getBrand();

    /**
     * Set brand name 
     *
     * @param string $value
     * @return $this
     */
    public function setBrand($value);

    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set phone number 
     *
     * @param string $value
     * @return $this
     */
    public function setPhone($value);
}