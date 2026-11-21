<?php

namespace App\Support\Query\Enums;

enum SelectMode: string
{
    case SetIfEmpty = 'setIfEmpty';
    case OnlyKeepId = 'onlyKeepId';
    case Ignore = 'ignore';
}
