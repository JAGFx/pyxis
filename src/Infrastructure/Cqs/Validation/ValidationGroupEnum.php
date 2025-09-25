<?php

namespace App\Infrastructure\Cqs\Validation;

enum ValidationGroupEnum: string
{
    case Default  = 'Default';
    case Business = 'business';
}
