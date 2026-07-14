<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], ?string $layout = 'main'): void
    {
        echo View::render($template, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): never
    {
        Response::json(array_filter([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], static fn ($v) => $v !== null) + ['success' => true, 'message' => $message], $status);
    }

    protected function fail(string $message, int $status = 422, array $errors = [], array $data = []): never
    {
        $payload = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        Response::json($payload + $data, $status);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function validate(Request $request, array $rules): Validator
    {
        return Validator::make($request->all(), $rules);
    }
}
