<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simicustomize\Override\Eav\Model\Entity\Attribute;


/**
 * Entity/Attribute/Model - attribute abstract
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class AbstractAttribute extends \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
{
    /* Simi */
    const KEY_IS_GLOBAL = 'is_global';
    /**
     * Return is attribute global
     *
     * @return integer
     */
    public function getIsGlobal()
    {
        return $this->_getData(self::KEY_IS_GLOBAL);
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL;
    }
}
