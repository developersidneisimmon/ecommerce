<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {          
            $table->increments('id')->unsigned();
            $table->string('external_id', 20)->nullable(true);
            $table->string('type', 200)->nullable(false);
            $table->string('country', 100)->nullable(false);
            $table->string('document_type', 4)->nullable(false);
            $table->string('document_number', 20)->nullable(false)->unique();  
            $table->string('name', 200)->nullable(false);
            $table->string('email', 200)->nullable(false)->unique();    
            $table->string('password', 60)->nullable(false);  
            $table->datetime('birthday')->nullable(true);
            $table->string('gender')->nullable(true);
            $table->string('status')->nullable(false)->default('A');
            $table->timestamps();
            $table->timestampsTz('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer');
    }
}
