<?php

declare(strict_types=1);

namespace App\Controllers;

class AdminController
{
    public function login()
    {
        // Reutiliza completamente o AuthController
        $auth = new AuthController();
        return $auth->loginAdmin();
    }
}