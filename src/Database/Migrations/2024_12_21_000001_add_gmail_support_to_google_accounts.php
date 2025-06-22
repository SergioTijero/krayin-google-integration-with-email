<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('google_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('google_accounts', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('google_accounts', 'gmail_enabled')) {
                $table->boolean('gmail_enabled')->default(false)->after('scopes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_accounts', function (Blueprint $table) {
            $columns = ['email', 'gmail_enabled'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('google_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
