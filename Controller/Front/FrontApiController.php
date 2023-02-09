<?php

namespace Option\Controller\Front;

use OpenApi\Service\OpenApiService;
use OpenApi\Annotations as OA;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use OpenApi\Model\Api\ModelFactory;
use OpenApi\OpenApi;
use Option\Event\OptionFormValidationEvent;
use Option\Model\OptionProduct;
use Option\Service\Front\OptionCartItemService;
use Option\Model\OptionProductQuery;
use Option\Service\Front\OptionService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\CartItemQuery;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Model\ProductQuery;
use Thelia\TaxEngine\TaxEngine;

class FrontApiController extends BaseFrontOpenApiController
{
    /**
     * @Route("/open_api/option/customizations/{cartItemId}/{optionId}", name="option_customizations", methods="POST")
     *
     * @OA\Post(
     *     path="/open_api/option/customizations/{cartItemId}/{optionId}",
     *     tags={"Option"},
     *     summary="Set customization for a cart item",
     *     @OA\Parameter(
     *          name="cartItemId",
     *          description="The id of cart item",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="optionId",
     *          description="The id of selected option",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="customizations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="type",
     *                             type="string"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     )
     * )
     *
     * @param $optionCartItemService OptionCartItemService
     * @param $cartItemId integer
     * @throws \OpenApi\Exception\OpenApiException
     */
    public function cartItemCustomizations(
        OptionCartItemService    $optionCartItemService,
        OpenApiService           $openApiService,
        ModelFactory             $modelFactory,
        Request                  $request,
        EventDispatcherInterface $eventDispatcher,
        int                      $cartItemId,
        int                      $optionId
    )
    {
        try {
            if (null === $cartItem = CartItemQuery::create()->findPk($cartItemId)) {
                throw new \Exception(Translator::getInstance()->trans("Error, missing cart item parameter"));
            }

            if (!$optionsFormData[] = $request->get('options')) {
                throw new \Exception(Translator::getInstance()->trans("Error, missing options form data"));
            }

            foreach ($optionsFormData as $optionFormData) {
                $eventFormValidation = new OptionFormValidationEvent();
                $eventFormValidation->setOptionsFormData($optionFormData);

                $eventDispatcher->dispatch($eventFormValidation, OptionFormValidationEvent::OPTION_FORM_IS_VALID);
                $data = $eventFormValidation->getOptionsFormData();

                $optionProduct = OptionProductQuery::create()->findPk($data['optionId']);

                if (!$optionProduct) {
                    continue;
                }

                $optionCartItemService->persistCartItemCustomizationData(
                    $cartItem,
                    $optionProduct,
                    $data
                );

                $optionCartItemService->handleCartItemOptionPrice($cartItem);
            }

            return OpenApiService::jsonResponse([
                'cart' => $openApiService->getCurrentOpenApiCart(),
                'cartItem' => $modelFactory->buildModel('CartItem', $request->getSession()->getSessionCart()),
            ]);
        } catch (\Exception $e) {
            throw $openApiService->buildOpenApiException(
                Translator::getInstance()->trans('Invalid data', [], OpenApi::DOMAIN_NAME),
                Translator::getInstance()->trans($e->getMessage(), [], OpenApi::DOMAIN_NAME)
            );
        }
    }

    /**
     * @Route("/open_api/option/customizations/get/{productId}", name="get_option_customizations", methods="GET")
     *
     * @param $productId
     * @param TaxEngine $taxEngine
     * @param $productId
     * @return JsonResponse
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getOptionsAvailable(TaxEngine $taxEngine, Request $request, OptionService $optionService, $productId)
    {
        $product = ProductQuery::create()->findPk($productId);
        if (!$product) {
            return new JsonResponse([]);
        }
        $data = [];

        $options = $optionService->getProductAvailableOptions($product);

        /** @var OptionProduct $option */
        foreach ($options as $option) {
            $data[] = [
                'option_id' => $option->getId(),
                'option_code' => $option->getProduct()?->getRef(),
                'option_price' => $optionService->getOptionTaxedPrice($option->getProduct())
            ];
        }

        return new JsonResponse($data);
    }
}