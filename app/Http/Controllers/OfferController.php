<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function showOffersByJob($id)
    {
        $offers = Offer::where('job_id', $id)->get();
        return response()->json(['Offers' => $offers]);
    }

    public function showOffersByClient($id)
    {
        $offers = Offer::where('client_id', $id)->get();
        return response()->json(['Offers' => $offers]);
    }

    public function showOffersByArtist($id)
    {
        $offers = Offer::where('artist_id', $id)->get();
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

        $validation = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'artist_id' => 'required|exists:users,id',
            'price' => 'required|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 401);
        }

        // $offer = Offer::create($request->all());


        try {


            // Create the ArtistProfile with the validated data
            $offerDetails = Offer::create(
                [
                    'job_id' => $request->job_id,
                    'client_id' => $client_id,
                    'artist_id' => $request->artist_id,
                    'status' => "pending",
                    'price' => $request->price,

                ]
            );





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

            'job_id' => 'required|exists:jobs,id',
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
}