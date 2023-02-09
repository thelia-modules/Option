<?php

namespace Option\Hook\Back;

use Option\Option;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class ProductEditHook extends BaseHook
{
    public function onProductTab(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'product_option_tab',
                'title' => $this->trans('Options', [], Option::DOMAIN_NAME),
                'href' => URL::getInstance()->absoluteUrl('/admin/option/product/show/' . $event->getArgument('id')),
                'content' => "Contenu !"
            ]
        );
    }

    public function onProductEditJs(HookRenderEvent $event)
    {

    }
}