<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Package;

final class ServiceController extends Controller
{
    public function index(Request $request): void
    {
        $packages = Package::featured();

        $this->view('services/index', ['packages' => $packages], 'main');
    }
}
