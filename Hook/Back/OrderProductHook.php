<?php

namespace Option\Hook\Back;

use Option\Model\OptionCartItemCustomizationQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class OrderProductHook extends BaseHook
{
    public function onOrderEditProductList(HookRenderEvent $event)
    {
        $orderProductId = $event->getArgument('order_product_id');

        if (null === $orderProductId) {
            return;
        }

        $orderProductCustomizations = OptionCartItemCustomizationQuery::create()
            ->filterByOrderProductId($orderProductId)
            ->find();

        if (null === $orderProductCustomizations) {
            return;
        }

        $event->add(
            $this->render('order-product/order_product_additional_data.html', [
                "orderProductCustomizations" => $orderProductCustomizations->getData()
            ])
        );
    }
}