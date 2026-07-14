<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Payments\PaymentGatewayFactory;
use App\Core\Request;
use App\Models\Booking;
use App\Models\Instructor;
use App\Models\MeetingLink;
use App\Models\Package;
use App\Models\Payment;

final class BookingController extends Controller
{
    public function schedulePage(Request $request): void
    {
        $instructorId = (int) $request->query('instructor_id');
        $packageId = (int) $request->query('package_id');

        $instructor = Instructor::findWithName($instructorId);
        $package = Package::find($packageId);

        if (!$instructor || !$package) {
            $this->redirect('/instructors');
        }

        $this->view('booking/schedule', [
            'instructor' => $instructor,
            'package' => $package,
        ], 'dashboard');
    }

    public function confirmPage(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);
        $gateway = PaymentGatewayFactory::make();
        $config = require dirname(__DIR__) . '/Config/app.php';

        $this->view('booking/confirm', [
            'booking' => $booking,
            'instructor' => Instructor::findWithName($booking['instructor_id']),
            'package' => Package::find($booking['package_id']),
            'paymentConfigured' => $gateway->isConfigured(),
            'paymentProvider' => $gateway->name(),
            'paymentMode' => $config['payments']['mode'],
        ], 'dashboard');
    }

    public function successPage(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);

        if (!in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed', 'completed'], true)) {
            $this->redirect('/booking/confirm/' . $ref);
        }

        $this->view('booking/success', [
            'booking' => $booking,
            'instructor' => Instructor::findWithName($booking['instructor_id']),
            'package' => Package::find($booking['package_id']),
        ], 'dashboard');
    }

    public function failedPage(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);

        $this->view('booking/failed', ['booking' => $booking], 'dashboard');
    }

    public function invoicePage(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);

        if (!in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed', 'completed'], true)) {
            $this->redirect('/booking/confirm/' . $ref);
        }

        $this->view('booking/invoice', [
            'booking' => $booking,
            'instructor' => Instructor::findWithName($booking['instructor_id']),
            'package' => Package::find($booking['package_id']),
            'payment' => Payment::forBooking($booking['id']),
            'meetingLink' => MeetingLink::forBooking($booking['id']),
        ], 'bare');
    }

    private function ownedBooking(string $ref): array
    {
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->redirect('/services');
        }

        return $booking;
    }
}
