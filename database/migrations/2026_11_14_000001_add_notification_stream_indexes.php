<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNotificationStreamIndexes extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notification')) {
            return;
        }

        Schema::table('notification', function (Blueprint $table) {
            if (!$this->hasIndex('notification', 'notification_recipient_id_idx')) {
                $table->index(['Recipient_Id', 'ID'], 'notification_recipient_id_idx');
            }
            if (!$this->hasIndex('notification', 'notification_status_id_idx')) {
                $table->index(['Status', 'ID'], 'notification_status_id_idx');
            }
            if (!$this->hasIndex('notification', 'notification_expires_at_idx')) {
                $table->index('Expires_At', 'notification_expires_at_idx');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('notification')) {
            return;
        }

        Schema::table('notification', function (Blueprint $table) {
            if ($this->hasIndex('notification', 'notification_recipient_id_idx')) {
                $table->dropIndex('notification_recipient_id_idx');
            }
            if ($this->hasIndex('notification', 'notification_status_id_idx')) {
                $table->dropIndex('notification_status_id_idx');
            }
            if ($this->hasIndex('notification', 'notification_expires_at_idx')) {
                $table->dropIndex('notification_expires_at_idx');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return !empty($rows);
    }
}
