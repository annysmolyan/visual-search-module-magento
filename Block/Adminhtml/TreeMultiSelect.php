<?php
/**
 * Copyright (c) 2023 by https://github.com/annysmolyan
 *
 * This module provides a visual search functionality for an e-commerce store.
 * For license details, please view the GNU General Public License v3 (GPL 3.0)
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 */

declare(strict_types=1);

namespace BelSmol\VisualSearch\Block\Adminhtml;

use BelSmol\VisualSearch\Model\Config\Source\Category;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class TreeMultiSelect
 * @package BelSmol\VisualSearch\Block\Adminhtml
 */
class TreeMultiSelect extends Field
{
    /**
     * Show config category multiselect as a "treeselectjs" jquery component
     * Mind that the component selector html object has class "tree-multi-select" and hidden by default
     *
     * Documentation is here:
     * BelSmol/VisualSearch/view/base/web/lib/treeselectjs/README.md
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $categoryTree = $this->getTreeCategoryArray($element->getValues());
        $selectedValues = $element->getValue();

        $elementCode = " <div id='" . $element->getId() . "-div'></div><script>
                require([
                    'jquery',
                    'BelSmol_VisualSearch/lib/treeselectjs/js/treeselect.min'
                ], function ($, Treeselect) {

                    const treeDivSelector = '#" . $element->getId() . "-div';
                    const magentoMultiSelectSelector = '#" . $element->getId() . "';
                    const useWebsiteInput = $(magentoMultiSelectSelector).parent().parent().find('.use-default input');

                    if (useWebsiteInput.length) {
                      handleWebsiteCheckboxState();
                      useWebsiteInput.on('change', handleWebsiteCheckboxState);
                    }

                    // Disable category tree selector on page load if the checkbox is checked
                    $(document).ready(function() {
                      if ($(useWebsiteInput).is(':checked')) {
                        handleWebsiteCheckboxState();
                      }
                    });

                    //When Use website / or Use default checkbox is selected then disable category tree selector
                      function handleWebsiteCheckboxState() {
                        if (useWebsiteInput.is(':checked')) {
                          $(treeDivSelector).addClass('treeselect--disabled');
                        } else {
                          $(treeDivSelector).removeClass('treeselect--disabled');
                        }
                      }


                    // Apply initial class based on checkbox state
                    handleWebsiteCheckboxState();
                    useWebsiteInput.on('change', handleWebsiteCheckboxState);

                    const treeDomElement = document.querySelector(treeDivSelector);
                    const treeElement = new Treeselect({
                      parentHtmlContainer: treeDomElement,
                      expandSelected: true,
                      isIndependentNodes: true,
                      value: [" . $selectedValues . "],
                      options: " . json_encode($categoryTree) . "
                    });

                    treeElement.srcElement.addEventListener('input', (e) => {

                        // Unselect all options
                        $(magentoMultiSelectSelector).find('option:selected').removeAttr('selected');
                        $(magentoMultiSelectSelector).val([]).change();

                        // Select new options
                        $(magentoMultiSelectSelector).val(e.detail).change();
                    });
                })
            </script>";

        return parent::_getElementHtml($element) . $elementCode;
    }

    /**
     * @param array $categoryArray
     * @param int $parentId
     * @return array
     */
    protected function getTreeCategoryArray(array $categoryArray, int $parentId = 0): array
    {
        $result = array();

        foreach ($categoryArray as $item) {
            if ($item[Category::INDEX_PARENT_ID] == $parentId) {
                $children = $this->getTreeCategoryArray($categoryArray, $item[Category::INDEX_VALUE]);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $item['name'] = $item[Category::INDEX_LABEL]; // Rename the 'label' key to 'name'
                unset($item[Category::INDEX_LABEL]); // Remove the 'label' key
                $result[] = $item;
            }
        }

        return $result;
    }
}
