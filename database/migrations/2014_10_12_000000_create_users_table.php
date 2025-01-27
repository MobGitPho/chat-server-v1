<?php

use App\Enums\AccountStatus;
use App\Enums\AuthType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('avatar')->nullable();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('profile_type')->nullable();
            $table->unsignedInteger('profile_id')->nullable();
            $table->integer('account_status')->default(AccountStatus::DISABLED->value);
            $table->json('preferences')->nullable();
            $table->integer('auth_type')->default(AuthType::UNDEFINED->value);
            $table->rememberToken();
            $table->timestamp('last_logged_in_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
