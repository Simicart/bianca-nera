<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Heading extends Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $comment = $element->getData('comment');
        if ($comment) {
            $comment = "<p class='comment'>$comment</p>";
        }
        $html = sprintf('<td colspan="5"><h4>%s</h4>%s</td>', $element->getData('label'), $comment);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Decorate field row html
     *
     * @param AbstractElement $element
     * @param string $html
     *
     * @return string
     */
    protected function _decorateRowHtml(AbstractElement $element, $html): string
    {
        return '<tr class="system-fieldset-sub-head" id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}
