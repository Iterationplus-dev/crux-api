<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('cruxId')->nullable();
            $table->string('username')->nullable();
            $table->longText('placed_left')->nullable();
            $table->bigInteger('positioned_left')->nullable();
            $table->longText('placed_right')->nullable();
            $table->bigInteger('positioned_right')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->text('contacts')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->text('about')->nullable();
            $table->string('ver_code')->nullable();
            $table->timestamp('ver_code_send_at')->nullable();
            $table->double('balance')->default(0);
            $table->text('kyc_data')->nullable();
            $table->enum('sv',['0','1'])->default('0')->comment('0=unverified, 1= verified');
            $table->enum('ev',['0','1'])->default('0')->comment('0=unverified, 1= verified');
            $table->enum('tf',['0','1'])->default('0')->comment('0=unverified, 1= verified');
            $table->enum('kyc',['0','1','2'])->default('0')->comment('0=unverified, 1= verified, 2=pending');
            $table->string('outletId')->nullable();
            $table->text('outlet_details')->nullable();
            $table->text('account_details')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('tsc')->nullable();
            $table->string('ban_reason')->nullable();
            $table->enum('role',['admin','outlet','member','therapist','patient'])->default('member');
            $table->enum('status',['0','1','2'])->default('0')->comment('0=inactive, 1= active, 2=suspended');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
