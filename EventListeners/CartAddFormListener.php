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

use Option\Form\Type\OptionGroupType;
use Option\Option as OptionModule;
use Option\Service\Front\ProductOptionsService;
use Option\Service\Front\SelectedOptionsStore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Form\CartAdd;

/**
 * Hangs the paid options of a product onto the core add-to-cart form.
 *
 * The options ride in the very request that creates the cart line, which is what the
 * Thelia 2 module obtained from its own front controller. Thelia 3 has no front
 * controller: the theme owns the add-to-cart form and submits it from a live component.
 * Extending the form is how the module gets back into that request without the theme
 * knowing it exists, and without a line changed in Thelia\Form\CartAdd.
 *
 * The fieldset is an OptionGroupType, so its markup comes from the module own widget
 * (see OptionFormThemeListener), not from the generic rows form_rest() would emit.
 */
final readonly class CartAddFormListener implements EventSubscriberInterface
{
    /** Name of the fieldset added to the form: thelia_cart_add[options][<optionId>]. */
    public const FIELDSET_NAME = 'options';

    public function __construct(
        private ProductOptionsService $productOptions,
        private SelectedOptionsStore $store,
        // Thelia's own Translator: the only one carrying the module catalogues.
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Suffixed with the form's own name, not with the FrontForm::CART_ADD definition
        // id: BaseForm dispatches on $this::getName(), which is 'thelia_cart_add'.
        return [
            TheliaEvents::FORM_AFTER_BUILD.'.'.CartAdd::getName() => 'addOptionFields',
        ];
    }

    public function addOptionFields(TheliaFormEvent $event): void
    {
        $builder = $event->getForm()->getFormBuilder();

        // The theme builds the form with the product it is showing. A form built without
        // one (an empty form for a listing, say) gets no fieldset rather than a guess.
        $data = $builder->getData();
        $productId = \is_array($data) ? (int) ($data['product'] ?? 0) : 0;

        if ($productId <= 0) {
            return;
        }

        $options = $this->productOptions->forProduct($productId);

        if ([] === $options) {
            return;
        }

        $builder->add(self::FIELDSET_NAME, OptionGroupType::class, [
            'group_label' => $this->translator->trans('Available options', [], OptionModule::DOMAIN_NAME),
            // Handed to the widget so it can print a price and a description beside each
            // field without going back to the database.
            'option_rows' => $options,
        ]);

        $fieldset = $builder->get(self::FIELDSET_NAME);

        foreach ($options as $option) {
            $constraint = $option['customizable'] ? [new Length(['max' => 50])] : [];

            $fieldset->add(
                (string) $option['id'],
                $option['customizable'] ? TextType::class : CheckboxType::class,
                [
                    'required' => false,
                    'label' => $option['customizable'] ? false : ($option['title'] ?: $option['ref']),
                    'constraints' => [],
                ]
            );
        }

        // Read once the whole form is bound, so the values are the submitted ones rather
        // than whatever the request happens to carry. The listener that attaches them to
        // the cart line runs later in this same request.
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $formEvent) use ($productId): void {
                $fieldset = $formEvent->getForm()->get(self::FIELDSET_NAME);

                $this->store->set($productId, $fieldset->getData() ?? []);
            }
        );
    }
}
