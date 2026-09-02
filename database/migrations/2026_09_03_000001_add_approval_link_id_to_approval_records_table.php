<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: For pre-existing pre-launch development databases, running
     * `php artisan migrate:fresh --seed` will start clean with consistent
     * foreign key relationships between approval_records and approval_links.
     */
    public function up(): void
    {
        Schema::table('approval_records', function (Blueprint $table) {
            $table->foreignUuid('approval_link_id')
                ->nullable()
                ->after('id')
                ->constrained('approval_links')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approval_link_id');
        });
    }
};
