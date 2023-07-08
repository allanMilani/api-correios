<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_correios', function(Blueprint $table){
            $table->id();
            $table->string('user')->nullable();
            $table->string('correios_id')->nullable();
            $table->string('cnpj')->unique();
            $table->timestamp('emissao')->nullable();
            $table->timestamp('expira_em')->nullable();
            $table->text('token')->nullable();
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
        Schema::dropIfExists('token_correios');
    }
};
