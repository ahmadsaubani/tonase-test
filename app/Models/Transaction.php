<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'from_user_wallet_id',
        'to_user_wallet_id',
        'transaction_method_id',
        'debit',   // uang keluar 
        'credit', // uang masuk
        'description'
    ];


    public function transactionMethod() {
        return $this->belongsTo(TransactionMethod::class);
    }

    public function topUpTransaction($wallet, $credit, $description)
    {
        $transactionMethod = TransactionMethod::whereTitle("TOPUP")->first();

        return Transaction::insert([
            "user_id"               => $wallet->user_id,
            "to_user_wallet_id"     => $wallet->id,
            "transaction_method_id" => $transactionMethod->id,
            "description"           => $description,
            "credit"                => $credit
        ]);
    }

    public function withDrawTransaction($wallet, $debit, $description)
    {
        $transactionMethod = TransactionMethod::whereTitle("WITHDRAW")->first();

        return Transaction::insert([
            "user_id"               => $wallet->user_id,
            "from_user_wallet_id"   => $wallet->id,
            "transaction_method_id" => $transactionMethod->id,
            "description"           => $description,
            "debit"                 => $debit
        ]);
    }

    public function transfer($fromWallet, $toWallet, $amount, $description)
    {
        $transactionMethod = TransactionMethod::whereTitle("TRANSFER")->first();
        
        Transaction::insert([
            "user_id"               => $toWallet->user_id,
            "from_user_wallet_id"   => $fromWallet->id,
            "to_user_wallet_id"     => $toWallet->id,
            "transaction_method_id" => $transactionMethod->id,
            "description"           => $description,
            "credit"                => $amount
        ]);
        
        
        $this->transferTransaction($fromWallet, $toWallet, $transactionMethod, $amount, $description);
    }

    public function transferTransaction($fromWallet, $toWallet, $transactionMethod, $debit, $description)
    {

        return Transaction::insert([
            "user_id"               => $fromWallet->user_id,
            "from_user_wallet_id"   => $fromWallet->id,
            "to_user_wallet_id"     => $toWallet->id,
            "transaction_method_id" => $transactionMethod->id,
            "description"           => $description,
            "debit"                 => $debit
        ]);
    }
}
