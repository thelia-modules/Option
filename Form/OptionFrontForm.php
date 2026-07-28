<?php

declare(strict_types=1);

namespace Option\Form;

use Option\Form\Base\BaseOptionFrontForm;

class OptionFrontForm extends BaseOptionFrontForm
{
    //Option without customization

    public static function getName():string
    {
        return 'option';
    }
}