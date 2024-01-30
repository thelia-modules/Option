<?php

namespace Option\Smarty\Plugins;

use Option\Model\OptionCartItemQuery;
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

        $orderProductCustomization = OptionCartItemQuery::create()
            ->filterByOrderProductId($orderProductId)
            ->findOne();

        if (null === $orderProductCustomization) {
            $smarty->assign('customizationData', []);
            return;
        }

        $smarty->assign('customizationData', json_decode($orderProductCustomization->getCustomisationData(), true));
    }

    public function getPluginDescriptors(): array
    {
        return array(
            new SmartyPluginDescriptor("function", "getCustomizationData", $this, "getCustomizationData")
        );
    }
}
