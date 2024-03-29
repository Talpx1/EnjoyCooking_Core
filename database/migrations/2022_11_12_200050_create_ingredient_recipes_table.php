<?php

use App\Models\Ingredient;
use App\Models\MeasureUnit;
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
        Schema::create('ingredient_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Recipe::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Ingredient::class)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedDecimal('quantity', places: 1)->nullable();
            $table->foreignIdFor(MeasureUnit::class)->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['recipe_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredient_recipe');
    }
};
