<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger("from_user_wallet_id")->nullable();
            $table->foreign('from_user_wallet_id')->references('id')->on('user_wallets')->onDelete('cascade');
            $table->unsignedBigInteger("to_user_wallet_id")->nullable();
            $table->foreign('to_user_wallet_id')->references('id')->on('user_wallets')->onDelete('cascade');
            $table->unsignedBigInteger("transaction_method_id");
            $table->foreign('transaction_method_id')->references('id')->on('transaction_methods')->onDelete('cascade');
            $table->unsignedDecimal('debit', 16,2)->nullable();
            $table->unsignedDecimal('credit', 16,2)->nullable();
            $table->string("description")->nullable();
            $table->timestamp("created_at")->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
