<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function showJobsByClient($id)
    {

        $client_id = Auth::user()->id;



        try {
            $job = Job::where('client_id', $client_id)->where('id', $id)->first();

            return response()->json(['message' => 'Job Found', 'Job Details' => $job]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
        }
    }


    public function index()
    {


        $id = Auth::user()->id;

        $jobs = Job::where('client_id', $id)->get();
        return response()->json( $jobs);
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




        $validation = Validator::make($request->all(), [

                     'title'=>'required|string',
                'client_id'=> 'required|exists:users,id',
                'catagory'=>'string|required|max:255',
                'description'=>'string|required',
                'status'=>'string|required',
                'from_date'=>'date|required',
                'to_date'=>'date|required',
            ]);

        if ($validation->fails()) {


            return response()->json(['error' => $validation->errors()], 400);
        }

        $validatedData = $request->all();





        try {


            // Create the ArtistProfile with the validated data
            $jobDetails = Job::create(
                [
                    'title'=>$validatedData['title'],
                    'client_id' => $validatedData['client_id'],
                    'catagory' => $validatedData['catagory'],
                    'description' => $validatedData['description'],
                    'status' => $validatedData['status'],
                    'from_date' => $validatedData['from_date'],
                    'to_date' => $validatedData['to_date'],

                ]
            );





            return response()->json(['message' => 'Job created successfully', 'Job Details' => $jobDetails]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
            }


    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {



       $client_id= Auth::user()->id;



        try{
            $job = Job::where('client_id', $client_id)->where('id', $id)->first();

             return response()->json( $job);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating Job Details: ' . $e->getMessage()], 500);
            }



    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job $job)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {

        $validation = Validator::make($request->all(), [

            'title'=>'required|string',
            'client_id'=> 'required|exists:users,id',
            'catagory'=>'string|required|max:255',
            'description'=>'string|required',
            'status'=>'string|required',
            'from_date'=>'date|required',
            'to_date'=>'date|required',
        ]);

        if ($validation->fails()) {



            return response()->json(['error' => $validation->errors()], 400);
        }

        $validatedData = $request->all();





        try {


            // Create the ArtistProfile with the validated data
            $jobDetails = Job::where('id', $job->id)->update(
                [
                    'title'=>$validatedData['title'],
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
    public function destroy(Job $job)
    {
        //

        try {
            $job->delete();
            return response()->json(['message' => 'Job Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error Deleting Job Details: ' . $e->getMessage()], 500);
        }
    }
}