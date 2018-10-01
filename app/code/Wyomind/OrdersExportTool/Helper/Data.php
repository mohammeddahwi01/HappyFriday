<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 *  common helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_modelProduct = null;
    protected $_messageManager = null;
    protected $_profile = null;
    protected $_coreHelper = null;
    protected $_envConfig = [];
    protected $_configReader = null;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Store\Model\StoreManager           $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product              $modelProduct
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Catalog\Model\Product $modelProduct,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Framework\App\DeploymentConfig\Reader $configReader,
            \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->_modelProduct = $modelProduct;
        $this->_messageManager = $messageManager;
        $this->_coreHelper = $coreHelper;
        $this->_configReader = $configReader;
        parent::__construct($context);
    }

    public function execPhp(
    $originalCall,
            $script,
            $order = null,
            $data = [],
            $item = null
    )
    {
        try {
            return eval($script);
        } catch (\Throwable $e) {
            if ($item != null) {
                $classes = explode("\\",get_class($item));
                $object = array_pop($classes);
                if ($object == "Interceptor") {
                    $object = array_pop($classes);
                }
                $exc = new \Exception("\nError on line:\n" . $originalCall . "\n\nExecuted script:\n" . $script . "\n\nError message:\n" . $e->getMessage() . "\n\nObject:\n&nbsp;&nbsp;- Type: " . $object . "\n&nbsp;&nbsp;- ID: " . $item->getId());
                throw $exc;
            }
            throw new \Exception($script . "<br/>" . $e->getMessage());
        }
    }

    public function setProfile($model)
    {
        $this->_profile = $model;
    }

    public function load($types)
    {
        foreach ($types as $type) {
            $this->_profile->requireData($type);
        }
    }

    public function executePhpScripts(
    $preview,
            $output,
            $order = null,
            $data = [],
            $item = null,
            $type = 1
    )
    {

        $matches = [];
        preg_match_all("/(?<script><\?php(?<php>.*)\?>)/sU", $output, $matches);

        $i = 0;
        foreach (array_values($matches["php"]) as $phpCode) {
            $val = null;

            if ($type != 1) {
                $phpCode = stripslashes($phpCode);
            }


            $displayErrors = ini_get("display_errors");
            ini_set("display_errors", 0);

            if (($val = $this->execPhp($phpCode, $phpCode, $order, $data, $item)) === false) {
                if ($preview) {
                    ini_set("display_errors", $displayErrors);
                    throw new \Exception("Syntax error in " . $phpCode . " : " . error_get_last()["message"]);
                } else {
                    ini_set("display_errors", $displayErrors);
                    $this->messageManager->addError("Syntax error in <i>" . $phpCode . "</i><br>." . error_get_last()["message"]);
                    throw new \Exception();
                }
            }
            ini_set("display_errors", $displayErrors);

            if (is_array($val)) {
                $val = implode(",", $val);
            }
            $output = str_replace($matches["script"][$i], $val, $output);
            $i++;
        }

        return $output;
    }

    /**
     * Get all db instances
     * @return array
     */
    public function getEntities()
    {
        return [
            "order" => [
                "code" => "order",
                "label" => "Order",
                "syntax" => "order",
                "table" => "sales_order",
                "filterable" => true,
                "connection" => "sales"
            ],
            "order_item" => [
                "code" => "order_item",
                "label" => "Product",
                "syntax" => "product",
                "table" => "sales_order_item",
                "filterable" => true,
                "connection" => "sales"
            ],
            "order_shipping_address" => [
                "code" => "order_shipping_address",
                "label" => "Shipping address",
                "syntax" => "shipping",
                "table" => "sales_order_address",
                "filterable" => false,
                "connection" => "sales"
            ],
            "order_billing_address" => [
                "code" => "order_billing_address",
                "label" => "Billing address",
                "syntax" => "billing",
                "table" => "sales_order_address",
                "filterable" => false,
                "connection" => "sales"
            ],
            "order_payment" => [
                "code" => "order_payment",
                "label" => "Payment",
                "syntax" => "payment",
                "table" => "sales_order_payment",
                "filterable" => false,
                "connection" => "sales"
            ],
            "invoice" => [
                "code" => "invoice",
                "label" => "Invoice",
                "syntax" => "invoice",
                "table" => "sales_invoice",
                "filterable" => true,
                "connection" => "sales"
            ],
            "shipment" => [
                "code" => "shipment",
                "label" => "Shipment",
                "syntax" => "shipment",
                "table" => "sales_shipment",
                "filterable" => false,
                "connection" => "sales"
            ],
            "creditmemo" => [
                "code" => "creditmemo",
                "label" => "Creditmemo",
                "syntax" => "creditmemo",
                "table" => "sales_creditmemo",
                "filterable" => false,
                "connection" => "sales"
            ]
        ];
    }

    /**
     * Order the item of an array
     * @param type $a
     * @param type $b
     * @return boolean
     */
    public static function cmp(
    $a,
            $b
    )
    {
        return ($a['field'] < $b['field']) ? (-1) : 1;
    }

    /**
     * return the porfile from which the order item must be exported
     * @param type $_item
     * @return string
     */
    public function getExportTo($_item)
    {
        if ($_item->getExportTo()) {
            return $_item->getExportTo();
        } else {
            return $this->_modelProduct->load($_item->getProductId())->getData('export_to');
        }
    }

    public function stripTagsContent(
    $text,
            $tags = '',
            $invert = false
    )
    {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if (is_array($tags) and count($tags) > 0) {
            if ($invert == false) {
                return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            } else {
                return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
            }
        } elseif ($invert == false) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return strip_tags($text);
    }
    
    public function getConnectionConfig()
    {
        if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
            if (empty($this->_envConfig)) {
                $this->_envConfig = $this->_configReader->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
            }
            return $this->_envConfig['db']['connection'];
        } else {
            return [];
        }
    }

    public function getConnection($db)
    {
        if (isset($this->getConnectionConfig()[$db])) {
            if ($this->getConnectionConfig()[$db]['active'] == 1) {
                return $db;
            } else {
                return "default";
            }
        } else {
            return "default";
        }
    }

}
