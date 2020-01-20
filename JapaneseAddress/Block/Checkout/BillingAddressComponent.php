<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CommunityEngineering\JapaneseAddress\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Billing address component template can not be configured through layout XML as generated by PHP.
 *
 * This processor found all billing address components and set Japanese address template for them.
 */
class BillingAddressComponent implements LayoutProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     */
    const XML_PATH_STORE_LOCALE = 'general/locale/code';

    /**
     * BillingAddressComponent constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function process($jsLayout)
    {
        $locale = $this->getStoreLocale();
        if($locale != 'ja_JP') {
            return $jsLayout;
        }
        if (!isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step'])) {
            return $jsLayout;
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step'] = $this->walkChildren(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        );

        return $jsLayout;
    }

    /**
     * Walk though components to find and modify billing address component to substitute template.
     *
     * @param array $component
     * @return array
     */
    private function walkChildren(array $component): array
    {
        if (!isset($component['children']) || !is_array($component['children'])) {
            return $component;
        }

        foreach ($component['children'] as $name => $child) {
            if ($component['component'] === 'Magento_CheckoutAddressSearch/js/view/billing-address') {
                $component['config'] = array_merge(
                    isset($component['config']) ? $component['config'] : [],
                    [
                        'detailsTemplate' => 'CommunityEngineering_JapaneseAddress/checkout-address-search/billing-address/jp'
                    ]
                );
                $component['children']['billingAddressList']['children']
                ['selectBillingAddressModal']['children']['searchBillingAddress']['addressTmpl'] =
                    'CommunityEngineering_JapaneseAddress/checkout-address-search/billing-address/address-renderer/jp';
            } else {
                $component['children'][$name] = $this->walkChildren($child);
            }
        }
        return $component;
    }

    private function getStoreLocale()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_STORE_LOCALE, ScopeInterface::SCOPE_STORE);
    }
}
