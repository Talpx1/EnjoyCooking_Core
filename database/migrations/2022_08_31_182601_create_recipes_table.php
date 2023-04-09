<?php

use App\Enums\ModerationStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DifficultyLevel;
use App\Models\Recipe;
use App\Models\Course;
use Illuminate\Foundation\Auth\User;
use App\Models\Category;
use App\Models\ModerationStatus;
use App\Models\VisibilityStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('featured_image_path')->nullable()->unique();
            $table->string('featured_image_thumbnail_path')->nullable()->unique();
            $table->integer('baking_minutes')->nullable()->unsigned();
            $table->integer('preparation_minutes')->unsigned();
            $table->text('description')->nullable();
            $table->integer('share_count')->default(0)->unsigned();
            $table->foreignIdFor(DifficultyLevel::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Recipe::class, 'parent_recipe_id')->nullable()->constrained('recipes')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Course::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Category::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(ModerationStatus::class)->default(ModerationStatuses::PENDING_MODERATION->value)->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(VisibilityStatus::class)->constrained()->restrictOnDelete()->cascadeOnUpdate();

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
        Schema::dropIfExists('recipes');
    }
};
