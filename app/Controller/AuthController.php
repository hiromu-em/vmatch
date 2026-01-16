<?php
declare(strict_types=1);

namespace Controller;

use Core\Request;
use Core\ViewRenderer;
use Vmatch\FormValidation;

class AuthController
{
    public function __construct(private Request $request)
    {
    }

    public function showLoginForm(): void
    {
        include __DIR__ . '/../../public/resources/views/login.php';
    }

    public function showRegisterForm(): void
    {
        include __DIR__ . '/../../public/resources/views/register.php';
    }

    public function registerHandle(FormValidation $formValidation): void
    {
        $email = $this->request->input('email');
    }
}