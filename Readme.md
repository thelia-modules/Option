# Option

This module allows you to manage the addition of paid options to your products.

## Installation

### Composer

```
composer require thelia/option-module:~1.0
```

## Usage

Options closely resemble a standard Thelia product, with a price to which you can add tax rules, promotional status, an 
image, a description, etc.

### BackOffice Configuration

From the module menu, you can: :
* Create, modify, or delete an option
* Assign an option to a product, category, or template

Assigning an option to a category or template will affect all linked products, making it easy to manage options and their 
assignment to relevant products.

### Customization of Options

An option may require user input (e.g., customizing a knife with text). To achieve this, you can link an option to a 
Symfony form. This form inherits from the class [BaseOptionFrontForm.php](Form%2FBase%2FBaseOptionFrontForm.php). The form describes all the fields necessary for
adding the product to the shopping cart (in this case, the knife). The form's name should correspond to the option 
reference (see: ```getName()```).

``` php
class OptionKnifeTextForm extends BaseOptionFrontForm
{
    protected function buildForm(): void
    {
        parent::buildForm();
        [...]
    }

    public static function getName():string
    {
        return 'OPTION_REF';
    }
}
```

Front-End Application

The options of a product are exposed as an API Platform resource. See the API doc:

```plaintext
GET /api/docs
```

List the options a product accepts, from the product or from one of its sale elements.
Only options flagged visible in the back-office are returned, with both untaxed and
taxed prices:

```plaintext
GET /api/front/options?productId={productId}
GET /api/front/options?pseId={pseId}
GET /api/front/options/{optionId}
```

The options carried by a cart item are read on the cart payloads of the core, under
the `CartItemOptions` key of each cart item:

```plaintext
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

Writing options onto a cart item is not exposed yet: the endpoint that validated the
customization form of each option and adjusted the price of the cart line has not been
ported. `CartItemOptions` is read-only, and a payload carrying it is rejected with a
400.


## Hook

In addition to the hook to attach the menu dedicated to option management in the main backOffice menu, a hook is used to
link an order product with the information provided by the option. To customize the display of this information on an 
invoice, you will need to override the order_product_additional_data.html template [order_product_additional_data.html](templates%2FbackOffice%2Fdefault%2Forder-product%2Forder_product_additional_data.html).


## Loop
Use [generic](https://doc.thelia.net/docs/loops/Generic) loop !
