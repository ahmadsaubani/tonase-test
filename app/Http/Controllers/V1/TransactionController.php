<?php

namespace App\Http\Controllers\V1;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

    public function getReport()
    {
        $user = Auth::user();
        $qb = DB::table("transactions as tr")->distinct()
            ->select(
                "usr.name as userWallet",
                "tr.debit",
                DB::raw("(CASE WHEN tr.debit > 0 THEN 'DB' ELSE 'CR' END) as indentical"),
                "tr.credit",
                "tr.description as transaction_description",
                "tm.title as methodTitle",
                "tr.created_at as date"
            )
            ->leftJoin("users as usr", "tr.user_id", "=", "usr.id")
            ->leftJoin("transaction_methods as tm", "tr.transaction_method_id", "=", "tm.id")
            ->where("tr.user_id", $user->id)
            ;

        $result = $qb->get();

        return response()->json([
            "success"   => true,
            "message"   => "data found",
            "data"      => $result
        ]);
    }
} 

