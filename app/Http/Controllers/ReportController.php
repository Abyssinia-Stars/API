<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Events\VerifyIdEvent;


class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $validation = Validator::make($request->all(),[
            'user_id' => 'required|exists:users,id',
            'reporter_id' => 'required|exists:users,id',
            'report_reason' => 'required|string',
        ]);

        if($validation->fails()){
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 400);
        }

        $report = Report::create($request->all());

        return response()->json([
            'message' => 'Report created successfully',
            'report' => $report
        ], 201);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
        $user = Auth::user();
        if($user->role !== 'admin'){
            return response()->json([
                'message' => 'You are not authorized to view this report'
            ], 401);
        }

        $reports = Report::all();
        $reportWithUserDetail = [];

        foreach($reports as $report){
            $report->user_id = User::where('id', $report->user_id)->select("id", "name")->first();
            $report->reporter_id = User::where('id', $report->reporter_id)->select("id", "name")->first();
        }

        return response()->json([
            'message' => 'Reports fetched successfully',
            'reports' => $reports
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }

    public function reportReviewed($id){

        $user = Auth::user();
        if($user->role !== 'admin'){
            return response()->json([
                'message' => 'You are not authorized to view this report'
            ], 401);
        }

        $report = Report::where('id', $id)->first();
        
        $report->report_status = 'completed';
        $report->save();
        
     
        $notification = new Notification([
            'user_id' => $report->reporter_id,
            'notification_type' => 'system',
            'source_id' => 1,
            'message' =>'Your report has Report Reviewed, A Message will be sent to you soon concerining the issues you reported',
            'title' => 'Report Confirmation ',
            'status' => 'unread'
        ]);

        $notification->save();
        broadcast(new VerifyIdEvent($notification));

    


    }
}
