<?php
/**
 * Sistel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the sistel.com license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Paymen Methods - Redsys
 * @package     Sistel_Redsys
 * @author      Juan Pedro Barba Soler || Developer at Sistel, Servicios Informáticos de Software y Telecomunicaciones
 * @copyright   Copyright (c) 2016 Sistel (http://www.sistel.es/)
 * @license     See root folder of this extension
**/

namespace Sistel\Redsys\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RedsysConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        'redsys'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaymentHelper $paymentHelper,
        Repository $assetRepo,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->request = $request;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'redsys' => []
            ]
        ];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['redsys']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
                $config['payment']['redsys']['instructions'] = $this->scopeConfig->getValue('payment/redsys/instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $config['payment']['redsys']['icons'] = $this->createAsset('Sistel_Redsys::images/icon_cards.png')->getUrl();
            }
        }
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }

    /**
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     * @return \Magento\Framework\View\Asset\File
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        return $this->assetRepo->createAsset($fileId, $params);
    }
}
