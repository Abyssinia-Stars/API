<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getUsers(Request $request)
    {
        $query = $request->validate([
            "verified" => 'sometimes|boolean',
            "page" => "sometimes|integer",
            "limit" => "sometimes|integer|min:1"
        ]);

        // Cast verified to boolean properly. This handles null, true, false, "true", "false", etc.
        $verified = filter_var($request->query('verified'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $usersQuery = User::query();

        
            $usersQuery->where("role", "artist")->orWhere("role", "customer")->orWhere("role", "manager");
        

        

        // Implement pagination if limit is provided, else return all
        if (isset($query['limit'])) {
            $users = $usersQuery->paginate($query['limit']);
        } else {
            $users = $usersQuery->get();
        }

        return response()->json($users);
    }

    public function verifyUser(User $user)
    {
        $user->is_verified = true;
        $user->save();
        return response()->json($user);
    }

    public function toggleIsActive(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['message' => 'User is_active status updated successfully']);
    }

    public function setVerificationStatus(User $user, Request $request)
    {
        $request->validate([
            "is_verified" => "required|in:pending,unverified,verified"
        ]);

        $user->is_verified = $request->is_verified;
        $user->save();
        return response()->json(['message' => 'User verification status updated successfully']);
    }
}
