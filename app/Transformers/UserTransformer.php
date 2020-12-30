<?php

namespace App\Transformers;

use App\Models\User;
use App\Models\UserWallet;
use App\Transformers\Log\LogActivityTransformer;
use App\Transformers\UserWallet\UserWalletTransformer;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public $type = 'user';
    
    protected $availableIncludes = ['wallet', 'activities'];

    public function transform(User $model)
    {
        return [
            "id"    => $model->id,
            "name"  => $model->name,
            "email" => $model->email
        ];
    }

    public function includeActivities(User $model)
    {
        if (!empty($model->logActivities)) {
            return $this->collection($model->logActivities, new LogActivityTransformer(), 'activities');
        }
    }

    public function includeWallet(User $model)
    {
        $wallet = $model->wallet()->first();
        if (! empty($wallet)) {
            return $this->item($wallet, new UserWalletTransformer(), 'wallet');
        }
    }
}