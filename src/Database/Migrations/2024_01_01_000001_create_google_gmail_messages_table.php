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
        Schema::create('google_gmail_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('google_message_id')->index();
            $table->string('thread_id')->nullable()->index();
            $table->json('label_ids')->nullable();
            $table->text('snippet')->nullable();
            $table->string('history_id')->nullable();
            $table->timestamp('internal_date')->nullable();
            $table->bigInteger('size_estimate')->nullable();
            $table->json('payload')->nullable();
            $table->string('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_trash')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->unsignedBigInteger('krayin_email_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('google_accounts')->onDelete('cascade');
            $table->unique(['account_id', 'google_message_id']);
            $table->index(['account_id', 'is_read']);
            $table->index(['account_id', 'is_sent']);
            $table->index(['account_id', 'is_draft']);
            $table->index(['account_id', 'is_trash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_gmail_messages');
    }
};
