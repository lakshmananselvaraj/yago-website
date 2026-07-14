<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use PDO;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ReportController extends Controller
{
    private const TYPES = ['revenue', 'bookings', 'payments', 'instructor_performance'];

    public function page(Request $request): void
    {
        $this->view('admin/reports', [
            'from' => $request->query('from') ?: date('Y-m-d', strtotime('-30 days')),
            'to' => $request->query('to') ?: date('Y-m-d'),
        ], 'dashboard');
    }

    public function export(Request $request): void
    {
        $type = (string) $request->query('type', 'revenue');
        $format = (string) $request->query('format', 'csv');
        $from = (string) $request->query('from', date('Y-m-d', strtotime('-30 days')));
        $to = (string) $request->query('to', date('Y-m-d'));

        if (!in_array($type, self::TYPES, true)) {
            $this->fail('Unknown report type.', 422);
        }

        [$headers, $rows] = $this->buildReport($type, $from, $to);

        if ($format === 'csv') {
            $this->streamCsv($type, $headers, $rows);
        }

        if ($format === 'pdf') {
            $this->streamPdf($type, $from, $to, $headers, $rows);
        }

        if ($format === 'xlsx') {
            $this->streamXlsx($type, $headers, $rows);
        }

        $this->view('admin/report-print', [
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'headers' => $headers,
            'rows' => $rows,
        ], 'bare');
    }

    private function buildReport(string $type, string $from, string $to): array
    {
        $db = Database::connection();

        return match ($type) {
            'revenue' => $this->revenueReport($db, $from, $to),
            'bookings' => $this->bookingsReport($db, $from, $to),
            'payments' => $this->paymentsReport($db, $from, $to),
            'instructor_performance' => $this->instructorPerformanceReport($db, $from, $to),
        };
    }

    private function revenueReport(PDO $db, string $from, string $to): array
    {
        $stmt = $db->prepare(
            "SELECT DATE(created_at) AS date, COUNT(*) AS bookings, COALESCE(SUM(total_amount),0) AS revenue
             FROM bookings
             WHERE status IN ('awaiting_trainer_approval','confirmed','completed') AND DATE(created_at) BETWEEN :from AND :to
             GROUP BY DATE(created_at) ORDER BY date ASC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);

        return [['Date', 'Bookings', 'Revenue'], $stmt->fetchAll()];
    }

    private function bookingsReport(PDO $db, string $from, string $to): array
    {
        $stmt = $db->prepare(
            "SELECT b.booking_ref, u.name AS client, iu.name AS instructor, p.name AS package,
                    b.slot_date, b.start_time, b.status, b.total_amount
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN instructors i ON i.id = b.instructor_id
             INNER JOIN users iu ON iu.id = i.user_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE DATE(b.created_at) BETWEEN :from AND :to
             ORDER BY b.created_at DESC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);

        return [['Booking Ref', 'Client', 'Instructor', 'Package', 'Date', 'Time', 'Status', 'Total'], $stmt->fetchAll()];
    }

    private function paymentsReport(PDO $db, string $from, string $to): array
    {
        $stmt = $db->prepare(
            "SELECT pay.id, b.booking_ref, pay.gateway, pay.status, pay.amount, pay.currency, pay.created_at
             FROM payments pay
             INNER JOIN bookings b ON b.id = pay.booking_id
             WHERE DATE(pay.created_at) BETWEEN :from AND :to
             ORDER BY pay.created_at DESC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);

        return [['Payment ID', 'Booking Ref', 'Gateway', 'Status', 'Amount', 'Currency', 'Date'], $stmt->fetchAll()];
    }

    private function instructorPerformanceReport(PDO $db, string $from, string $to): array
    {
        $stmt = $db->prepare(
            "SELECT u.name AS instructor, COUNT(*) AS sessions, COALESCE(SUM(b.total_amount),0) AS revenue,
                    i.rating_avg, i.rating_count
             FROM bookings b
             INNER JOIN instructors i ON i.id = b.instructor_id
             INNER JOIN users u ON u.id = i.user_id
             WHERE b.status IN ('awaiting_trainer_approval','confirmed','completed') AND DATE(b.created_at) BETWEEN :from AND :to
             GROUP BY i.id, u.name, i.rating_avg, i.rating_count
             ORDER BY revenue DESC"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);

        return [['Instructor', 'Sessions', 'Revenue', 'Avg Rating', 'Rating Count'], $stmt->fetchAll()];
    }

    private function streamCsv(string $type, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '-report-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, array_values($row));
        }
        fclose($out);
        exit;
    }

    private function streamPdf(string $type, string $from, string $to, array $headers, array $rows): never
    {
        $title = ucwords(str_replace('_', ' ', $type)) . ' Report';
        $rangeLabel = htmlspecialchars($from, ENT_QUOTES) . ' to ' . htmlspecialchars($to, ENT_QUOTES);

        $headHtml = implode('', array_map(
            static fn (string $h): string => '<th>' . htmlspecialchars($h, ENT_QUOTES) . '</th>',
            $headers
        ));

        $bodyHtml = implode('', array_map(
            static fn (array $row): string => '<tr>' . implode('', array_map(
                static fn ($cell): string => '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES) . '</td>',
                array_values($row)
            )) . '</tr>',
            $rows
        ));

        $html = <<<HTML
            <!doctype html>
            <html><head><meta charset="utf-8"><style>
                body { font-family: sans-serif; font-size: 11px; color: #222; }
                h1 { font-size: 16px; margin-bottom: 2px; }
                p.range { color: #666; margin-top: 0; margin-bottom: 16px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style></head><body>
            <h1>{$title}</h1>
            <p class="range">{$rangeLabel}</p>
            <table><thead><tr>{$headHtml}</tr></thead><tbody>{$bodyHtml}</tbody></table>
            </body></html>
            HTML;

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $type . '-report-' . date('Y-m-d') . '.pdf"');
        echo $dompdf->output();
        exit;
    }

    private function streamXlsx(string $type, array $headers, array $rows): never
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $col => $header) {
            $ref = Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($ref, $header);
            $sheet->getStyle($ref)->getFont()->setBold(true);
        }

        foreach (array_values($rows) as $rowIndex => $row) {
            foreach (array_values($row) as $col => $value) {
                $ref = Coordinate::stringFromColumnIndex($col + 1) . ($rowIndex + 2);
                $sheet->setCellValue($ref, $value);
            }
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $type . '-report-' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
