<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\PaymentInfo;
use App\Models\TxnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'amount' => 'required|numeric|min:0',
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
        
            'amount' => $request->input('amount') / 0.965, // 3.5% charge
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
        $charge = $data['data']['charge'];
        $amount = $data['data']['amount'] - $charge;
        if ($data['status'] == 'success') {
            TxnHistory::create([
                'tx_ref' => $data['data']['tx_ref'],
                'amount' => $amount,
                'charge' => $charge,
                'from' => $user_id,
                'to' => $user_id,
                'reason' => 'Adding funds to account',
                'type' => 'deposit'
            ]);

            $balance = Balance::where('user_id', $user_id)->firstOrFail();
            $balance->balance += $amount;
            $balance->save();
        } else {
            Log::info("Payment failed");
        }
    }

    public function withdraw(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validated->errors()
            ], 400);
        }

        $reference = $this->reference;
        $user = Auth::user();
        $user_id = $user->id;
        $balance = Balance::where("user_id", $user_id)->first();
        $amount = $request->input('amount') / 0.965; // 3.5% charge

        if ($balance->balance < $amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $payment_info = PaymentInfo::where('user_id', $user_id)->first();

        if ($payment_info === null) {
            return response()->json([
                'message' => 'Please add payment information'
            ], 400);
        }

        // Enter the details of the payment
        $data = [
            'amount' => $amount,
            'currency' => $payment_info->currency,
            "account_name" => $payment_info->account_name,
            "account_number" => $payment_info->account_number,
            "reference" => $reference,
            "bank_code" => $payment_info->bank_code,
        ];
        Log::info($data);

        $secret = env('CHAPA_SECRET_KEY');
        $res = Http::withToken($secret)->post('https://api.chapa.co/v1/transfers', $data);
        $charge = $amount * 0.035;
        $amount_charged = $amount - $charge;
        if ($res['status'] == 'success') {
            TxnHistory::create([
                'tx_ref' => $res['data'],
                'amount' => $amount_charged,
                'charge' => $charge,
                'from' => $user_id,
                'to' => $user_id,
                'reason' => 'Withdraw to account',
                'type' => 'withdrawal'
            ]);

            $balance->balance -= $amount;
            $balance->save();
            return response()->json(['message' => 'Your withdrawal is successfull']);
        } else {
            Log::info("Payment failed");
        }
    }

    public function getBalance()
    {
        $user_id = Auth::user()->id;
        $balance = Balance::where("user_id", $user_id)->first();

        return response()->json([
            'balance' => $balance], 200);
    }

    public function getBanks()
    {
        $secret = env('CHAPA_SECRET_KEY');
        $res = Http::withToken($secret)->get('https://api.chapa.co/v1/banks');
        return response()->json($res->json(), $res->status());
    }

    

}
