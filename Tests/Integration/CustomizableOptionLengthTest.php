<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\EventListeners\CartAddFormListener;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Form\Definition\FrontForm;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * The text typed into a free-text option is capped at 50 characters.
 */
final class CustomizableOptionLengthTest extends ActionIntegrationTestCase
{
    public function testFiftyCharactersAreAccepted(): void
    {
        self::assertCount(0, $this->errorsOnTheOptionFor(str_repeat('a', 50)));
    }

    public function testFiftyOneCharactersAreRefused(): void
    {
        self::assertCount(1, $this->errorsOnTheOptionFor(str_repeat('a', 51)));
    }

    private function errorsOnTheOptionFor(string $text): iterable
    {
        $currency = $this->factory->currency();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $this->factory->taxRule(), $currency, ['baseQuantity' => 10, 'basePrice' => 10.0]);
        $optionSource = $this->factory->product($category, $this->factory->taxRule(), $currency, ['baseQuantity' => 10, 'basePrice' => 5.0]);

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(true);
        $option->save();
        (new ProductAvailableOption())->setProductId($product->getId())->setOptionId($option->getId())->save();

        $form = $this->getService(TheliaFormFactory::class)
            ->createForm(FrontForm::CART_ADD, FormType::class, ['product' => $product->getId()], ['csrf_protection' => false])
            ->getForm();

        $form->submit([
            'product' => $product->getId(),
            'product_sale_elements_id' => ProductSaleElementsQuery::create()
                ->filterByProductId($product->getId())->filterByIsDefault(true)->findOne()->getId(),
            'quantity' => 1,
            CartAddFormListener::FIELDSET_NAME => [(string) $option->getId() => $text],
        ], false);

        return $form->get(CartAddFormListener::FIELDSET_NAME)->get((string) $option->getId())->getErrors();
    }
}
