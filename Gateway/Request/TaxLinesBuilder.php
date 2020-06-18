<?php
namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Catalog\Model\Product;
use Magento\Tax\Model\ClassModel;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class TaxLinesBuilder implements BuilderInterface
{
    private $_product;
    private $_taxClass;

    private $_conektaLogger;

    public function __construct(
        Product $product,
        ClassModel $taxClass,
        ConektaLogger $conektaLogger
    ) {
        $this->_conektaLogger = $conektaLogger;
        $this->_conektaLogger->info('Request TaxLinesBuilder :: __construct');
        $this->_product = $product;
        $this->_taxClass = $taxClass;
    }

    public function build(array $buildSubject)
    {
        $this->_conektaLogger->info('Request TaxLinesBuilder :: build');

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $request = [];
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() == 'simple' && $item->getPrice() <= 0) {
                break;
            }
            $request['tax_lines'][] = [
                'description' => $this->getTaxName($item),
                'amount' => (int)($item->getTaxAmount() * 100)
            ];
        }

        $this->_conektaLogger->info('Request TaxLinesBuilder :: build : return request', $request);

        return $request;
    }

    public function getTaxName($item)
    {
        $_product = $this->_product->load($item->getProductId());
        $taxClassId = $_product->getTaxClassId();
        $taxClass = $this->_taxClass->load($taxClassId);
        $taxClassName = $taxClass->getClassName();
        if (empty($taxClassName)) {
            $taxClassName = "tax";
        }

        $this->_conektaLogger->info('Request TaxLinesBuilder :: getTaxName : ', [$taxClassName]);

        return $taxClassName;
    }
}
