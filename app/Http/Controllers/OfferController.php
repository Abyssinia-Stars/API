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

    public function showOffersByClient()
    {
        $client_id = Auth::user()->id;

        $offers = Offer::where('client_id', $client_id)->get();
        return response()->json(['Offers' => $offers]);
    }

    public function showOffersByArtist()
    {
        $artist_id = Auth::user()->id;

        $offers = Offer::where('artist_id', $artist_id)->get();
        return response()->json(['Offers' => $offers]);
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
        
        // $offer = Offer::create($request->all());
        
        
        try {
            
            $balance = Balance::where('user_id', $client_id)->first();
            // Log::info($balance);
            if($balance->balance < $request->price ){
                return response()->json(['message' => 'Insufficient Balance']);
            }


            // Create the ArtistProfile with the validated data
            // $our_amount = $request->price * 0.1;

            // $after_tax =   $our_amount * 0.35;

            // $net_amount = $our_amount - $after_tax;

            // $mainTransaction = MainTransaction::create(
            //     [

            //         'client_id' => $client_id,
            //         'artist_id' => $request->artist_id,
            //         'full_amount'=> $request->price,
            //         'our_amount' => $our_amount,
            //         'after_tax' => $after_tax,
            //         'net_amount' => $net_amount,
            //         'percentage' => 0.1,
            //         "tax_percentage" => 0.35
            //     ]
            //     );

                // $artist_amount = $request->price - $our_amount;

            $balance->balance = $balance->balance -  $request->price; 
            $balance->onhold_balance = $balance->onhold_balance +  $request->price;
            $balance->save();
            // $client_id = Auth::user()->id;
            $offerDetails = Offer::create(
                [
                    'work_id' => $request->work_id,
                    'client_id' => $client_id,
                    'artist_id' => $request->artist_id,
                    'status' => "pending",
                    'price' => $request->price,

                ]
            );

        // $uuid = $client_id . Str::uuid();

        // $txn_detail = TxnHistory::create(
        //     [
        //         'tx_ref' =>  $uuid,
        //         'amount' => $request->price,
        //         'charge' => $our_amount,
        //         'from' => $client_id,
        //         'to' => $request->artist_id,
        //         'reason' => 'Offer',
        //         'type' => 'payment'
        //     ]
        // );






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

            $offer->delete();

            return response()->json(['message' => 'Offer deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting Offer: ' . $e->getMessage()], 500);
        }
    }

    public function acceptOffer($id,$status){

        $artist_id = Auth::user()->id;

        $offer = Offer::where('id', $id)->where('artist_id', $artist_id)
        ->first();

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

    

        return response()->json(['message'=>'offer accepted'], 200);


    }


        public function jobIsOver($id){
            $client_id = Auth::user()->id;
    
            $offer = Offer::where('id', $id)->where('client_id', $client_id)
            ->first();
    
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

                    return response()->json([
                        "message" => "success"
                    ],200);
            }

            return response()->json([
                "error"=>"error"
            ],401);
    
        }
    


}