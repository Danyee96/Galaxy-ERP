<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDashboardIndexes extends Migration
{
    public function up()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            $table->index('status', 'item_histories_status_index');
            $table->index('created_at', 'item_histories_created_at_index');
            $table->index('entry_date', 'item_histories_entry_date_index');
            $table->index('exit_date', 'item_histories_exit_date_index');
            $table->index('company_id', 'item_histories_company_id_index');
            $table->index('power_id', 'item_histories_power_id_index');
            $table->index('error_id', 'item_histories_error_id_index');
        });

        Schema::table('item_history_accessories', function (Blueprint $table) {
            $table->index('accessory_id', 'item_history_accessories_accessory_id_index');
        });
    }

    public function down()
    {
        Schema::table('item_histories', function (Blueprint $table) {
            $table->dropIndex('item_histories_status_index');
            $table->dropIndex('item_histories_created_at_index');
            $table->dropIndex('item_histories_entry_date_index');
            $table->dropIndex('item_histories_exit_date_index');
            $table->dropIndex('item_histories_company_id_index');
            $table->dropIndex('item_histories_power_id_index');
            $table->dropIndex('item_histories_error_id_index');
        });

        Schema::table('item_history_accessories', function (Blueprint $table) {
            $table->dropIndex('item_history_accessories_accessory_id_index');
        });
    }
}