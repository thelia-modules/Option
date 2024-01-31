<?php

namespace Option\Service;

use Exception;
use Option\Event\OptionInputValidationEvent;
use Option\Form\OptionFrontForm;
use Option\Model\OptionProductQuery;
use Option\Service\Front\OptionCartItemService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Form\TheliaFormValidator;
use Thelia\Model\CartItem;
use Thelia\Model\Product;

class CartItemCustomizationOptionHandler
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected OptionCartItemService    $optionCartItemService,
        protected RequestStack             $requestStack,
        protected TheliaFormFactory        $theliaFormFactory,
        protected TheliaFormValidator      $theliaFormValidator
    )
    {
    }

    public function updateCustomizationOptionOnCartItem(CartItem $cartItem): void
    {
        if (!$requestContent = json_decode($this->requestStack->getCurrentRequest()->getContent(), true)) {
            return;
        }

        if (empty($requestContent['options'])) {
            return;
        }

        $options = $requestContent['options'];

        /** @var Product[] $optionsProduct */
        $optionsProduct = $this->optionCartItemService->getOptionsByCartItem($cartItem);

        $optionsProduct = array_filter($optionsProduct,
            function ($optionsProduct) use ($options) {
                if (in_array($optionsProduct->getRef(), array_keys($options))) {
                    return true;
                }
                return false;
            }
        );

        foreach ($optionsProduct as $index => $optionProduct) {
            $formName = $optionProduct->getRef();
            $formData = $options[$optionProduct->getRef()];

            try {
                $form = $this->theliaFormFactory->createForm(
                    $formName,
                    FormType::class,
                    $formData,
                    ['csrf_protection' => false]
                );
            } catch (Exception) {
                $form = $this->theliaFormFactory->createForm(
                    OptionFrontForm::class,
                    FormType::class,
                    $formData,
                    ['csrf_protection' => false]
                );
            }

            $form->getForm()->submit($formData);
            $this->theliaFormValidator->validateForm($form);

            $optionId = $form->getForm()->get('id')->getData();
            $formData = $form->getForm()->getData();

            $optionProductModel = OptionProductQuery::create()
                ->filterById($optionId)
                ->useProductAvailableOptionQuery()
                ->filterByProductId($cartItem->getProductId())
                ->endUse()
                ->useProductQuery()
                ->filterByRef($optionProduct->getRef())
                ->endUse()
                ->findOne();

            if (!$optionProductModel) {
                unset($optionsProduct[$index]);
                return;
            }

            $extendEvent = (new OptionInputValidationEvent())
                ->setOptionId($optionId)
                ->setOptionCustomizationFormData($formData)
                ->setCartItem($cartItem);

            $this->dispatcher->dispatch($extendEvent, OptionInputValidationEvent::CUSTOMIZATION_OPTION_INPUT_EXTEND);

            $this->optionCartItemService->persistCartItemCustomizationData(
                $cartItem,
                $optionProductModel,
                $extendEvent->getOptionCustomizationFormData()
            );
        }

        $this->optionCartItemService->handleCartItemOptionPrice($cartItem, $optionsProduct);
    }
}