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

namespace Option\Form;

use Option\Model\CategoryAvailableOptionQuery;
use Option\Model\Map\OptionProductTableMap;
use Option\Option;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Form\BaseForm;
use Thelia\Model\Lang;
use Thelia\Model\ProductQuery;

class CategoryAvailableOptionForm extends BaseForm
{
    /**
     * @throws PropelException
     */
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                'category_id',
                HiddenType::class,
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
                    'choices' => $this->getOptionChoices($this->currentCategoryId()),
                    'label' => $this->translator->trans('Options', [], Option::DOMAIN_NAME),
                ]
            );
    }

    public static function getName(): string
    {
        return 'category_available_option_form';
    }

    /**
     * Options the category can still be given: the whole option catalog minus what is
     * already attached to it.
     *
     * A category id of 0 means the caller did not name a category — on the POST that
     * validates the form, for instance. The catalog is then listed whole, so a choice
     * the browser had is never rejected as unknown.
     *
     * @return array<string, int> option label, keyed the way ChoiceType expects
     *
     * @throws PropelException
     */
    protected function getOptionChoices(int $categoryId): array
    {
        $optionProducts = ProductQuery::create()->useOptionProductQuery();
        $optionProducts->withColumn(OptionProductTableMap::COL_ID, 'option_id');

        $attachedOptionIds = $this->attachedOptionIds($categoryId);

        if ([] !== $attachedOptionIds) {
            $optionProducts->filterById($attachedOptionIds, Criteria::NOT_IN);
        }

        $locale = $this->editionLocale();
        $data = [];

        foreach ($optionProducts->endUse()->find() as $option) {
            $option->setLocale($locale);
            $data[$option->getTitle().' - '.$option->getRef()] = (int) $option->getVirtualColumn('option_id');
        }

        return $data;
    }

    /**
     * @return list<int>
     *
     * @throws PropelException
     */
    private function attachedOptionIds(int $categoryId): array
    {
        if ($categoryId <= 0) {
            return [];
        }

        $optionIds = CategoryAvailableOptionQuery::create()
            ->filterByCategoryId($categoryId)
            ->select('OptionId')
            ->find()
            ->getData();

        return array_map(static fn ($optionId): int => (int) $optionId, $optionIds);
    }

    /**
     * The language the back-office is editing in. Reading the default language instead
     * leaves the option titles empty whenever the catalog is not translated into it,
     * and the select then shows nothing but the reference.
     */
    private function editionLocale(): string
    {
        $session = $this->request->getSession();

        if ($session instanceof Session) {
            return $session->getAdminEditionLang()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }

    /**
     * The category being edited, as named by the template that asked for this form
     * through getForm('category_available_option_form', {category_id: ...}).
     */
    private function currentCategoryId(): int
    {
        $data = $this->formBuilder->getData();

        return \is_array($data) ? (int) ($data['category_id'] ?? 0) : 0;
    }
}
