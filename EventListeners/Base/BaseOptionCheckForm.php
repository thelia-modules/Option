<?php

namespace Option\EventListeners\Base;

use Option\Event\OptionFormValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Form\TheliaFormValidator;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;

abstract class BaseOptionCheckForm extends BaseAction implements EventSubscriberInterface
{
    protected $formFactory;
    protected $formValidator;
    protected ParserContext $parserContext;

    /**
     * @param TheliaFormFactory $formFactory
     * @param TheliaFormValidator $formValidator
     * @param ParserContext $parserContext
     */
    public function __construct(TheliaFormFactory $formFactory, TheliaFormValidator $formValidator, ParserContext $parserContext)
    {
        $this->formFactory = $formFactory;
        $this->formValidator = $formValidator;
        $this->parserContext = $parserContext;
    }

    /**
     * @param OptionFormValidationEvent $event
     * @throws \Exception
     */
    public function checkData(OptionFormValidationEvent $event)
    {
        try {
            /* @todo  need to be extends and form specified
             *   $form = $this->formFactory->createForm(Form::class, [], ['csrf_protection' => false]);
             *   $form->getForm()->submit($event->getOptionsFormData());             *
             *   $this->formValidator->validateForm($form);
             *   $this->parserContext->clearForm($form);
             */
        } catch (FormValidationException $ex) {
            throw new \Exception("Form validation error: " . $ex->getMessage());
        }

    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [OptionFormValidationEvent::OPTION_FORM_IS_VALID => ['checkData', 128]];
    }
}