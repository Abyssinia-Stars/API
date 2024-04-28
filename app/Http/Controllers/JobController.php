<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;


class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showJobsByClient($id)
    {
        $client_id = Auth::user()->id;

        try {
            $job = Work::where('client_id', $client_id)->where('id', $id)->first();

            return response()->json(['message' => 'Job Found', 'Job Details' => $job]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
        }
    }


    public function index()
    {
        $id = Auth::user()->id;

        $jobs = Work::where('client_id', $id)->get();
        // $offerCount = Offer::where('work_id', $jobs->id)->count();
        $jobsWithOfferCount = [];

        foreach ($jobs as $job) {
            $offerCount = Offer::where('work_id', $job->id)->count();
            $jobsWithOfferCount[] = [
                'job' => $job,
                'offerCount' => $offerCount
            ];
        }

        return response()->json($jobsWithOfferCount);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required|string',
            'client_id' => 'required|exists:users,id',
            'catagory' => 'string|required|max:255',
            'description' => 'string|required',
            'status' => 'string|required',
            'from_date' => 'string|required',
            'to_date' => 'string|required',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $validatedData = $request->all();

        try {
            // Create the ArtistProfile with the validated data
            $jobDetails =Work::create(
                [
                    'title' => $validatedData['title'],
                    'client_id' => $validatedData['client_id'],
                    'catagory' => $validatedData['catagory'],
                    'description' => $validatedData['description'],
                    'status' => $validatedData['status'],
                    'from_date' => $validatedData['from_date'],
                    'to_date' => $validatedData['to_date'],

                ]
            );

            return response()->json(['message' => 'Job created successfully', 'Job Details' => $jobDetails], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $client_id = Auth::user()->id;

        try {
            $job = Work::where('client_id', $client_id)->where('id', $id)->first();
            return response()->json($job);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {

        $validation = Validator::make($request->all(), [
            'title' => 'required|string',
            'client_id' => 'required|exists:users,id',
            'catagory' => 'string|required|max:255',
            'description' => 'string|required',
            'status' => 'string|required',
            'from_date' => 'date|required',
            'to_date' => 'date|required',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $validatedData = $request->all();

        try {
            // Create the ArtistProfile with the validated data
            $jobDetails =Work::where('id', $job->id)->update(
                [
                    'title' => $validatedData['title'],
                    'client_id' => $validatedData['client_id'],
                    'catagory' => $validatedData['catagory'],
                    'description' => $validatedData['description'],
                    'status' => $validatedData['status'],
                    'from_date' => $validatedData['from_date'],
                    'to_date' => $validatedData['to_date'],
                ]
            );

            // Update the user's profile picture if provided in the request
            return response()->json(['message' => 'Job Updated successfully', 'Job Details' => $jobDetails]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error Updating Job Details: ' . $e->getMessage()], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Work $job,$id)
    {
        try {
            $job = Work::where('id', $id)->first();
    
            $job->delete();
            return response()->json(['message' => 'Job Deleted successfully'],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error Deleting Job Details: ' . $e->getMessage()], 500);
        }
    }

    public function getJob($id)
    {
        $user = Auth::user();
        $job = Work::where('id', $id)->
        where('client_id', $user->id)->
        first();
        return response()->json($job);
    }
    
}