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

Two routes are available to manipulate options and products in the shopping cart.

See OpenApi doc : 
```plaintext
GET /open_api/doc
```

List options for a product selling unit (pse):

```plaintext
GET /open_api/option/get/{pseId}
```

Add one or more options to a cart item :

```plaintext
POST /open_api/option/add/{cartItemId}
```


## Hook

In addition to the hook to attach the menu dedicated to option management in the main backOffice menu, a hook is used to
link an order product with the information provided by the option. To customize the display of this information on an 
invoice, you will need to override the order_product_additional_data.html template [order_product_additional_data.html](templates%2FbackOffice%2Fdefault%2Forder-product%2Forder_product_additional_data.html).


## Loop
Use [generic](https://doc.thelia.net/docs/loops/Generic) loop !
