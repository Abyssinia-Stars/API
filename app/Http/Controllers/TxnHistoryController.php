<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\TxnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TxnHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $user_id = Auth::user()->id;
        $txn_histories = TxnHistory::where('to', $user_id)->orWhere('from', $user_id)->orderBy('created_at', "DESC")->paginate($limit, ['*'], 'page', $page);
       
        //change the user_id to and from from a number to a name 
        foreach ($txn_histories as $txn_history) {
            $txn_history->to = Auth::user()->where('id', $txn_history->to)->first()->name;
            $txn_history->from = Auth::user()->where('id', $txn_history->from)->first()->name;
        }
        


      if ($txn_histories->isEmpty()) {
            return response()->json([
                'message' => 'No transaction history found'
            ], 404);
        }
        return response()->json([
            'txn_histories' => $txn_histories
        ],200);
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
    public function show(TxnHistory $txnHistory)
    {
        //
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TxnHistory $txnHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TxnHistory $txnHistory)
    {
        //
    }
}
