<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class CustomerController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $q = trim((string) $request->query('q', ''));
        $q = $q !== '' ? $q : null;
        // Test/demo accounts are hidden from this list by default (they're
        // never deleted, just excluded from normal operational views — see
        // migration 011). "all" or a specific status opts back in, so admins
        // can still find them when needed.
        $status = (string) $request->query('status', '');
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $db = Database::connection();

        $countSql = "SELECT COUNT(*) c FROM users u WHERE u.role = 'client'";
        $params = [];
        if ($q) {
            $countSql .= ' AND (u.name LIKE :q OR u.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($status !== '' && $status !== 'all') {
            $countSql .= ' AND u.status = :status';
            $params['status'] = $status;
        } elseif ($status === '') {
            $countSql .= " AND u.status != 'test'";
        }
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $sql = "SELECT u.*, cp.country, cp.timezone, cp.bio,
                       (SELECT COUNT(*) FROM bookings b WHERE b.client_id = u.id) AS booking_count,
                       (SELECT COALESCE(SUM(p.amount), 0) FROM payments p
                            INNER JOIN bookings b ON b.id = p.booking_id
                            WHERE b.client_id = u.id AND p.status = 'success') AS total_paid
                FROM users u
                LEFT JOIN client_profiles cp ON cp.user_id = u.id
                WHERE u.role = 'client'";

        if ($q) {
            $sql .= ' AND (u.name LIKE :q OR u.email LIKE :q)';
        }
        if ($status !== '' && $status !== 'all') {
            $sql .= ' AND u.status = :status';
        } elseif ($status === '') {
            $sql .= " AND u.status != 'test'";
        }
        $sql .= sprintf(' ORDER BY u.created_at DESC LIMIT %d OFFSET %d', self::PER_PAGE, $offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        $this->view('admin/customers', [
            'customers' => $customers,
            'q' => $q,
            'status' => $status,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / self::PER_PAGE)),
        ], 'dashboard');
    }

    public function show(Request $request, int $id): void
    {
        $db = Database::connection();

        $stmt = $db->prepare(
            "SELECT u.*, cp.age, cp.gender, cp.country, cp.bio, cp.timezone, cp.avatar_path
             FROM users u
             LEFT JOIN client_profiles cp ON cp.user_id = u.id
             WHERE u.id = :id AND u.role = 'client'
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            $this->fail('Customer not found.', 404);
        }

        $bookingStmt = $db->prepare(
            'SELECT b.*, u.name AS client_name, u.email AS client_email,
                    iu.name AS instructor_name, p.name AS package_name
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN instructors i ON i.id = b.instructor_id
             INNER JOIN users iu ON iu.id = i.user_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.client_id = :id
             ORDER BY b.created_at DESC
             LIMIT 100'
        );
        $bookingStmt->execute(['id' => $id]);
        $bookings = $bookingStmt->fetchAll();

        $paymentStmt = $db->prepare(
            'SELECT pay.*, b.booking_ref
             FROM payments pay
             INNER JOIN bookings b ON b.id = pay.booking_id
             WHERE b.client_id = :id
             ORDER BY pay.created_at DESC
             LIMIT 100'
        );
        $paymentStmt->execute(['id' => $id]);
        $payments = $paymentStmt->fetchAll();

        $this->view('admin/customer-detail', [
            'customer' => $customer,
            'bookings' => $bookings,
            'payments' => $payments,
        ], 'dashboard');
    }
}
