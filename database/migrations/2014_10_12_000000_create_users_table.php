<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UserType;
use App\Models\Gender;
use App\Models\ProfessionGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('company_name')->nullable();
            $table->date('date_of_birth');
            $table->string('instagram_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('image_path')->nullable()->unique();
            $table->foreignIdFor(ProfessionGroup::class)->nullable()->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Gender::class)->nullable()->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(UserType::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
