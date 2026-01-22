<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\Response;
use Core\ViewRenderer;

class TopController
{
    public function __construct(private Request $request, private Response $response)
    {
    }

    public function showTop(ViewRenderer $viewRenderer): void
    {
        $viewRenderer->render('top');
    }
}