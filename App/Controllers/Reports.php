<?php

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\ReportExporter;
use App\Libraries\Reports as ReportBuilder;
use CodeIgniter\Exceptions\PageNotFoundException;

class Reports extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('reports/fundraising'));
    }

    public function show(string $slug)
    {
        if (! can('reports.view')) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
        $report = ReportBuilder::build($slug);
        if ($report === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('reports/show', ['slug' => $slug, 'report' => $report, 'tabs' => ReportBuilder::TABS]);
    }

    /**
     * GET /reports/{slug}/export?format=csv|pdf|xlsx   (csv is the default, for old links/bookmarks)
     */
    public function export(string $slug)
    {
        if (! can('reports.export')) {
            return $this->response->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
        $report = ReportBuilder::build($slug);
        if ($report === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $format = strtolower((string) ($this->request->getGet('format') ?: 'csv'));
        if (! in_array($format, ['csv', 'pdf', 'xlsx'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
        Audit::log('export', 'reports:' . $slug, null, null, ['format' => $format]);
        $filename = 'satvikdaan-' . $slug . '-' . date('Ymd');

        return match ($format) {
            'pdf'   => $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"')
                ->setBody(ReportExporter::pdf($report, $slug)),
            'xlsx'  => $this->streamXlsx($report, $slug, $filename),
            default => $this->csv($report, $filename),
        };
    }

    private function csv(array $report, string $filename)
    {
        // Guard against spreadsheet formula injection in exported text.
        $safe = static fn ($v) => is_string($v) && $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false ? "'" . $v : $v;
        $fh   = fopen('php://temp', 'r+');
        fputcsv($fh, [$report['title'] . ' - ' . $report['table_title']]);
        fputcsv($fh, array_map(static fn ($k) => $k[0], $report['kpis']));
        fputcsv($fh, array_map(static fn ($k) => $k[1], $report['kpis']));
        fputcsv($fh, []);
        fputcsv($fh, $report['headers']);
        foreach ($report['rows'] as $row) {
            fputcsv($fh, array_map($safe, $row));
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    /** PhpSpreadsheet writes to a temp file; stream it out, then remove it. */
    private function streamXlsx(array $report, string $slug, string $filename)
    {
        $path = ReportExporter::xlsx($report, $slug);
        $body = file_get_contents($path);
        unlink($path);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"')
            ->setBody($body);
    }
}
