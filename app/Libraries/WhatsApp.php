<?php

namespace App\Libraries;

/**
 * WhatsApp Business Cloud API (Meta). Needs WHATSAPP_TOKEN and WHATSAPP_PHONE_ID in .env.
 * NOTE: business-initiated messages outside a 24-hour customer window must use an approved
 * template (sendTemplate). Plain text (sendText) only works inside that window.
 */
class WhatsApp
{
    private const VERSION = 'v20.0';

    public static function configured(): bool
    {
        return env('WHATSAPP_TOKEN', '') !== '' && env('WHATSAPP_PHONE_ID', '') !== '';
    }

    public static function sendText(string $phone, string $text): ?string
    {
        return self::post(['type' => 'text', 'text' => ['preview_url' => false, 'body' => $text]], $phone);
    }

    /** @param string[] $bodyParams values for {{1}}, {{2}}... in the template body */
    public static function sendTemplate(string $phone, string $template, string $lang = 'en', array $bodyParams = []): ?string
    {
        $tpl = ['name' => $template, 'language' => ['code' => $lang]];
        if ($bodyParams) {
            $tpl['components'] = [['type' => 'body', 'parameters' => array_map(static fn ($p) => ['type' => 'text', 'text' => (string) $p], $bodyParams)]];
        }

        return self::post(['type' => 'template', 'template' => $tpl], $phone);
    }

    /** @return string|null null on success, else an error message */
    private static function post(array $payload, string $phone): ?string
    {
        if (! self::configured()) {
            return 'WhatsApp is not configured (set WHATSAPP_TOKEN and WHATSAPP_PHONE_ID in .env).';
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 10) {
            $digits = '91' . $digits; // default to India
        }
        $ch = curl_init('https://graph.facebook.com/' . self::VERSION . '/' . env('WHATSAPP_PHONE_ID') . '/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . env('WHATSAPP_TOKEN'), 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode(['messaging_product' => 'whatsapp', 'to' => $digits] + $payload),
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $code >= 300) {
            $msg = json_decode((string) $resp, true)['error']['message'] ?? 'HTTP ' . $code;
            log_message('error', 'WhatsApp send failed: ' . $msg);

            return 'WhatsApp: ' . $msg;
        }

        return null;
    }
}
