<?php
declare(strict_types=1);

namespace Vmatch\Exception;

class DatabaseException extends \Exception
{
    public function __construct()
    {
        parent::__construct();
    }

}