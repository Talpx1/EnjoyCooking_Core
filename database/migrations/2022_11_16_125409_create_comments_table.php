<?php

use App\Models\Comment;
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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->text('body');
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Comment::class, 'parent_comment_id')->nullable()->constrained('comments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            // $table->unique(['commentable_id', 'commentable_type', 'body', 'user_id', 'parent_comment_id']); FIXME: impossible to create index because text field (body) cant be an index. Trying to set a really big varchar also dont work.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
