<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


use App\Models\MainTransaction;
use App\Models\TxnHistory;
use App\Models\User;
use App\Models\Work;
use App\Models\Notification;
use App\Models\ArtistProfile;
use App\Events\VerifyIDEvent;

use App\Models\Balance;


class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function showOffersByJob($id)
    {
        $offers = Offer::where('work_id', $id)->get();
        return response()->json(['Offers' => $offers]);
    }

    public function showOffersByClient($id)
    {
        $client_id = Auth::user()->id;

        $offers = Offer::where('work_id', $id)->where(
            'client_id',
            $client_id
        )->get();
        $getAristProfile = [];
        foreach ($offers as $offer) {
            $artistProfile = User::where('id', $offer->artist_id)->first();
            $getAristProfile[] = [
                'offer' => $offer,
                'artistProfile' => $artistProfile
            ];
        }

        return response()->json(['Offers' => $getAristProfile]);
    }

    public function showOffersByArtist()
    {
        $artist_id = Auth::user()->id;
        $ClientProfile = [];

        $artistProfile = ArtistProfile::where('user_id', $artist_id)->first();
        if($artistProfile->manager_id !== null){
            return response()->json(['message' => 'You can not access offers! Please contact manager'], 400);
        }

        $offers = Offer::where('artist_id', $artist_id)->get();
        foreach ($offers as $offer) {
            $clientProfile = User::where('id', $offer->client_id)->first();
            $job = Work::where('id', $offer->work_id)->first();
            $ClientProfile[] = [
                'offer' => $offer,
                'job' => $job->title,
                'clientProfile' => $clientProfile
            ];
        }
        return response()->json(['Offers' => $ClientProfile]);
    }
    
    public function showOffersByManager(){
        $manager_id = Auth::user()->id;
        $ClientProfile = [];

        $artistProfiles = ArtistProfile::where('manager_id', $manager_id)->get();
        foreach($artistProfiles as $artistProfile){
            $offers = Offer::where('artist_id', $artistProfile->user_id)->get();
            foreach ($offers as $offer) {
                Log::info($offer);
                $clientProfile = User::where('id', $offer->client_id)->first();
                $job = Work::where('id', $offer->work_id)->first();
                $ClientProfile[] = [
                    'offer' => $offer,
                    'job' => $job->title,
                    'clientProfile' => $clientProfile
                ];
            }
        }
        return response()->json(['offers' => $ClientProfile]);

    }

    public function index()
    {
        //

        return response()->json('Hello world');

        // $offers = Offer::all();
        // return response()->json(['Offers' => $offers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $client_id = Auth::user()->id;
        // Log::info($client_id);
        
        $validation = Validator::make($request->all(), [
            'work_id' => 'required|exists:works,id',
            'artist_id'     => 'required|exists:users,id',
            'price' => 'required|numeric',
        ]);
        
        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 401);
        }
 
        
        try {
            
            $offerAlreadyExists = Offer::where('work_id', $request->work_id)->where("artist_id", $request->artist_id)->first();
            if($offerAlreadyExists){

                return response()->json(['message' => "You can't send more than one offer per job"],409);
            }
            $balance = Balance::where('user_id', $client_id)->first();
            // Log::info($balance);
            if($balance->balance < $request->price ){
                return response()->json(['message' => 'Insufficient Balance'],400);
            }

            $balance->balance = $balance->balance -  $request->price; 
            $balance->onhold_balance = $balance->onhold_balance +  $request->price;
            $balance->save();
            $offerPointRequired = $request->price * 0.01;
            // $client_id = Auth::user()->id;
            $artistProfile = ArtistProfile::where('user_id', $request->artist_id)->first();
           
                if($artistProfile->offfer_point < $offerPointRequired){
                    return response()->json(['message' => 'Artist does not have enough offer points'], 400);
                }

                $offerDetails = Offer::create(
                    [
                        'work_id' => $request->work_id,
                        'client_id' => $client_id,
                        'artist_id' => $request->artist_id,
                        'status' => "pending",
                        'price' => $request->price,
                        'offer_point_required' => $offerPointRequired
    
                    ]
                );
    
    
                //create a notification and triigger the verifyID Event 
    
                $notification = Notification::create(
                    [
                        'user_id' => $request->artist_id,
                        'title' => 'New Offer',
                        'source_id' => $client_id,
                        'message' => 'You have a new offer',
                        'type' => 'system',
                        'status' => 'unread',
                
                    
                    ]
                );
    
                $notification->save();
    
    
                event(new VerifyIDEvent($notification));
            

            // else{
            //     $manager_id = $artistProfile->manager_id;
            //     // $managerProfile = User::where('id', $manager_id)->first();
            //     // if($managerProfile->offfer_point < $offerPointRequired){
            //     //     return response()->json(['message' => 'Manager does not have enough offer points'], 400);
            //     // }
            //     $offerDetails = Offer::create(
            //         [
            //             'work_id' => $request->work_id,
            //             'client_id' => $client_id,
            //             'artist_id' => $manager_id,
            //             'status' => "pending",
            //             'price' => $request->price,
            //             'offer_point_required' => $offerPointRequired
    
            //         ]
            //     );
    
    
            //     //create a notification and triigger the verifyID Event 
    
            //     $notification = Notification::create(
            //         [
            //             'user_id' => $manager_id,
            //             'title' => 'New Offer',
            //             'source_id' => $client_id,
            //             'message' => 'You have a new offer',
            //             'type' => 'offer',
            //             'status' => 'unread',
                
                    
            //         ]
            //     );
    
            //     $notification->save();
    
    
            //     event(new VerifyIDEvent($notification));


            // }
        

            return response()->json(['message' => 'Offer created successfully', 'Offer Details' => $offerDetails]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Offer Details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //

        $offer = Offer::findOrFail($id);

        return response()->json(['Offer Details' => $offer]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        //


        $validation = Validator::make($request->all(), [

            'work_id' => 'required|exists:works,id',
            'client_id' => 'required|exists:users,id',
            'artist_id' => 'required|exists:users,id',
            'status' => 'string|required',
            'price' => 'required|numeric',
        ]);

        if ($validation->fails()) {

            return response()->json(['error' => $validation->errors()], 401);
        }

        try {

            $offer->update($request->all());

            return response()->json(['message' => 'Offer updated successfully', 'Offer Details' => $offer]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating Offer Details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        $offer = Offer::findOrFail($id);

        try {

            if($offer->status ==="pending"){

                $offer->delete();
    
                return response()->json(['message' => 'Offer deleted successfully']);
            }else{
                return response()->json(['message' => 'You can not delete an offer that has been accepted or rejected'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting Offer: ' . $e->getMessage()], 500);
        }
    }

    public function acceptOffer($id,$status){

        $artist_id = Auth::user()->id;

        $artistProfile = null;
        if(Auth::user()->role == "manager"){
            
            $artistProfile = ArtistProfile::where('manager_id', $artist_id)->first();
            
        }
        
        else{
            
            $artistProfile = ArtistProfile::where("user_id", $artist_id)->first();
        }
        
        
        $offer = Offer::where('id', $id)->where('artist_id', $artistProfile->user_id)
        ->first();
 
        if($artistProfile->offfer_point < $offer->offer_point_required){
            return response()->json(['message' => 'You do not have enough offer points'], 400);
        }
        $artistProfile->offfer_point = $artistProfile->offfer_point - $offer->offer_point_required;
        $artistProfile->save();

        $job = Work::where('id', $offer->work_id)->first();
        $job->status = "started";
        $job->save();

        $offer->status = $status;

        $offer->save();


        if($status === "rejected"){
            $balance = Balance::where('user_id', $offer->client_id)->first();
            Log::info( $offer->price );


            $newbalance = $balance->balance +  $offer->price;
            $balance->onhold_balance = $balance->onhold_balance - $offer->price;
            Log::info($balance->balance);
            // Log::info($balance->onhold_balance);
$balance->balance = $newbalance;
            $balance->save();
        
            
        }

        $notification = Notification::create(
            [
                'user_id' => $offer->client_id,
                'title' => 'New Offer',
                'source_id' => $artist_id,
                'message' => 'Offer has been accepted',
                'type' => 'system',
                'status' => 'unread',
        
            
            ]
        );

        $notification->save();

        try {
            //code...
            event(new VerifyIDEvent($notification));
        } catch (\Throwable $th) {
            //throw $th;
        }

    

        return response()->json(['message'=>'offer accepted'], 200);


    }


        public function jobIsOver($id){
            $client_id = Auth::user()->id;
            $offer = Offer::where('id', $id)->where('client_id', $client_id)
            ->first();
     
            $job = Work::where('id', $offer->work_id)->first();
        
            if($offer->status === "accepted"){
                
                $clientBalance = Balance::where('user_id', $client_id)->first(); 
                $artistBalance = Balance::where('user_id', $offer->artist_id)->first(); 

                            $our_amount = $offer->price * 0.1;

            $after_tax =   $our_amount * 0.35;

            $net_amount = $our_amount - $after_tax;

    

                $clientBalance->onhold_balance = $clientBalance->onhold_balance -$offer->price;
                $clientBalance->save();
                
                $artistBalance->balance =  $artistBalance->balance + ($offer->price - $our_amount);
                $artistBalance->save();
                
                $mainTransaction = MainTransaction::create(
                    [
    
                        'client_id' => $client_id,
                        'artist_id' => $offer->artist_id,
                        'full_amount'=> $offer->price,
                        'our_amount' => $our_amount,
                        'after_tax' => $after_tax,
                        'net_amount' => $net_amount,
                        'percentage' => 0.1,
                        "tax_percentage" => 0.35
                    ]
                    );

                    $uuid = $client_id . Str::uuid();

                    $txn_detail = TxnHistory::create(
                        [
                            'tx_ref' =>  $uuid,
                            'amount' => $offer->price,
                            'charge' => $our_amount,
                            'from' => $client_id,
                            'to' => $offer->artist_id,
                            'reason' => 'Offer',
                            'type' => 'payment'
                        ]
                    );

                    $job->status = "completed";
                    $job->save();

                    $offer->status = "completed";
                    $offer->save();

                    $notification = Notification::create(
                        [
                            'user_id' => $offer->artist_id,
                            'title' => 'Payment For Job',
                            'source_id' => $client_id,
                            'message' => 'You Have Successfully Completed The Job',
                            'type' => 'offer',
                            'status' => 'unread',
                    
                        
                        ]
                    );
        
                    $notification->save();
        
        
                    event(new VerifyIDEvent($notification));


                    return response()->json([
                        "message" => "success"
                    ],200);
            }

            return response()->json([
                "error"=> "Job Already Completed or not accepted"
            ],401);
    
        }
    


}