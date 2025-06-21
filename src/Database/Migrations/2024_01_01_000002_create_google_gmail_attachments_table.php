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
        Schema::create('google_gmail_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('google_attachment_id');
            $table->string('filename');
            $table->string('mime_type');
            $table->bigInteger('size')->default(0);
            $table->longText('data')->nullable();
            $table->boolean('is_inline')->default(false);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('google_gmail_messages')->onDelete('cascade');
            $table->index(['message_id', 'is_inline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_gmail_attachments');
    }
};
