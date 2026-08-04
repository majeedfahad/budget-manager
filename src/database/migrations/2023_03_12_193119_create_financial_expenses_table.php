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
        Schema::create('financial_expenses', function (Blueprint $table) {
            $table->id();
            $table->morphs('expensable');
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3);
            $table->unsignedBigInteger('financial_budget_id');
            $table->timestamps();

            $table->foreign('financial_budget_id')->references('id')->on('financial_budgets')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_expenses');
    }
};
