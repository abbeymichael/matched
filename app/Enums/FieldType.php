<?php

namespace App\Enums;

enum FieldType: string
{
    case SingleSelect = 'single_select';
    case MultiSelect = 'multi_select';
    case Scale = 'scale';
    case Range = 'range';
    case Number = 'number';
    case Geo = 'geo';
    case Text = 'text';
}
