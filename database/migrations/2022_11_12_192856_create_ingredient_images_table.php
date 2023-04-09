<?php

use App\Enums\ModerationStatuses;
use App\Models\Ingredient;
use App\Models\ModerationStatus;
use App\Models\User;
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
        Schema::create('ingredient_images', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('thumbnail_path')->unique();
            $table->text('description')->nullable();
            $table->foreignIdFor(Ingredient::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(ModerationStatus::class)->default(ModerationStatuses::PENDING_MODERATION->value)->constrained()->restrictOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('ingredient_images');
    }
};
