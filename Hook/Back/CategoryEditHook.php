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
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Tools\URL;

class CategoryEditHook extends BaseHook
{
    public function __construct(
        protected OptionTabContextService $tabContext,
        protected TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'category.tab' => [
                [
                    'type' => 'back',
                    'method' => 'onCategoryTab',
                ],
            ],
        ];
    }

    public function onCategoryTab(HookRenderBlockEvent $event): void
    {
        $categoryId = (int) $event->getArgument('id');

        $event->add(
            [
                'id' => Option::CATEGORY_OPTION_TAB_ID,
                'title' => $this->trans('Options', [], Option::DOMAIN_NAME),
                'content' => $this->render('category/category-option-tab.html.twig', [
                    'category_id' => $categoryId,
                    'attached_options' => $this->tabContext->attachedToCategory($categoryId),
                    'redirect_url' => URL::getInstance()->absoluteUrl('/admin/categories/update', [
                        'category_id' => $categoryId,
                        'current_tab' => Option::CATEGORY_OPTION_TAB_ID,
                    ]),
                ]),
            ]
        );
    }
}
