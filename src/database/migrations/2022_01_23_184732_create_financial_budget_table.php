<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_budgets', function (Blueprint $table) {
            $table->id();
            $table->double('budget');
            $table->morphs('budgetable');
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            $table->unique(['budgetable_type', 'budgetable_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_budgets');
    }
};
