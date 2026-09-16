<?php

declare(strict_types=1);

namespace Option\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * Option (product) creation form. Replaces the core Thelia\Form\ProductCreationForm
 * removed in Thelia 3 — the create flow still relies on TheliaEvents::PRODUCT_CREATE.
 */
class OptionCreationForm extends BaseForm
{
    public static function getName(): string
    {
        return 'option_creation_form';
    }

    protected function buildForm(): void
    {
        $this->formBuilder
            ->add('ref', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('title', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('locale', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('default_category', IntegerType::class, ['constraints' => [new NotBlank()]])
            ->add('tax_rule', IntegerType::class, ['constraints' => [new NotBlank()]])
            ->add('price', NumberType::class, ['constraints' => [new NotBlank(), new GreaterThanOrEqual(['value' => 0])]])
            ->add('currency', IntegerType::class, ['constraints' => [new NotBlank()]])
            ->add('weight', NumberType::class, ['required' => false])
            ->add('quantity', IntegerType::class, ['required' => false])
            ->add('visible', IntegerType::class, ['required' => false])
            ->add('virtual', IntegerType::class, ['required' => false])
            ->add('template_id', IntegerType::class, ['required' => false])
            ->add('is_customizable', CheckboxType::class, ['required' => false]);
    }
}
