<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSshKeyAndAvatarToBitcoinAuths extends Migration
{
    public function up()
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->text('ssh_key')->nullable()->after('signature');
            $table->string('avatar_path')->nullable()->after('ssh_key');
        });
    }

    public function down()
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->dropColumn(['ssh_key', 'avatar_path']);
        });
    }
}