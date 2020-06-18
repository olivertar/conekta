<?php
namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Catalog\Model\Product;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class LineItemsBuilder implements BuilderInterface
{
    private $_product;

    private $_conektaLogger;

    public function __construct(
        Product $product,
        ConektaLogger $conektaLogger
    ) {
        $this->_conektaLogger = $conektaLogger;
        $this->_conektaLogger->info('Request LineItemsBuilder :: __construct');
        $this->_product = $product;
    }

    public function build(array $buildSubject)
    {
        $this->_conektaLogger->info('Request LineItemsBuilder :: build');

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $request = [];

        $items = $order->getItems();
        foreach ($items as $itemId => $item) {
            if ($item->getProductType() == 'simple' && $item->getPrice() <= 0) {
                break;
            }
            $request['line_items'][] = [
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'unit_price' => (int)($item->getPrice() * 100),
                'description' => strip_tags($this->_product->load($item->getProductId())->getDescription()),
                'quantity' => (int)($item->getQtyOrdered()),
                'tags' => [
                    $item->getProductType()
                ]
            ];
        }

        $this->_conektaLogger->info('Request LineItemsBuilder :: build : return request', $request);

        return $request;
    }
}
