<?php

namespace App\Libraries;

/**
 * Re-engagement drafts for lapsed donors. Uses Claude (Anthropic Messages API) when ANTHROPIC_API_KEY
 * is set, and a fixed template otherwise. Only first name, gift dates/amounts and campaign names are
 * sent to the API - never contact details, PAN or notes. A human always reviews before anything is sent.
 */
class DraftWriter
{
    /** @return array{subject:string,body:string,source:string} */
    public static function write(array $donor, array $gifts): array
    {
        $first = explode(' ', trim($donor['name']))[0] ?: 'Friend';
        $last  = $gifts[0] ?? null;
        $ctx   = [
            'first_name'        => $first,
            'gifts_count'       => count($gifts),
            'last_gift_date'    => $last ? fmt_date($last['donated_on'], 'j F Y') : null,
            'last_gift_amount'  => $last ? money($last['amount'], 'Rs. ', 0) : null,
            'last_campaign'     => $last['campaign'] ?? null,
            'months_since_gift' => $last ? max(1, (int) round((time() - strtotime($last['donated_on'])) / 2629800)) : null,
        ];

        if (env('ANTHROPIC_API_KEY', '') !== '') {
            try {
                $r = self::viaClaude($ctx);
                if ($r) {
                    return $r + ['source' => 'ai'];
                }
            } catch (\Throwable $e) {
                log_message('error', 'Draft via Claude failed: ' . $e->getMessage());
            }
        }

        return self::template($ctx) + ['source' => 'template'];
    }

    private static function viaClaude(array $ctx): ?array
    {
        $system = 'You write short, warm re-engagement emails for ' . org('name') . ', an Indian nonprofit working in healthcare, education, vocational training and food security for rural women, marginalized youth and disaster-affected families. '
            . 'Use ONLY the facts provided. Never invent impact numbers, stories or promises. No guilt or pressure. Thank the donor, mention their last gift naturally, and invite them to stay connected. '
            . '110-150 words, plain text, sign off "The ' . org('name') . ' Team". Reply with JSON only: {"subject": "...", "body": "..."}';

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['x-api-key: ' . env('ANTHROPIC_API_KEY'), 'anthropic-version: 2023-06-01', 'content-type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode([
                'model'      => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
                'max_tokens' => 700,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => 'Donor facts: ' . json_encode($ctx)]],
            ]),
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $code >= 300) {
            throw new \RuntimeException('Anthropic API HTTP ' . $code);
        }
        $text = json_decode($resp, true)['content'][0]['text'] ?? '';
        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', $text));
        $j    = json_decode($text, true);

        return (is_array($j) && ! empty($j['subject']) && ! empty($j['body'])) ? ['subject' => (string) $j['subject'], 'body' => (string) $j['body']] : null;
    }

    private static function template(array $c): array
    {
        $gift = $c['last_gift_date']
            ? "Your gift of {$c['last_gift_amount']} on {$c['last_gift_date']}" . ($c['last_campaign'] ? " to {$c['last_campaign']}" : '') . ' meant a great deal to us.'
            : 'Your support has meant a great deal to us.';

        return [
            'subject' => "{$c['first_name']}, thank you — and a note from " . org('name'),
            'body'    => "Dear {$c['first_name']},\n\n{$gift} It's been a little while since we last connected, and we wanted to say thank you again.\n\n"
                . "Our teams continue to work in healthcare, education, vocational training and food support with rural women, young people and families affected by disasters. "
                . "If you'd like to hear how the work is going, or to support it again, just reply to this email - we'd love to hear from you.\n\nWith gratitude,\nThe " . org('name') . ' Team',
        ];
    }
}
