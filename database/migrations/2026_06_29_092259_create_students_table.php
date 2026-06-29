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
    Schema::create('students', function (
        Blueprint $table
    ) {

        $table->id();

        $table->string(
            'student_id'
        )->unique();

        $table->string(
            'first_name'
        );

        $table->string(
            'last_name'
        );

        $table->string(
            'email'
        )->unique();

        $table->string(
            'phone'
        )->nullable();

        $table->string(
            'gender'
        )->nullable();

        $table->date(
            'dob'
        )->nullable();

        $table->text(
            'address'
        )->nullable();

        $table->string(
            'city'
        )->nullable();

        $table->string(
            'state'
        )->nullable();

        $table->string(
            'country'
        )->nullable();

        $table->string(
            'course'
        )->nullable();

        $table->string(
            'profile_image'
        )->nullable();

        $table->string(
            'password'
        );

        $table->boolean(
            'is_active'
        )->default(true);

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
