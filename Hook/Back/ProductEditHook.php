<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Option\Hook\Back;

use Option\Option;
use Option\Service\Back\OptionTabContextService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Tools\URL;

class ProductEditHook extends BaseHook
{
    public function __construct(
        protected OptionTabContextService $tabContext,
        ?EventDispatcherInterface $dispatcher,
        ?ParserResolver $parserResolver,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'product.tab' => [
                [
                    'type' => 'back',
                    'method' => 'onProductTab',
                ],
            ],
        ];
    }

    public function onProductTab(HookRenderBlockEvent $event): void
    {
        $productId = (int) $event->getArgument('id');

        $event->add(
            [
                'id' => Option::PRODUCT_OPTION_TAB_ID,
                'title' => $this->trans('Options', [], Option::DOMAIN_NAME),
                'content' => $this->render('product/product-option-tab.html.twig', [
                    'product_id' => $productId,
                    'attached_options' => $this->tabContext->attachedToProduct($productId),
                    'redirect_url' => URL::getInstance()->absoluteUrl('/admin/products/update', [
                        'product_id' => $productId,
                        'current_tab' => Option::PRODUCT_OPTION_TAB_ID,
                    ]),
                ]),
            ]
        );
    }
}
