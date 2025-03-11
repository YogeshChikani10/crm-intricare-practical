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
        Schema::table('contact_custom_fields', function (Blueprint $table) {
            //
            $table->string('data_type')->default('text')->after('field_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_custom_fields', function (Blueprint $table) {
            //
            $table->dropColumn('data_type');
        });
    }
};
