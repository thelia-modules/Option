<?php

declare(strict_types=1);

namespace Option\Hook\Back;

use Option\Option;
use Option\Service\Front\AttachedOptionsService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;

/**
 * Under an order line of the back office, the options the customer bought with it: each
 * one by its name and with what the customer typed, read from what the order froze, the
 * same list the customer sees in their account.
 */
class OrderProductHook extends BaseHook
{
    public function __construct(
        protected AttachedOptionsService $attachedOptions,
        ?EventDispatcherInterface $dispatcher,
        ?ParserResolver $parserResolver,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'order-edit.product-list' => [
                [
                    'type' => 'back',
                    'method' => 'onOrderEditProductList',
                ],
            ],
        ];
    }

    public function onOrderEditProductList(HookRenderEvent $event): void
    {
        $options = $this->attachedOptions->forHostOrderProduct((int) $event->getArgument('order_product_id'));

        if ([] === $options) {
            return;
        }

        $event->add(
            $this->render('Option/order-product/order_product_additional_data.html.twig', [
                'options' => $options,
                'options_ordered' => $this->trans('Options ordered with this product:', [], Option::DOMAIN_NAME),
            ])
        );
    }
}
