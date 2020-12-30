<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $user;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'description',
        'user_ip'
    ];

    public static function logging(\App\Models\User $user, $log = 1) 
    {
        switch ($log) {
            case $log === 1;
                $description = $user->name . ' success to login';
            break;
            case $log === 2;
                $description = $user->name . ' failed to login cause wrong password';
            break;
            case $log === 3;
                $description = $user->name . ' logout success';
            break;
        }
        
        return LogActivity::insert([
            "user_id"       => $user->id,
            "description"   => $description,
            "user_ip"       => request()->ip()
        ]);
    }
}
