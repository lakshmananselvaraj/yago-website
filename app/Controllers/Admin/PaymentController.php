<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class PaymentController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $status = $request->query('status') ?: null;
        $gateway = $request->query('gateway') ?: null;
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $db = Database::connection();

        $conditions = [];
        $params = [];
        if ($status) {
            $conditions[] = 'pay.status = :status';
            $params['status'] = $status;
        }
        if ($gateway) {
            $conditions[] = 'pay.gateway = :gateway';
            $params['gateway'] = $gateway;
        }
        $whereSql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $countStmt = $db->prepare('SELECT COUNT(*) c FROM payments pay' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $sql = 'SELECT pay.*, b.booking_ref, u.name AS client_name, u.email AS client_email
                FROM payments pay
                INNER JOIN bookings b ON b.id = pay.booking_id
                INNER JOIN users u ON u.id = b.client_id'
                . $whereSql;
        $sql .= sprintf(' ORDER BY pay.created_at DESC LIMIT %d OFFSET %d', self::PER_PAGE, $offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        $this->view('admin/payments', [
            'payments' => $payments,
            'selectedStatus' => $status,
            'selectedGateway' => $gateway,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / self::PER_PAGE)),
        ], 'dashboard');
    }
}
