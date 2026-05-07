<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('blog.database.table_prefix', 'blog_').'posts';

        Schema::table($tableName, function (Blueprint $table) {
            // Content Brief metadata
            $table->string('search_intent', 32)->nullable()->after('focus_keyword');
            $table->string('customer_journey_stage', 32)->nullable()->after('search_intent');
            $table->string('topic_cluster', 32)->nullable()->after('customer_journey_stage');
            $table->string('pillar_url', 500)->nullable()->after('topic_cluster');
            $table->string('target_icp', 255)->nullable()->after('pillar_url');
            $table->string('target_geography', 32)->nullable()->after('target_icp');
            $table->string('content_freshness', 32)->nullable()->after('target_geography');
            $table->string('conversion_goal', 64)->nullable()->after('content_freshness');
            $table->json('original_insight')->nullable()->after('conversion_goal');
        });
    }

    public function down(): void
    {
        $tableName = config('blog.database.table_prefix', 'blog_').'posts';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn([
                'search_intent',
                'customer_journey_stage',
                'topic_cluster',
                'pillar_url',
                'target_icp',
                'target_geography',
                'content_freshness',
                'conversion_goal',
                'original_insight',
            ]);
        });
    }
};
