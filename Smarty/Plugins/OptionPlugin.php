<?php

namespace Option\Smarty\Plugins;

use Option\Model\OptionCartItemOrderProductQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class OptionPlugin extends AbstractSmartyPlugin
{
    public function getCustomizationData($params, $smarty)
    {
        $orderProductId = $params['order_product_id'];

        if (null === $orderProductId) {
            $smarty->assign('customizationData', []);
            return;
        }

        $orderProductCustomization = OptionCartItemOrderProductQuery::create()
            ->filterByOrderProductId($orderProductId)
            ->findOne();

        if (null === $orderProductCustomization) {
            $smarty->assign('customizationData', []);
            return;
        }

        $smarty->assign('customizationData', json_decode($orderProductCustomization->getCustomizationData(), true));
    }

    public function getPluginDescriptors(): array
    {
        return array(
            new SmartyPluginDescriptor("function", "getCustomizationData", $this, "getCustomizationData")
        );
    }
}
