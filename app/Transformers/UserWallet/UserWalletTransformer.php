<?php

namespace App\Transformers\UserWallet;

use App\Models\UserWallet;
use App\Transformers\UserTransformer;
use League\Fractal\TransformerAbstract;

class UserWalletTransformer extends TransformerAbstract
{
    public $type = 'wallet';
    
    protected $availableIncludes = ['user'];

    public function transform(UserWallet $wallet)
    {
        return [
            "id"                => $wallet->id,
            "wallet_id"         => $wallet->wallet_id,
            "balance"           => (int) $wallet->balance,
        ];
    }

    public function includeUser(UserWallet $wallet)
    {
        $user = $wallet->user;
        
        if (! empty($user)) {
            return $this->item($user, new UserTransformer(), 'user');
        }
    }
}