<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'wallet_id',
        'balance'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // public function walletHistory() {
    //     return $this->hasMany(WalletHistory::class, 'wallet_id');
    // }
}
