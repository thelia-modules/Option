<?php

namespace Option\Form;

use Option\Model\Map\OptionProductTableMap;
use Option\Option;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;
use Thelia\Model\Lang;
use Thelia\Model\ProductQuery;

class ProductAvailableOptionForm extends BaseForm
{
    /**
     * @throws PropelException
     */
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                'product_id',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'option_id',
                ChoiceType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                    'choices' => $this->getOptionChoices(),
                    'label' => $this->translator->trans('Options', [], Option::DOMAIN_NAME)
                ]
            )
            ->add(
                'option_price',
                MoneyType::class,
                [
                    'label' => $this->translator->trans('Price', [], Option::DOMAIN_NAME)
                ]
            )
            ->add(
                'option_promo_price',
                MoneyType::class,
                [
                    'label' => $this->translator->trans('Promo price', [], Option::DOMAIN_NAME)
                ]
            );
    }

    public static function getName(): string
    {
        return "product_available_option_form";
    }

    /**
     * @throws PropelException
     */
    protected function getOptionChoices(): array
    {
        $data = [];
        $options = ProductQuery::create()->useOptionProductQuery()
            ->withColumn(OptionProductTableMap::COL_ID, 'option_id')
            ->endUse()
            ->find();

        foreach ($options as $option) {
            $option->setLocale(Lang::getDefaultLanguage()->getLocale());
            $data[$option->getTitle() . " - " . $option->getRef()] = $option->getVirtualColumn('option_id');
        }

        return $data;
    }
}