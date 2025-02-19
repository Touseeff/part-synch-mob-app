<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();                            // Equivalent to 'id' int(11) AUTO_INCREMENT
            $table->string('name', 255)->nullable(); // 'name' varchar(255)
            $table->text('description')->nullable(); // 'description' text
            $table->string('category_image',200)->nullable();
            $table->timestamps(); // This will automatically add 'created_at' and 'updated_at' datetime fields
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
}
