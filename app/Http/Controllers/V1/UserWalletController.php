<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Transformers\UserWallet\UserWalletTransformer;
use Exception;
use Illuminate\Support\Facades\DB;

class UserWalletController extends Controller
{
    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function topUpBalance(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            if (empty($user->wallet)) {
                return $this->errorResponse("sorry you dont have a user wallet", 400);
            }

            $validator = validator($request->all(), [ 
                'balance'               => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $wallet             = $user->wallet;
            $wallet->balance    = $wallet->balance + $request->balance;
            $wallet->save();

            $this->transaction->topUpTransaction($wallet, $request->balance, $request->description);

            $result = $this->item($wallet, new UserWalletTransformer(), 'user');
            DB::commit();
            return $this->showResultV2('data created', $result);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->realErrorResponse($e);
        }
    }

    public function getSaldoBalance()
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            if (empty($user->wallet)) {
                return $this->errorResponse("sorry you dont have a user wallet", 400);
            }

            $result = $this->item($user->wallet, new UserWalletTransformer(), 'user');
            DB::commit();
            return $this->showResultV2('data created', $result);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->realErrorResponse($e);
        }
    }

    public function withDrawBalance(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();

            $validator = validator($request->all(), [ 
                'balance'               => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $wallet = $user->wallet;
            $total = $wallet->balance - $request->balance;

            if (empty($wallet)) {
                return $this->errorResponse("sorry you dont have a user wallet", 400);
            }

            if ($wallet->balance < 0 || $total < 0) {
                return $this->errorResponse("your account balance " . $user->wallet->balance . " for using with-draw atleast you must have account balance greater than 1", 400);
            }
            
            $wallet->balance    = $total;
            $wallet->save();

            $this->transaction->withDrawTransaction($wallet, $request->balance, $request->description);

            $result = $this->item($wallet, new UserWalletTransformer(), 'user');
            DB::commit();
            return $this->showResultV2('success with-draw', $result);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->realErrorResponse($e);
        }
    }

    public function transferBalance(Request $request, $walletId)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $validator = validator($request->all(), [ 
                'amount' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            $wallet = $user->wallet;

            if (empty($wallet)) {
                return $this->errorResponse("sorry you dont have a account wallet", 400);
            }

            $total = $wallet->balance - $request->amount;

            if ($wallet->balance < 0 || $total < 0) {
                return $this->errorResponse("your account balance " . $user->wallet->balance . " for using with-draw atleast you must have account balance greater than 1", 400);
            }

            if ($wallet->wallet_id === $walletId) {
                return $this->errorResponse("can't transfer to same account wallet.", 400);
            }

            $wallet->balance    = $total;
            $wallet->save();

            $toWallet = UserWallet::whereWalletId($walletId)->first();
        
            if (empty($toWallet)) {
                return $this->errorResponse("account wallet not found", 400);
            }
            
            $this->transaction->transfer($wallet, $toWallet, $request->amount, $request->description);

            $toWallet->balance = $toWallet->balance + $request->amount;
            $toWallet->save();

            $result = $this->item($toWallet, new UserWalletTransformer(), 'user');
            DB::commit();
            return $this->showResultV2('success transfer', $result);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->realErrorResponse($e);
        }
    }
} 

