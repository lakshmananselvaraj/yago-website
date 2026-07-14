<?php

namespace App\Core;

final class Mailer
{
    public static function send(string $to, string $subject, string $bodyHtml): bool
    {
        $config = (require dirname(__DIR__) . '/Config/app.php')['mail'];

        if ($config['driver'] === 'log') {
            return self::logToFile($to, $subject, $bodyHtml);
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= sprintf("From: %s <%s>\r\n", $config['from_name'], $config['from_address']);

        return @mail($to, $subject, $bodyHtml, $headers);
    }

    private static function logToFile(string $to, string $subject, string $bodyHtml): bool
    {
        $logPath = dirname(__DIR__, 2) . '/storage/logs/mail.log';
        $entry = sprintf(
            "[%s] To: %s | Subject: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            str_repeat('-', 60),
            strip_tags($bodyHtml)
        );

        return file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}
