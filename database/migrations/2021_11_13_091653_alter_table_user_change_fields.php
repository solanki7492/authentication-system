<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserChangeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users',function(Blueprint $table) {
            $table->string("user_name")->nullable()->after('email');
            $table->string("avatar")->nullable()->after('user_name');
            $table->integer("user_role")->comment("0 => user, 1 => admin")->default(0)->nullable()->after('avatar');
            $table->timestamp("registered_at")->nullable()->after('user_role');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('users',function(Blueprint $table) {
            $table->dropField("user_name");
            $table->dropField("avatar");
            $table->dropField("user_role");
            $table->dropField("registered_at");
        });
    }
}
