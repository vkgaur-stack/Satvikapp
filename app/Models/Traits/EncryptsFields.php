<?php

namespace App\Models\Traits;

use App\Libraries\Crypto;

/**
 * Transparent AES-256 encryption for selected columns.
 * Register with:  $beforeInsert = ['encryptFields'], $beforeUpdate = ['encryptFields'], $afterFind = ['decryptFields'].
 *
 * $encryptedFields : columns stored encrypted
 * $hashedFields    : plain column => hash column (HMAC) used for duplicate look-ups
 */
trait EncryptsFields
{
    protected function encryptFields(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }
        foreach ($this->encryptedFields as $field) {
            if (! array_key_exists($field, $data['data'])) {
                continue;
            }
            $plain = $data['data'][$field];
            $plain = ($plain === null || $plain === '') ? null : (string) $plain;
            if (isset($this->hashedFields[$field])) {
                $data['data'][$this->hashedFields[$field]] = Crypto::hash($plain);
            }
            $data['data'][$field] = Crypto::encrypt($plain);
        }

        return $data;
    }

    protected function decryptFields(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }
        $decrypt = function (array $row): array {
            foreach ($this->encryptedFields as $field) {
                if (array_key_exists($field, $row)) {
                    $row[$field] = Crypto::decrypt($row[$field]);
                }
            }

            return $row;
        };
        $data['data'] = ! empty($data['singleton']) ? $decrypt($data['data']) : array_map($decrypt, $data['data']);

        return $data;
    }
}
