<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Package;
use App\Models\ServiceType;

final class PackageController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::connection();
        $packages = $db->query(
            'SELECT p.*, st.name AS service_type_name
             FROM packages p
             INNER JOIN service_types st ON st.id = p.service_type_id
             ORDER BY p.is_active DESC, p.name ASC'
        )->fetchAll();

        $this->view('admin/packages', [
            'packages' => $packages,
            'serviceTypes' => ServiceType::activeOrdered(),
        ], 'dashboard');
    }

    public function store(Request $request): void
    {
        $validator = $this->validate($request, $this->rules());

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $id = Package::insert([
            'service_type_id' => (int) $request->input('service_type_id'),
            'name' => (string) $request->input('name'),
            'description' => $request->input('description') ?: null,
            'sessions_count' => (int) $request->input('sessions_count', 1),
            'duration_minutes' => (int) $request->input('duration_minutes', 60),
            'max_participants' => (int) $request->input('max_participants', 1),
            'price' => (float) $request->input('price'),
            'currency' => (string) $request->input('currency', 'INR'),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'is_featured' => $request->input('is_featured') ? 1 : 0,
        ]);

        ActivityLog::log(Auth::id(), 'package_created', 'package', $id, ['name' => $request->input('name')]);

        $this->success(['id' => $id], 'Package created.');
    }

    public function update(Request $request, int $id): void
    {
        if (!Package::find($id)) {
            $this->fail('Package not found.', 404);
        }

        $validator = $this->validate($request, $this->rules());

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        Package::update($id, [
            'service_type_id' => (int) $request->input('service_type_id'),
            'name' => (string) $request->input('name'),
            'description' => $request->input('description') ?: null,
            'sessions_count' => (int) $request->input('sessions_count', 1),
            'duration_minutes' => (int) $request->input('duration_minutes', 60),
            'max_participants' => (int) $request->input('max_participants', 1),
            'price' => (float) $request->input('price'),
            'currency' => (string) $request->input('currency', 'INR'),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'is_featured' => $request->input('is_featured') ? 1 : 0,
        ]);

        ActivityLog::log(Auth::id(), 'package_updated', 'package', $id, ['name' => $request->input('name')]);

        $this->success(null, 'Package updated.');
    }

    public function toggleActive(Request $request, int $id): void
    {
        $package = Package::find($id);

        if (!$package) {
            $this->fail('Package not found.', 404);
        }

        $newState = $package['is_active'] ? 0 : 1;
        Package::update($id, ['is_active' => $newState]);
        ActivityLog::log(Auth::id(), $newState ? 'package_activated' : 'package_deactivated', 'package', $id);

        $this->success(['is_active' => $newState], $newState ? 'Package activated.' : 'Package deactivated.');
    }

    private function rules(): array
    {
        return [
            'service_type_id' => 'required|integer',
            'name' => 'required|min:2|max:150',
            'sessions_count' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|max:3',
        ];
    }
}
