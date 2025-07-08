<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['reclamo', 'queja']);
            $table->string('consumer_name');
            $table->string('consumer_email');
            $table->string('consumer_phone');
            $table->text('product_description');
            $table->text('complaint_detail');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamp('response_deadline');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
}; 