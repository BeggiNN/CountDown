<?php

namespace Perspective\SalesEndTime\Block;

use Magento\Framework\Registry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    protected $_rules;

    /**
     * @var \Magento\Catalog\Block\Product\View\AbstractView
     */
    protected $_view;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Registry $registry,
        Configurable $configurable,
        \Magento\Catalog\Block\Product\View\AbstractView $view,
        \Magento\CatalogRule\Model\Rule $rules,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateHelper,
        array $data = []
    )
    {
        $this->_rules = $rules;
        $this->_dateHelper = $dateHelper;
        $this->_view = $view;
        $this->_productRepository = $productRepository;
        $this->registry = $registry;
        $this->_configurable = $configurable;
        parent::__construct($context, $data);
    }

    /**
     * Get end date special price
     * @return array|string
     */
    public function getDatePrice()
    {
        $text = "Special Price";
        $product = $this->_view->getProduct();
        if (isset($product)) {
            $type = $product->getTypeId();
            if ($type == 'configurable') {
                $id = $product->getId();
                $products = $this->_configurable->getChildrenIds($id);
                foreach ($products[0] as $value) {
                    $item = $this->_productRepository->getById($value);
                    $specialPrice = $item->getSpecialPrice();
                    if (isset($specialPrice)) {
                        $endTimeSale = $item->getSpecialToDate();
                        $startTimeSale = $item->getSpecialFromDate();
                        $nowDate = date('Y-m-d h:m:s');
                        if ($startTimeSale <= $nowDate && $nowDate <= $endTimeSale) {
                            return [$endTimeSale, $text];
                        } else
                            return '';
                    }
                }
                return '';
            } else {
                $price = $product->getSpecialPrice();
                if (isset($price)) {
                    $endTimeSale = $product->getSpecialToDate();
                    $startTimeSale = $product->getSpecialFromDate();
                    $nowDate = date('Y-m-d h:m:s');
                    if ($startTimeSale <= $nowDate && $nowDate <= $endTimeSale) {
                        return [$endTimeSale, $text];
                    }
                }
            }
            return '';
        }
        return '';
    }

    /**
     * Get currect product rules and end date rule.
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrectProductRules()
    {
        $text = "Rule Price";
        $productId = $this->_view->getProduct()->getId();
        $rules = $this->_rules->getResourceCollection()->addFieldToFilter('is_active', 1);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (isset($rule->getMatchingProductIds()[$productId][1])) {
                    $productsRules[] = $rule;
                }
            }
        }
        if (isset($productsRules)) {
            reset($productsRules);
            for ($i = 0; $i < count($productsRules) - 1; ++$i) {
                $current = current($productsRules);
                $next = next($productsRules);
                if ($current->getToDate() >= $next->getToDate()) {
                    $dateTo = $next->getToDate();
                } else {
                    $dateTo = $current->getToDate();
                }
                return [$dateTo, $text];
            }
        }
        if (isset($productsRules)) {
            foreach ($productsRules as $rulesa) {
                $dateTo = $rulesa->getToDate();
            }
            return [$dateTo, $text];
        }
        return '';
    }

    /**
     * Get All catalog rules
     *
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCatalogRulePrices()
    {
        $productId = $this->_view->getProduct()->getId();
        $rules = $this->_rules->getResourceCollection()->addFieldToFilter('is_active', 1);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (isset($rule->getMatchingProductIds()[$productId][1])) {
                    $productsRules[] = $rule;
                }
            }
            if (isset($productsRules)) {
                foreach ($productsRules as $rules) {
                    $name = $rules->getName();
                    $date = $rules->getToDate();
                    $arr[] = ['name' => $name, 'date' => $date];
                }
                return $arr;
            }
        }
        return '';
    }
}
