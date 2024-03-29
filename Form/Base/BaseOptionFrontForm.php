<?php

namespace Option\Form\Base;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

abstract class BaseOptionFrontForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'id',
                IntegerType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ]
                ]
            );
    }
}