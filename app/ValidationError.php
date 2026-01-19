<?php
declare(strict_types=1);

enum ValidationError
{
    case EMAIL_EMPTY;
    case EMAIL_INVALID_FORMAT;
}