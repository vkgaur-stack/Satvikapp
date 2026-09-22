<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Stores uploads encrypted, outside the web root (writable/uploads/<module>/).
 */
class SecureFiles
{
    public const MAX_BYTES = 5242880; // 5 MB
    public const ALLOWED   = ['image/jpeg', 'image/png', 'application/pdf'];

    /** @return array{path:string,name:string,mime:string,size:int} */
    public static function store(UploadedFile $file, string $folder): array
    {
        $dir = WRITEPATH . 'uploads/' . $folder . '/';
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $rel = $folder . '/' . bin2hex(random_bytes(16)) . '.enc';
        file_put_contents(WRITEPATH . 'uploads/' . $rel, Crypto::encryptBinary((string) file_get_contents($file->getTempName())));

        return [
            'path' => $rel,
            'name' => substr(preg_replace('/[^A-Za-z0-9._ -]/', '_', $file->getClientName()), 0, 200),
            'mime' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
        ];
    }

    public static function read(string $relativePath): ?string
    {
        $full = realpath(WRITEPATH . 'uploads/' . $relativePath);
        $base = realpath(WRITEPATH . 'uploads');
        if ($full === false || $base === false || ! str_starts_with($full, $base) || ! is_file($full)) {
            return null;
        }

        return Crypto::decryptBinary((string) file_get_contents($full));
    }
}
