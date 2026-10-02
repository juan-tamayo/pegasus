<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_subject')->nullable()->unique();
        });
        Schema::create('mailbox_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('address');
            $table->string('display_name')->nullable();
            $table->string('state')->default('disconnected');
            $table->text('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('generation')->default(1);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider', 'address']);
        });
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mailbox_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->string('detected_status')->default('awaiting_response');
            $table->string('manual_status')->nullable();
            $table->boolean('review_required')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_response_at')->nullable();
            $table->timestamps();
        });
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mailbox_connection_id')->constrained()->cascadeOnDelete();
            $table->string('provider_message_key');
            $table->string('provider_thread_id')->nullable();
            $table->string('sender');
            $table->string('subject');
            $table->timestamp('received_at');
            $table->string('processing_state')->default('pending');
            $table->timestamps();
            $table->unique(['mailbox_connection_id', 'provider_message_key']);
        });
        Schema::create('application_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_message_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('subject');
            $table->text('evidence');
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique('email_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_events');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('mailbox_connections');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('google_subject'));
    }
};
