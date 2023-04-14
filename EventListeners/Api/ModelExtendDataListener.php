<?php

namespace Option\EventListeners\Api;

use OpenApi\Annotations as OA;
use OpenApi\Events\ModelExtendDataEvent;
use OpenApi\Model\Api\CartItem;
use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModelExtendDataListener implements EventSubscriberInterface
{
    /**
     * @OA\Schema(
     *    schema="OptionExtendCartItem",
     *    @OA\Property(
     *      property="customizations",
     *      type="array"
     *    )
     * )
     * @param ModelExtendDataEvent $event
     */
    public function addDataOnCartItem(ModelExtendDataEvent $event)
    {
        /** @var CartItem $cartItem */
        $cartItem = $event->getModel();

        $customizations = OptionCartItemCustomizationQuery::create()
            ->filterByCartItemOptionId($cartItem->getId())
            ->find();

        $event->setExtendDataKeyValue(
            'customizations',
            array_map(
                static function (OptionCartItemCustomization $customization) {
                    return json_decode($customization->getCustomisationData(), true);
                }, iterator_to_array($customizations)
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModelExtendDataEvent::ADD_EXTEND_DATA_PREFIX . 'cart_item' => ['addDataOnCartItem', 300],
        ];
    }
}