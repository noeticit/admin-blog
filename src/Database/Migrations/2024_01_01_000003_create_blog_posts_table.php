<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('blog.database.table_prefix');
        $authorTable = config('blog.author.table', 'users');

        Schema::create($prefix.'posts', function (Blueprint $table) use ($prefix, $authorTable) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->json('body_blocks')->nullable();
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('updated_content_at')->nullable();
            $table->string('source_url')->nullable();

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->json('schema_markup')->nullable();

            // Relationships
            $table->foreignId('category_id')->nullable()->constrained($prefix.'categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained($authorTable)->cascadeOnDelete();

            // Analytics
            $table->integer('reading_time')->nullable();
            $table->integer('word_count')->nullable();
            $table->integer('views_count')->default(0);

            // Robots
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);

            // Table of Contents
            $table->boolean('show_toc')->default(true);
            $table->json('toc_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('published_at');
            $table->index('category_id');
            $table->index('author_id');
            $table->index('source_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.database.table_prefix').'posts');
    }
};
