<?php

namespace Option\Service\BackOffice;

use Option\Option;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Option\Service\optionTrait;

class OptionCategoryService
{
    private $request;

    use optionTrait;

    /**
     * OptionCategoryService constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @return Category|null
     * @throws \Exception
     */
    public function getOptionCategory()
    {
        if ($optionCategoryId = Option::getConfigValue(Option::OPTION_CATEGORY_ID)) {
            return CategoryQuery::create()->filterById($optionCategoryId)->findOne();
        }

        $optionCategory = CategoryQuery::create()
            ->useCategoryI18nQuery()
                ->filterByTitle(Option::OPTION_CATEGORY_TITLE)
                ->filterByLocale($this->getDefaultLocale()->getLocale())
            ->endUse()
            ->findOne();

        if (null !== $optionCategory) {
            return $optionCategory;
        }

        return $this->createOptionCategory(Option::OPTION_CATEGORY_TITLE);
    }

    /**
     * @param $title
     * @param int $parent
     * @return Category
     * @throws \Exception
     */
    protected function createOptionCategory($title, $parent = 0)
    {
        try {
            $optionCategory = new Category();
            $optionCategory
                ->setLocale($this->getDefaultLocale()->getLocale())
                ->setParent($parent)
                ->setVisible(0)
                ->setTitle($title)
                ->save();

            Option::setConfigValue(Option::OPTION_CATEGORY_ID, $optionCategory->getId());
            return $optionCategory;

        } catch (\Exception $ex) {
            throw new \Exception(sprintf("Error during option category creation %s", $ex->getMessage()));
        }
    }
}