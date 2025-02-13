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
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name',500)->after('last_name')->nullable();
            $table->string('business_discription',500)->after('business_name')->nullable();
            $table->string('business_license',100)->after('business_discription')->nullable();
            $table->string('business_logo',100)->after('business_license')->nullable();
            $table->enum('business_status',['active', 'inactive'])->after('business_logo')->default('inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
