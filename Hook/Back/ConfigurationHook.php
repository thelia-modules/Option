<?php

declare(strict_types=1);

namespace Option\Hook\Back;

use Exception;
use Option\Service\OptionConfigurationPresenter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;

class ConfigurationHook extends BaseHook
{
    public function __construct(
        protected OptionConfigurationPresenter $configurationPresenter,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigurationJs'],
            ],
            'main.in-top-menu-items' => [
                ['type' => 'back', 'method' => 'onMainTopMenuTools'],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $event->add(
            $this->render('Option/option-configuration.html.twig', $this->configurationPresenter->present())
        );
    }

    public function onModuleConfigurationJs(HookRenderEvent $event): void
    {
        $event->add(
            $this->render('Option/option-configuration.js.html.twig')
        );
    }

    public function onMainTopMenuTools(HookRenderEvent $event): void
    {
        $event->add($this->render('Option/hook/menu-hook.html.twig', $event->getArguments()));
    }
}
