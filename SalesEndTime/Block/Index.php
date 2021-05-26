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
     * @var \Magento\Catalog\Block\Product\View\AbstractView
     */
    protected $_view;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Registry $registry,
        Configurable $configurable,
        \Magento\Catalog\Block\Product\View\AbstractView $view,
        array $data = []
    ) {
        $this->_view = $view;
        $this->_productRepository = $productRepository;
        $this->registry = $registry;
        $this->_configurable = $configurable;
        parent::__construct($context, $data);
    }

    /**
     * Get date end special price.
     * @return array|string
     */
    public function getDatePrice()
    {
        $product = $this->_view->getProduct();
        if (isset($product)) {
            $type = $product->getTypeId();
            if ($type == 'configurable'){
                $id = $product->getId();
                $products = $this->_configurable->getChildrenIds($id);
            foreach ($products[0] as $value) {
                $item = $this->_productRepository->getById($value);
                $specialPrice = $item->getSpecialPrice();
                if (isset($specialPrice)) {
                    $endTimeSale = $item->getSpecialToDate();
                    $startTimeSale = $item->getSpecialFromDate();
                    $endConvert = substr(str_replace("-", "/", $endTimeSale), 0, 10);
                    return [$endConvert, $startTimeSale,$endTimeSale];
                }
                }
            return '';
            }else{
                $price = $product->getSpecialPrice();
                if (isset($price)) {
                    $endTimeSale = $product->getSpecialToDate();
                    $startTimeSale = $product->getSpecialFromDate();
                    $endConvert = substr(str_replace("-", "/", $endTimeSale), 0, 10);
                    return [$endConvert, $startTimeSale,$endTimeSale];
                }
            }
        }
        return '';
    }
}
