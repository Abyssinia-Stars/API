<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'required|integer|min:1|max:100', // Adjust max limit as needed
            'current_page' => 'required|integer|min:1',
            'role' => 'in:artist,customer,manager,admin', // Allowed roles (optional)
            'is_verified' => 'boolean', // Optional, true or false
            'search' => 'string|nullable', // Optional search term
            'sort' => ['string', 'nullable', 'regex:/^([a-zA-Z0-9_]+)(,(asc|desc))?$/'], // Custom sort validation
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $perPage = $request->query('per_page');
        $currentPage = $request->query('current_page');
        $isVerified = $request->query('is_verified');
        $role = $request->query('role');
        $search = $request->query('search');
        $sortParam = $request->query('sort');

        // Extract sort field and order (already validated)
        $sortField = null;
        $sortOrder = 'desc'; // Default to descending order

        if ($sortParam) {
            $sortParts = explode(',', $sortParam);

            if (count($sortParts) === 1) {
                $sortField = $sortParts[0];
            } elseif (count($sortParts) === 2) {
                $sortField = $sortParts[0];
                $sortOrder = strtolower($sortParts[1]) === 'asc' ? 'asc' : 'desc';
            }

            // Validate allowed sort field
            $allowedFields = ['name', 'email', 'role', 'is_verified'];
            if (!in_array($sortField, $allowedFields)) {
                // Return error for invalid sort field
                return response()->json([
                    'message' => 'Invalid sort field. Allowed fields: ' . implode(', ', $allowedFields)
                ], 400);
            }
        }

        $users = User::query();

        if (isset($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($role)) {
            $users->where('role', $role);
        }

        if (isset($isVerified)) {
            $users->where('is_verified', $isVerified);
        }

        if ($sortField) {
            $users->orderBy($sortField, $sortOrder);
        } else {
            // Default sorting by name, descending
            $users->orderBy('name', 'desc');
        }

        $users = $users->orderBy('name') // Default sorting by name (optional)
            ->paginate($perPage, ['*'], 'page', $currentPage); // Use custom query params

        return response()->json($users);

    }

    public function verifyUser(User $user)
    {
        $user->is_verified = true;
        $user->save();
        return response()->json($user);
    }
}
