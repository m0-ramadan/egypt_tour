<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('nationality', 120)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('passport_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('inquiry_type', ['contact', 'package', 'quote', 'tailor_made', 'callback'])->default('contact');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('country_name', 120)->nullable();
            $table->date('travel_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->string('source')->nullable();
            $table->longText('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'quoted', 'won', 'lost', 'spam'])->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('booking_number', 50)->unique();
            $table->enum('status', ['pending', 'confirmed', 'paid', 'cancelled', 'completed'])->default('pending');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->date('booking_date');
            $table->date('travel_date');
            $table->unsignedInteger('adults');
            $table->unsignedInteger('children')->default(0);
            $table->text('special_requests')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['email', 'whatsapp', 'phone', 'sms'])->default('email');
            $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->timestamp('sent_at');
            $table->enum('status', ['draft', 'sent', 'failed', 'received'])->default('sent');
            $table->string('attachment_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('clients');
    }
};
