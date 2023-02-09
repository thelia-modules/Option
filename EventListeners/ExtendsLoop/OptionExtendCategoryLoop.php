<?php

namespace Option\EventListeners\ExtendsLoop;

use Option\Service\BackOffice\OptionCategoryService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\ProductQuery;

class OptionExtendCategoryLoop implements EventSubscriberInterface
{
    private $optionCategoryService;

    public function __construct(OptionCategoryService $optionCategoryService)
    {
        $this->optionCategoryService = $optionCategoryService;
    }
    
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'category') => ['optionCategoryBuildModelCriteria', 128],
        ];
    }

    /**
     * @param LoopExtendsBuildModelCriteriaEvent $event
     */
    public function optionCategoryBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event)
    {
        $optionCategoryId = $this->optionCategoryService->getOptionCategory()->getId();
        /** @var ProductQuery $query */
        $event->getModelCriteria()
            ->filterById($optionCategoryId, Criteria::NOT_IN);
    }
}