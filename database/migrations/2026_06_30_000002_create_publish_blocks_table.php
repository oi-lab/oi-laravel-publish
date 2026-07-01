<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('publish_page_id')->constrained('publish_pages')->cascadeOnDelete();
            $table->string('template_key');
            $table->string('name');
            $table->string('key');
            $table->string('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->json('props')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['publish_page_id', 'key']);
            $table->index('template_key');
            $table->index(['publish_page_id', 'sort']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_blocks');
    }
};
