<?php

namespace Option\Hook\Back;

use Option\Service\BackOffice\OptionCategoryService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Assets\AssetResolverInterface;
use TheliaSmarty\Template\SmartyParser;

class ConfigurationHook extends BaseHook
{
    private $optionCategoryService;

    public function __construct(
        OptionCategoryService $optionCategoryService,
        SmartyParser $parser = null,
        AssetResolverInterface $resolver = null,
        EventDispatcherInterface $eventDispatcher = null
    )
    {
        parent::__construct($parser, $resolver, $eventDispatcher);
        $this->optionCategoryService = $optionCategoryService;
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $optionCategory = $this->optionCategoryService->getOptionCategory();

        $event->add(
            $this->render('module-configuration.html', [
                'category_id' => $optionCategory->getId()
            ])
        );
    }

    public function onModuleConfigurationJs(HookRenderEvent $event): void
    {
        $event->add(
            $this->render('module-configuration.js.html')
        );
    }

    public function onMainTopMenuTools(HookRenderEvent $event)
    {
        $event->add($this->render("hook/menu-hook.html", $event->getArguments()));
    }
}