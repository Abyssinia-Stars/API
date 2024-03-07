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

        if (!is_null($verified)) {
            $usersQuery->where("is_verified", $query['verified']);
        }

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
}
