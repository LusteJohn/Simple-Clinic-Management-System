<?php

namespace src\Controllers;

use src\Helpers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            "name" => "John Mark",
            "role" => "Web Developer"
        ];

        $this->view('home', $data);
    }
}