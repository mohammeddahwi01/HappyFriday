<?php

namespace Doofinder\Feed\Block\Adminhtml\System\Config\Panel;

/**
 * Dynamic feed url
 */
class DynamicFeedUrl extends Message
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\Url $frontendUrlBuilder
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\Url $frontendUrlBuilder,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get element text
     *
     * @return string
     */
    public function getText()
    {
        $storeCodes = $this->storeConfig->getStoreCodes();

        $html = '';
        foreach ($storeCodes as $storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
            $config = $this->storeConfig->getStoreConfig($storeCode);

            $password = $config['password'];
            $params = ['language' => $store->getCode()];

            if ($password) {
                $params['password'] = $password;
            }

            $this->frontendUrlBuilder->setScope($store->getId());
            $url = $this->frontendUrlBuilder->getUrl('doofinder/feed', [
                '_nosid' => true
            ] + $params);
            $anchor = '<a href="' . $url . '">' . $url . '</a>';

            $html .= '<p><strong>' . $store->getName() . ':</strong></p><p>' . $anchor . '</p>';
        }

        $html .= '<p>';
        $html .= __(
            'If cron feed doesn\'t work for you, use these URLs ' .
            'to dynamically index your content from Doofinder. ' .
            'Contact support if you need help.'
        );
        $html .= '</p>';

        return $html;
    }
}
