<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->float('value',12,2);
            $table->enum('status',['failed','success','waiting'])->default('waiting');
            $table->enum('type',['withdraw','put']);
            $table->integer('vallet_from');
            $table->integer('vallet_to');
            $table->integer('pocket_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_transactions');
    }
}
