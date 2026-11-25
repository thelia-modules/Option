<?php

namespace Option\Form;

use Option\Form\Base\BaseOptionFrontForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OptionFrontForm extends BaseOptionFrontForm
{
    // Used for every option that has no form named after its reference.
    //
    // Besides the option id, it takes what a front gets from GET /open_api/option/get/{pseId}
    // and sends back (code, title, price), and a free text typed by the customer. None is
    // required: an option without customization still goes through with its id alone.
    // An option that needs other fields, or rules on the text, declares its own form.

    protected function buildForm()
    {
        parent::buildForm();

        foreach (['code', 'title', 'price', 'text'] as $field) {
            $this->formBuilder->add($field, TextType::class, ['required' => false]);
        }

        // A field the front did not send is left out of the data, so an option without
        // customization is stored with its id alone, as it was before these fields existed.
        $this->formBuilder->addEventListener(
            FormEvents::SUBMIT,
            static function (FormEvent $event): void {
                $event->setData(
                    array_filter((array) $event->getData(), static fn ($value) => null !== $value)
                );
            }
        );
    }

    public static function getName():string
    {
        return 'option';
    }
}
