<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Validator;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;

final class PublicController extends Controller
{
    public function submitContact(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email|max:190',
            'message' => 'required|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $name = (string) $request->input('name');
        $email = (string) $request->input('email');
        $message = (string) $request->input('message');

        ContactMessage::insert(['name' => $name, 'email' => $email, 'message' => $message]);

        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        Mailer::send(
            $config['mail']['from_address'],
            'New contact message from ' . $name,
            '<p><strong>' . htmlspecialchars($name, ENT_QUOTES) . '</strong> (' . htmlspecialchars($email, ENT_QUOTES) . ') wrote:</p><p>' . nl2br(htmlspecialchars($message, ENT_QUOTES)) . '</p>'
        );

        $this->success(null, 'Thanks for reaching out — we\'ll get back to you soon.');
    }

    public function subscribeNewsletter(Request $request): void
    {
        $email = (string) $request->input('email', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('Please enter a valid email address.', 422);
        }

        if (!NewsletterSubscriber::existsWithEmail($email)) {
            NewsletterSubscriber::insert(['email' => $email]);
        }

        $this->success(null, 'You\'re subscribed! Watch your inbox for updates.');
    }
}
