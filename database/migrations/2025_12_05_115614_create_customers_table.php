<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration class
 * This file is responsible for creating database tables
 * when we run: php artisan migrate
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * This method creates tables in the database.
     */
    public function up(): void
    {
        /**
         * ===============================
         * CUSTOMERS TABLE
         * ===============================
         * This table stores customer details
         * for authentication (Register, Login, Change Password)
         */
        Schema::create('customers', function (Blueprint $table) {

            // Auto-increment primary key (customer ID)
            $table->id();

            // Customer full name
            $table->string('name');

            // Customer email address (must be unique)
            // Used for login and forgot password
            $table->string('email')->unique();

            // Encrypted (hashed) password
            // Never store plain text password
            $table->string('password');

            // Customer account status
            // active  → can login
            // inactive → blocked
            $table->enum('status', ['active', 'inactive'])->default('active');

            /**
             * created_by
             * Stores the admin (users table) who created this customer
             * Nullable because customer can also self-register
             */
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            /**
             * updated_by
             * Stores the admin who last updated customer record
             */
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // created_at & updated_at columns
            $table->timestamps();

            /**
             * Soft delete column
             * When customer is deleted, record stays in DB
             * deleted_at column is filled instead of hard delete
             */
            $table->softDeletes();
        });

        /**
         * ===============================
         * PASSWORD RESETS TABLE
         * ===============================
         * This table is used for Forgot Password feature
         * It stores password reset token temporarily
         */
        Schema::create('password_resets', function (Blueprint $table) {

            // Customer email requesting password reset
            $table->string('email')->index();

            // Random token sent in reset password link
            $table->string('token');

            // Token creation time
            // Used to expire/reset old tokens if needed
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     * This method deletes tables if we rollback migration.
     */
    public function down(): void
    {
        // Drop password_resets table first (dependency safe)
        Schema::dropIfExists('password_resets');

        // Drop customers table
        Schema::dropIfExists('customers');
    }
};
