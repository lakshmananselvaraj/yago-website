<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Package;
use App\Models\ServiceType;

final class ServiceController extends Controller
{
    public function index(Request $request): void
    {
        $this->success(['service_types' => ServiceType::activeOrdered()]);
    }

    public function packages(Request $request, string $id): void
    {
        $this->success(['packages' => Package::forServiceType((int) $id)]);
    }
}
