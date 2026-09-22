<?php

/**
 * Global helper functions (loaded automatically by CodeIgniter's bootstrap).
 */

use App\Libraries\CurrentUser;

if (! function_exists('can')) {
    /** Does the signed-in user (web session or API token) hold this permission? */
    function can(string $permission): bool
    {
        return CurrentUser::can($permission);
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        return CurrentUser::get();
    }
}

if (! function_exists('org')) {
    /** Organisation details for receipts / letters (set in .env). */
    function org(string $key): string
    {
        $map = [
            'name'    => env('ORG_NAME', 'Satvikdaan Foundation'),
            'address' => env('ORG_ADDRESS', ''),
            'pan'     => env('ORG_PAN', ''),
            'reg_80g' => env('ORG_80G_NO', ''),
            'email'   => env('ORG_EMAIL', ''),
            'phone'   => env('ORG_PHONE', ''),
        ];

        return (string) ($map[$key] ?? '');
    }
}

if (! function_exists('money')) {
    /** Format a number the Indian way: 12,34,567.00 */
    function money($n, string $symbol = '₹', int $decimals = 2): string
    {
        if ($n === null || $n === '') {
            return '—';
        }
        $n   = (float) $n;
        $neg = $n < 0;
        $s   = number_format(abs($n), $decimals, '.', '');
        [$int, $frac] = array_pad(explode('.', $s), 2, '');
        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($int, 0, -3));
            $int   = $rest . ',' . $last3;
        }

        return ($neg ? '-' : '') . $symbol . $int . ($decimals ? '.' . $frac : '');
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date($d, string $format = 'd M Y'): string
    {
        if (empty($d) || str_starts_with((string) $d, '0000')) {
            return '—';
        }
        $t = strtotime((string) $d);

        return $t ? date($format, $t) : '—';
    }
}

if (! function_exists('fy_bounds')) {
    /** Indian financial year (1 Apr - 31 Mar): current start, previous start, label. */
    function fy_bounds(?string $today = null): array
    {
        $t     = strtotime($today ?? date('Y-m-d'));
        $y     = (int) date('Y', $t);
        $start = (int) date('n', $t) >= 4 ? $y : $y - 1;

        return [
            'start' => sprintf('%d-04-01', $start),
            'prev'  => sprintf('%d-04-01', $start - 1),
            'label' => sprintf('%d-%02d', $start, ($start + 1) % 100),
        ];
    }
}

if (! function_exists('badge')) {
    /** Coloured status pill. */
    function badge(?string $value, ?string $label = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $green = ['active', 'issued', 'captured', 'verified', 'delivered', 'resolved', 'completed', 'enrolled', 'eligible', 'sent', 'graduated', 'fulfilled', 'yes', 'major'];
        $amber = ['pending', 'registered', 'lapsed', 'open', 'paused', 'gold'];
        $red   = ['failed', 'ineligible', 'dropped', 'disabled', 'inactive', 'cancelled', 'flagged'];
        $class = in_array($value, $green, true) ? 'success' : (in_array($value, $amber, true) ? 'warning' : (in_array($value, $red, true) ? 'danger' : 'secondary'));
        $label ??= ucwords(str_replace('_', ' ', $value));

        return '<span class="badge sd-badge text-bg-' . $class . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('amount_in_words')) {
    /** 1234.50 -> "Rupees One Thousand Two Hundred Thirty Four and Fifty Paise Only" */
    function amount_in_words(float $amount): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $two  = static fn (int $n): string => $n < 20 ? $ones[$n] : trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
        $three = static fn (int $n): string => trim(($n >= 100 ? $ones[intdiv($n, 100)] . ' Hundred ' : '') . $two($n % 100));

        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);
        if ($paise === 100) {
            $rupees++;
            $paise = 0;
        }
        $parts = [];
        $crore = intdiv($rupees, 10000000);
        $lakh  = intdiv($rupees % 10000000, 100000);
        $thou  = intdiv($rupees % 100000, 1000);
        $rest  = $rupees % 1000;
        if ($crore) {
            $parts[] = $three($crore) . ' Crore';
        }
        if ($lakh) {
            $parts[] = $two($lakh) . ' Lakh';
        }
        if ($thou) {
            $parts[] = $two($thou) . ' Thousand';
        }
        if ($rest) {
            $parts[] = $three($rest);
        }
        $words = 'Rupees ' . ($parts ? implode(' ', $parts) : 'Zero');
        if ($paise) {
            $words .= ' and ' . $two($paise) . ' Paise';
        }

        return $words . ' Only';
    }
}
