<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ArtistProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\SendNotificationTry;
use Illuminate\Support\Facades\Broadcast;
use App\Events\VerifyIdEvent;
use App\Models\Notification;

class AdminController extends Controller
{
    public function getUsers(Request $request)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();


        $validator = Validator::make($request->all(), [
            'per_page' => 'required|integer|min:1|max:100', // Adjust max limit as needed
            'current_page' => 'required|integer|min:1',
            'role' => 'in:artist,customer,manager,all', // Allowed roles (optional)
            'is_verified' => 'in:verified,unverified,pending', // Optional, true or false
            'q' => 'string|nullable', // Optional search term
            'sort' => ['string', 'nullable', 'regex:/^([a-zA-Z0-9_]+)(,(asc|desc))?$/'], // Custom sort validation
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }



        $out->write($request->all());

        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('current_page', 1);
        $isVerified = $request->input('is_verified', 'all');
        $role = $request->input('role', 'all');
        $q = $request->input('q', '');
        $sortParam = $request->input('sort');


        if ($role) {

            if ($role == 'all') {
                $users = User::where('role', '!=', 'admin');
            } else {
                $users = User::where('role', $role);
            }
        }
        if ($isVerified != 'all') {
            $users = $users->where('is_verified', $isVerified);
        }

        if ($q) {
            $users = $users->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        if ($sortParam) {
            $sort = explode(',', $sortParam);
            $users = $users->orderBy($sort[0], $sort[1] ?? 'asc');
        }



        $users = $users->orderBy('id') // Default sorting by name (optional)
            ->paginate($perPage, ['*'], 'page', $currentPage); // Use custom query params

        // $out->writeln($isVerified);
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

    public function getUser(User $user)
    {
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln($user->all());
        $profile = null;

        if ($user->role === "artist") {
            $profile = ArtistProfile::where("user_id", $user->id)->first();
        }

        return response()->json(['user' => $user, 'profile' => $profile]);
    }

    public function setVerificationStatus(User $user, Request $request)
    {

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($request->all());
        // $user = auth()->user();

        $request->validate([
            "is_verified" => "required|in:pending,unverified,verified"
        ]);

        $user->is_verified = $request->is_verified;

        $user->save();

        $notification = new Notification([
            'user_id' => $user->id,
            'notification_type' => 'request',
            'source_id' => 1,
            'message' =>'Id Verification ' . $request->is_verified,
            'title' => 'ID Verification',
            'status' => 'unread'
        ]);
        $notification->save();
        broadcast(new VerifyIdEvent($notification));
        return response()->json(['message' => 'User verification status updated successfully']);
    }


}
