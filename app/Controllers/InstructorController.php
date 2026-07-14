<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Favorite;
use App\Models\Instructor;
use App\Models\InstructorAvailability;
use App\Models\InstructorReview;
use App\Models\Package;
use App\Models\ServiceType;

final class InstructorController extends Controller
{
    public function index(Request $request): void
    {
        $packageId = $request->query('package');
        $serviceTypeId = $request->query('service');

        $instructors = Instructor::searchActive($packageId ? ['package_id' => $packageId] : []);

        $this->view('instructors/index', [
            'instructors' => $instructors,
            'serviceTypes' => ServiceType::activeOrdered(),
            'selectedPackageId' => $packageId,
            'selectedServiceTypeId' => $serviceTypeId,
        ], 'main');
    }

    public function show(Request $request, string $id): void
    {
        $instructor = Instructor::findWithName((int) $id);

        if (!$instructor || $instructor['status'] !== 'active') {
            $this->redirect('/instructors');
        }

        $this->view('instructors/show', [
            'instructor' => $instructor,
            'reviews' => InstructorReview::forInstructor($instructor['id']),
            'availability' => InstructorAvailability::forInstructor($instructor['id']),
            'packageId' => $request->query('package'),
            'isFavorited' => Favorite::isFavorited(Auth::id(), (int) $instructor['id']),
        ], 'main');
    }
}
