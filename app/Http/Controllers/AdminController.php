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
use App\Models\Manager;
use App\Models\MainTransaction;


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

    public function getUser($id)
    {
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln($user->all());
        $user = User::where('id', $id)->get(['id', 'name', 'email', 'profile_picture', 'role', 'is_verified', 'is_active'])->first();
        $profile = [];

        if ($user->role === "artist") {
            $profile = ArtistProfile::where("user_id", $user->id)->get(['bio', 'category', 'attachments','manager_id','is_subscribed','location','gender'])->first();
        }
        if($user->role === "manager"){
            $profile = Manager::where("user_id", $user->id)->first();
            $artistsManagedByManager = ArtistProfile::where('manager_id', $id)->get("user_id","name");
            $artistProfile = [];
            foreach($artistsManagedByManager as $artist){
                $artistProfile[] = User::where('id', $artist->user_id)->get(["name","profile_picture","id","email","user_name"])->first();
                
            }


          
            return response()->json([
                'user' => $user,
                'profile' => $profile,
                'artists_managed' => $artistProfile
            ]);
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

    public function getMainTransactionsAndBalance(Request $request){

        $validation = Validator::make($request->all(), [
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validation->errors()
            ], 400);
        }

        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $transactions = MainTransaction::orderBy('id', 'desc')->paginate($limit, ['*'], 'page', $page);
        $balance = 0;
        $allTransactions = MainTransaction::all();
        //convert all from and to ids with names from user
        foreach($transactions as $transaction){
            $transaction->client_id = User::where('id', $transaction->client_id)->get('name')->first();
            $transaction->artist_id = User::where('id', $transaction->artist_id)->get('name')->first();
        }
        foreach($allTransactions as $transaction){
            $balance += $transaction->net_amount;
        }
        return response()->json(['transactions' => $transactions, 'balance' => $balance]);
    }


}
