<?php

namespace App\Libraries;

/**
 * Holds the authenticated user for the current request, whether they came in
 * through the web session (AuthFilter) or an API bearer token (ApiAuthFilter).
 */
final class CurrentUser
{
    private static ?array $user = null;

    public static function set(?array $user): void
    {
        self::$user = $user;
    }

    public static function get(): ?array
    {
        return self::$user;
    }

    public static function id(): ?int
    {
        return isset(self::$user['id']) ? (int) self::$user['id'] : null;
    }

    public static function role(): ?string
    {
        return self::$user['role'] ?? null;
    }

    public static function can(string $permission): bool
    {
        return in_array($permission, self::$user['perms'] ?? [], true);
    }
}
