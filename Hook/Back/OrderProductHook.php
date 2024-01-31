<?php

namespace Option\Hook\Back;

use Option\Model\OptionCartItemOrderProductQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class OrderProductHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            "order-edit.product-list" => [
                [
                    "type" => "back",
                    "method" => "onOrderEditProductList"
                ]
            ]
        ];
    }

    public function onOrderEditProductList(HookRenderEvent $event): void
    {
        $orderProductId = $event->getArgument('order_product_id');

        if (null === $orderProductId) {
            return;
        }

        $orderProductOption = OptionCartItemOrderProductQuery::create()
            ->filterByOrderProductId($orderProductId)
            ->findOne();
        
        if (null === $orderProductOption) {
            return;
        }

        $event->add(
            $this->render('order-product/order_product_additional_data.html', [
                "orderProductCustomization" => json_decode($orderProductOption?->getCustomizationData(), true, 512, JSON_THROW_ON_ERROR)
            ])
        );
    }
}