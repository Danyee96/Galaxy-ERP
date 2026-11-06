<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiSystemEstimationPlanning extends Migration
{
    public function up()
    {
        Schema::table('product_estimates', function (Blueprint $table) {
            $table->unsignedTinyInteger('planning_version')->nullable();
            $table->string('allocation_method', 20)->nullable();
            $table->date('plan_date')->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->string('inventory_fingerprint', 64)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->uuid('request_key')->nullable()->unique();
        });
        Schema::create('product_estimate_systems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_estimate_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('position');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('requested_units');
            $table->unsignedBigInteger('planned_units');
            $table->unsignedBigInteger('feasible_units');
            $table->unsignedBigInteger('standalone_maximum');
            $table->unsignedInteger('weight');
            $table->json('recipe_snapshot');
            $table->unique(['product_estimate_id', 'product_id'], 'estimate_system_unique');
            $table->foreign('product_estimate_id')->references('id')->on('product_estimates')->onDelete('cascade');
        });
        Schema::create('product_estimate_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_estimate_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->string('sku')->nullable();
            foreach (['physical_stock', 'legacy_committed', 'projected_stock', 'in_transit', 'available', 'required', 'allocated', 'remaining', 'shortage'] as $column) {
                $table->decimal($column, 24, 4);
            }
            $table->json('consumers');
            $table->unique(['product_estimate_id', 'product_id'], 'estimate_component_unique');
            $table->foreign('product_estimate_id')->references('id')->on('product_estimates')->onDelete('cascade');
        });
        Schema::create('estimate_number_counters', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->unsignedBigInteger('next_number')->default(1001);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_estimate_components');
        Schema::dropIfExists('product_estimate_systems');
        Schema::dropIfExists('estimate_number_counters');
        Schema::table('product_estimates', function (Blueprint $table) {
            $table->dropUnique(['request_key']);
            $table->dropColumn(['planning_version', 'allocation_method', 'plan_date', 'calculation_snapshot', 'inventory_fingerprint', 'created_by', 'request_key']);
        });
    }
}
