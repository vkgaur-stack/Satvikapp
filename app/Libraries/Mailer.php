<?php

namespace App\Libraries;

/** Thin wrapper over CodeIgniter's Email service. SMTP settings live in .env (email.*). */
class Mailer
{
    /** @return string|null null on success, otherwise a short error message */
    public static function send(string $to, string $subject, string $html): ?string
    {
        $cfg   = config('Email');
        $email = service('email');
        $email->clear(true);
        $email->setFrom($cfg->fromEmail ?: 'no-reply@localhost', $cfg->fromName ?: org('name'));
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);
        if (! $email->send(false)) {
            $err = trim(strip_tags($email->printDebugger(['headers'])));
            log_message('error', 'Email to ' . $to . ' failed: ' . $err);

            return mb_substr($err, 0, 400) ?: 'Email could not be sent.';
        }

        return null;
    }
}
