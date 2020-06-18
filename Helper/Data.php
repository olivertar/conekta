<?php
namespace Conekta\Payments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;

class Data extends AbstractHelper
{
    private $_moduleList;

    protected $_encryptor;

    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->_moduleList = $moduleList;
        $this->_encryptor = $encryptor;
    }

    public function getConfigData($area, $field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'payment/' . $area . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getModuleVersion()
    {
        return $this->_moduleList->getOne($this->_getModuleName())['setup_version'];
    }

    public function getPrivateKey()
    {
        $privateKey = '';
        $sandboxMode = $this->getConfigData('conekta/conekta_global', 'sandbox_mode');
        if ($sandboxMode) {
            $privateKey = $this->_encryptor->decrypt($this->getConfigData(
                'conekta/conekta_global',
                'test_private_api_key'
            ));
        } else {
            $privateKey = $this->_encryptor->decrypt($this->getConfigData(
                'conekta/conekta_global',
                'live_private_api_key'
            ));
        }
        return $privateKey;
    }

    public function checkBalance($order, $total)
    {
        $total = $total * 100;
        $amount = 0;
        foreach ($order['line_items'] as $lineItem) {
            $amount = $amount +
            ($lineItem['unit_price'] * $lineItem['quantity']);
        }
        foreach ($order['shipping_lines'] as $shippingLine) {
            $amount = $amount + $shippingLine['amount'];
        }
        foreach ($order['discount_lines'] as $discountLine) {
            $amount = $amount - $discountLine['amount'];
        }
        foreach ($order['tax_lines'] as $taxLine) {
            $amount = $amount + $taxLine['amount'];
        }
        if ($amount != $total) {
            $adjustment = $total - $amount;
            $order['tax_lines'][0]['amount'] =
            $order['tax_lines'][0]['amount'] + $adjustment;
            if (empty($order['tax_lines'][0]['description'])) {
                $order['tax_lines'][0]['description'] = 'Round Adjustment';
            }
        }
        return $order;
    }

    public function getPublicKey()
    {
        $sandboxMode = $this->getConfigData('conekta/conekta_global', 'sandbox_mode');
        if ($sandboxMode) {
            $publicKey = $this->getConfigData('conekta/conekta_global', 'test_public_api_key');
        } else {
            $publicKey = $this->getConfigData('conekta/conekta_global', 'live_public_api_key');
        }
        return $publicKey;
    }

    public function getApiVersion()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/api_version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function pluginType()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/plugin_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function pluginVersion()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/plugin_version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
