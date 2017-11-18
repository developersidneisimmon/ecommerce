<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomerAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         /*
        street String Obrigatório. Rua
        customer_id - referencia do cliente
        type -  billing (dados de cobrança) ou shipping (dados de envio)
        street_number String Obrigatório. Número
        zipcode String Obrigatório. CEP. Para endereço brasileiro, deve conter uma numeração de 8 dígitos
        country String Obrigatório. País. Duas letras minúsculas. Deve seguir o padão ISO 3166-1 alpha-2
        state String Obrigatório. Estado
        city String Obrigatório. Cidade
        neighborhood String Bairro 
        complementary String Complemento
        */
        Schema::create('customer_address', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade')->onUpdate('cascade');
            $table->string('type', 10)->nullable(false);
            $table->string('street', 200)->nullable(false);
            $table->integer('street_number')->nullable(false);
            $table->integer('zipcode')->nullable(false);
            $table->string('country', 2)->nullable(false)->default('br');
            $table->string('state', 2)->nullable(false);  
            $table->string('city', 100)->nullable(false);
            $table->string('neighborhood', 100)->nullable(false);
            $table->string('complementary', 100)->nullable(false);            
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
        Schema::dropIfExists('customer_address');
    }
}
