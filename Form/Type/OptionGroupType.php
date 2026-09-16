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

namespace Option\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The group of paid options hung onto the add-to-cart form.
 *
 * A plain compound form with a block prefix of its own, which is what buys the module
 * a dedicated widget: 'option_group_widget' in the form theme, instead of the generic
 * rows form_rest() would otherwise emit inside the theme's form.
 *
 * The rows the module already computed travel to the view, so the template can print a
 * price and a description next to each field without querying anything again.
 */
final class OptionGroupType extends AbstractType
{
    public function getParent(): string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'option_group';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => false,
                'required' => false,
                'option_rows' => [],
                'group_label' => null,
            ])
            ->setAllowedTypes('option_rows', 'array')
            ->setAllowedTypes('group_label', ['null', 'string']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Keyed by option id: the template looks a row up from the child's name, which
        // is that same id, rather than relying on both lists being in the same order.
        $rows = [];

        foreach ($options['option_rows'] as $row) {
            if (isset($row['id'])) {
                $rows[(string) $row['id']] = $row;
            }
        }

        $view->vars['option_rows'] = $rows;
        $view->vars['group_label'] = $options['group_label'];
    }
}
