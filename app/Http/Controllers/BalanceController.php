<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\PaymentInfo;
use App\Models\TxnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Chapa\Chapa\Facades\Chapa as Chapa;

class BalanceController extends Controller
{
    protected $reference;

    public function __construct()
    {
        $this->reference = Chapa::generateReference();
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validated->errors()
            ], 400);
        }

        $reference = $this->reference;
        $user = Auth::user();
        $payment_info = PaymentInfo::where('user_id', $user->id)->first();

        if ($payment_info === null) {
            return response()->json([
                'message' => 'Please add payment information'
            ], 400);
        }

        // Enter the details of the payment
        $data = [
            'amount' => $request->input('amount'),
            'email' => $payment_info->email,
            'tx_ref' => $reference,
            'currency' => $payment_info->currency,
            'callback_url' => route('callback', [$reference, 'user_id' => $user->id]),
            'first_name' => $payment_info->first_name,
            'last_name' => $payment_info->last_name,
            "customization" => [
                "title" => 'Abysinia Stars',
                "description" => "Where talents shine"
            ]
        ];

        $payment = Chapa::initializePayment($data);
        return response()->json($payment, 200);

        if ($payment['status'] !== 'success') {
            return response()->json([
                'message' => 'Something went really bad'
            ], 500);
        }

        Log::info(json_encode($payment));
        return $payment['data'];
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback($reference, Request $request)
    {

        $data = Chapa::verifyTransaction($reference);

        
        $user_id = $request->input('user_id');
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln($data)
        // return response()->json($data, 200);    
        //if payment is successful
        Log::info(json_encode($data) . ' ' . $user_id);
        if ($data['status'] == 'success') {
            TxnHistory::create([
                'tx_ref' => $data['data']['tx_ref'],
                'amount' => $data['data']['amount'],
                'from' => $user_id,
                'to' => $user_id,
                'reason' => 'Adding funds to account',
                'type' => 'deposit'
            ]);

            $balance = Balance::where('user_id', $user_id)->firstOrFail();
            $balance->balance += $data['data']['amount'];
            $balance->save();
        } else {
            Log::info("Payment failed");
        }
    }

    public function withdraw(Request $request)
    {
    }

    public function getBalance()
    {
        $user_id = Auth::user()->id;
        $balance = Balance::where("user_id", $user_id)->first();

        return response()->json([
            'balance' => $balance], 200);
    }

}
