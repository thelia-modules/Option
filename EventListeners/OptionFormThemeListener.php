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

namespace Option\EventListeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Makes the module's option_group widget reachable from the front-office theme.
 *
 * The Flexy theme renders its forms with {% form_theme form with flexy_form_themes only %}:
 * `only` discards every theme registered in twig.form_themes, so a global registration
 * would never be seen. What the theme does read is its own Twig global, and appending to
 * it at runtime is what lets the module in without the theme knowing it exists.
 *
 * Done here rather than in the container: two extensions declaring the same Twig global
 * overwrite each other, and which one wins depends on the order the bundles are loaded.
 * Read-then-append cannot lose the theme's own entry.
 *
 * A theme that does not define this global is left alone: the module simply falls back
 * to whatever rendering that theme gives a compound field.
 */
final readonly class OptionFormThemeListener implements EventSubscriberInterface
{
    public const FORM_THEME = '@OptionModule/form/option_group.html.twig';

    private const THEME_GLOBAL = 'flexy_form_themes';

    public function __construct(
        private Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Before anything renders, and on the main request only: a sub-request would
        // append the same entry a second time.
        return [
            KernelEvents::REQUEST => ['registerFormTheme', 0],
        ];
    }

    public function registerFormTheme(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $themes = $this->twig->getGlobals()[self::THEME_GLOBAL] ?? null;

        if (!\is_array($themes) || \in_array(self::FORM_THEME, $themes, true)) {
            return;
        }

        // Appended, not prepended: the theme's own blocks stay the default, and only
        // option_group_widget — which no theme defines — comes from the module.
        $themes[] = self::FORM_THEME;

        $this->twig->addGlobal(self::THEME_GLOBAL, $themes);
    }
}
