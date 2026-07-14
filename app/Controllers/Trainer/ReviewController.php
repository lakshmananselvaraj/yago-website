<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Instructor;
use App\Models\InstructorReview;

final class ReviewController extends Controller
{
    public function index(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());

        $this->view('trainer/reviews', [
            'reviews' => InstructorReview::forInstructor((int) $instructor['id']),
            'ratingAvg' => (float) ($instructor['rating_avg'] ?? 0),
            'ratingCount' => (int) ($instructor['rating_count'] ?? 0),
        ], 'dashboard');
    }
}
