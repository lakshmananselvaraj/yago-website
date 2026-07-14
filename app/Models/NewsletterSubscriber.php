<?php

namespace App\Models;

use App\Core\Model;

final class NewsletterSubscriber extends Model
{
    protected static string $table = 'newsletter_subscribers';

    public static function existsWithEmail(string $email): bool
    {
        return self::findBy('email', $email) !== null;
    }
}
