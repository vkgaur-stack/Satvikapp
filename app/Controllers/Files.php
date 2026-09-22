<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\CurrentUser;
use App\Libraries\ModuleService;
use App\Libraries\SecureFiles;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Streams encrypted uploads (documents, proof of delivery) after a permission check; every download is audited. */
class Files extends BaseController
{
    public function download(string $slug, int $id)
    {
        $svc = new ModuleService();
        $def = $svc->def($slug);
        if (! $def || ! can($slug . '.view') || ($slug === 'documents' && ! can('pii.decrypt'))) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
        $row = $svc->find($def, $id);
        $fileField = null;
        foreach ($def['fields'] as $f) {
            if ($f['type'] === 'file') {
                $fileField = $f;
                break;
            }
        }
        if (! $row || ! $fileField || empty($row[$fileField['store']['path'] ?? $fileField['name']])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $store = $fileField['store'] ?? ['path' => $fileField['name']];
        $data  = SecureFiles::read((string) $row[$store['path']]);
        if ($data === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        Audit::log('download', $slug, $id);

        $name = $row[$store['name'] ?? ''] ?? ('file-' . $id);
        $mime = $row[$store['mime'] ?? ''] ?? (finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $data) ?: 'application/octet-stream');
        if (! in_array($mime, SecureFiles::ALLOWED, true)) {
            $mime = 'application/octet-stream';
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . str_replace('"', '', (string) $name) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($data);
    }

    /** Program Manager marks a document as checked. */
    public function verify(int $id)
    {
        if (! can('documents.update')) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
        $db = db_connect();
        $doc = $db->table('documents')->where('id', $id)->where('deleted_at', null)->get()->getRowArray();
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }
        $db->table('documents')->where('id', $id)->update(['verified_by' => CurrentUser::id(), 'verified_at' => date('Y-m-d H:i:s')]);
        Audit::log('verify', 'documents', $id);

        return redirect()->back()->with('success', 'Document marked as verified.');
    }
}
