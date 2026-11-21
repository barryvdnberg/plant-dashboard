<?php

namespace App\Enums;

enum FormOrder: string
{
    case NAME_FIRST = 'name-first';
    case PARENT_FIRST = 'parent-first';
}
