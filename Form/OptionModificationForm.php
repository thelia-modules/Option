<?php

declare(strict_types=1);

namespace Option\Form;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * Option (product) modification form. Replaces the core Thelia\Form\ProductModificationForm
 * removed in Thelia 3 — the update flow still relies on TheliaEvents::PRODUCT_UPDATE.
 */
class OptionModificationForm extends BaseForm
{
    public static function getName(): string
    {
        return 'option_modification_form';
    }

    protected function buildForm(): void
    {
        $this->formBuilder
            ->add('id', IntegerType::class, ['constraints' => [new NotBlank()]])
            ->add('locale', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('ref', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('title', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('chapo', TextType::class, ['required' => false])
            ->add('description', TextType::class, ['required' => false])
            ->add('postscriptum', TextType::class, ['required' => false])
            ->add('visible', IntegerType::class, ['required' => false])
            ->add('virtual', IntegerType::class, ['required' => false])
            ->add('default_category', IntegerType::class, ['required' => false])
            ->add('brand_id', IntegerType::class, ['required' => false])
            ->add('virtual_document_id', IntegerType::class, ['required' => false]);
    }
}
