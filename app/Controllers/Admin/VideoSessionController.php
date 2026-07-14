<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;

final class VideoSessionController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/video-sessions', [], 'dashboard');
    }
}
