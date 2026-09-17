# Option

Sell paid options alongside a product: gift wrapping, engraving, an extended warranty.

An option is a Thelia product of its own, so it has a price, a tax rule, a promotional
status, an image and a description. What this module adds is the link between an option
and the products that accept it, and the plumbing that carries the customer's choice from
the product page to the invoice.

This version requires Thelia 3.0.

## Installation

```
composer require thelia/option-module
```

## Back office

From the module menu you can create, edit and delete options.

An option is then attached to a product, a category or a template. Attaching it to a
category or a template covers every product underneath, which is usually how you want to
manage a catalogue-wide option.

An option can be flagged customizable. The customer then types a value when adding the
product to their cart, instead of just ticking a box.

## Front office

The options of a product are hung onto the core add-to-cart form, so the theme submits
them in the very request that creates the cart line. There is nothing to add to the theme:
the fields arrive under `thelia_cart_add[options][<optionId>]`, a checkbox for a plain
option and a text field for a customizable one.

An option the product does not accept has no field, so it cannot be submitted.

What the customer picked is displayed through two theme hooks, which the module answers
with the templates in `templates/theme_hook/`:

| Hook | Where |
|---|---|
| `cart.item.bottom` | under each cart line |
| `account-order.item.bottom` | under each line of a past order |

### Pricing

In the cart, the option amount is added to the price of the line it hangs under, because
the cart total is computed from that column alone.

Once the order is placed, each option becomes an order line of its own and its amount is
taken off the host line. The two figures, untaxed and taxed, are settled when the customer
picks the option and copied as they are, so the order charges what the cart displayed.

## Extending an option with your own data

A module can add data to an option as it is attached to the cart line. Listen to
`customization_option_input_extend`:

```php
use Option\Event\OptionInputValidationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EngravingFontListener
{
    #[AsEventListener(OptionInputValidationEvent::CUSTOMIZATION_OPTION_INPUT_EXTEND)]
    public function addFont(OptionInputValidationEvent $event): void
    {
        if (self::ENGRAVING_OPTION_ID !== $event->getOptionId()) {
            return;
        }

        $event->setOptionCustomizationFormData(
            $event->getOptionCustomizationFormData() + ['font' => 'Copperplate'],
        );
    }
}
```

The event carries the option id, the data the customer submitted and the cart line. What
the listener leaves in it is stored with the option and travels to the order line.

The event is dispatched for every option, customizable or not, so a listener can attach
data to an option that asks the customer for nothing.

## API

The admin and front operations are documented with the rest of the API:

```
GET /api/docs
```

List the options a product accepts, from the product or from one of its sale elements.
Only options flagged visible are returned, with both untaxed and taxed prices:

```
GET /api/front/options?productId={productId}
GET /api/front/options?pseId={pseId}
GET /api/front/options/{optionId}
```

The admin operations are guarded by the `admin.module` permission, the same one the module
back-office screens check:

```
GET /api/admin/options
GET /api/admin/options/{optionId}
```

The options carried by a cart line are read on the core cart payloads, under the
`CartItemOptions` key of each cart item:

```
GET /api/front/cart
GET /api/front/cart_items/{id}
```

```json
{
  "id": 19,
  "quantity": 1,
  "CartItemOptions": {
    "options": [
      {
        "optionId": 1,
        "ref": "OPTION_REF",
        "title": "Gift wrap",
        "price": 96.0,
        "taxedPrice": 115.2,
        "quantity": 1.0,
        "customization": {"message": "Happy birthday"}
      }
    ]
  }
}
```

`CartItemOptions` is read-only. Options are written by submitting the add-to-cart form,
which validates each option against the product and prices the line accordingly; a payload
carrying `CartItemOptions` is rejected with a 400.

The lines an option became are removed from every front read of an order, so a theme
listing an order shows the products the customer chose and nothing else. Admin reads keep
every line.

## Overriding the back-office display

Customization data is printed under each order product line in the back office. To change
how it looks, override
`templates/backOffice/default-twig/Option/order-product/order_product_additional_data.html.twig`.
