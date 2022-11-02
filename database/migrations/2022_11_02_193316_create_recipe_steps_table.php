<?php

use App\Models\Recipe;
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
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->unique()->nullable();
            $table->string('thumbnail_path')->unique()->nullable();
            $table->text('description');
            $table->foreignIdFor(Recipe::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['recipe_id', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe_steps');
    }
};
