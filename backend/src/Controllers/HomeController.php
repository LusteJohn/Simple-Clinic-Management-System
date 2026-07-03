<?php

namespace src\Controllers;

use src\Helpers\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home');
    }
}
