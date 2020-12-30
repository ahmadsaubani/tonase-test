<?php

namespace App\Transformers\Log;

use App\Models\LogActivity;
use League\Fractal\TransformerAbstract;

class LogActivityTransformer extends TransformerAbstract
{
    public $type = 'activities';
    
    protected $availableIncludes = [];

    public function transform(LogActivity $log)
    {
        return [
            "id"                => $log->id,
            "description"       => $log->description,
            "user_ip"           => $log->user_ip,
            "created_at"        => $log->created_at,
        ];
    }
}