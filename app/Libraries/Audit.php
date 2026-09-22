<?php

namespace App\Libraries;

/**
 * Append-only audit trail. Values of sensitive fields are never written to the log.
 */
class Audit
{
    private const REDACT = ['phone', 'email', 'id_number', 'address', 'content', 'password', 'password_hash', 'pan', 'phone_hash', 'id_number_hash'];

    public static function log(string $action, ?string $resourceType = null, ?int $resourceId = null, ?array $before = null, ?array $after = null, ?int $userId = null): void
    {
        try {
            $req = service('request');
            db_connect()->table('audit_logs')->insert([
                'user_id'       => $userId ?? CurrentUser::id(),
                'action'        => $action,
                'resource_type' => $resourceType,
                'resource_id'   => $resourceId,
                'ip_address'    => $req->getIPAddress(),
                'user_agent'    => substr((string) $req->getUserAgent(), 0, 250),
                'before_json'   => $before === null ? null : json_encode(self::redact($before)),
                'after_json'    => $after === null ? null : json_encode(self::redact($after)),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }

    private static function redact(array $data): array
    {
        foreach ($data as $k => $v) {
            if (in_array($k, self::REDACT, true) && $v !== null && $v !== '') {
                $data[$k] = '[redacted]';
            }
        }

        return $data;
    }
}
