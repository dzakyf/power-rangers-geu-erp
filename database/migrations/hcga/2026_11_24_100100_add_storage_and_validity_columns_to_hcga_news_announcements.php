<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStorageAndValidityColumnsToHcgaNewsAnnouncements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hcga_news_announcements')) {
            return;
        }

        Schema::table('hcga_news_announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('hcga_news_announcements', 'storage_document_id')) {
                $table->unsignedInteger('storage_document_id')->nullable()->after('id');
                $table->index('storage_document_id');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'storage_version_id')) {
                $table->unsignedInteger('storage_version_id')->nullable()->after('storage_document_id');
                $table->index('storage_version_id');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'storage_module_type')) {
                $table->string('storage_module_type', 30)->nullable()->after('storage_version_id');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'storage_sub_type')) {
                $table->string('storage_sub_type', 40)->nullable()->after('storage_module_type');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'document_number')) {
                $table->string('document_number', 100)->nullable()->after('storage_sub_type');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'version_label')) {
                $table->string('version_label', 20)->nullable()->after('document_number');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'effective_from')) {
                $table->date('effective_from')->nullable()->after('target_audience');
                $table->index('effective_from');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'effective_until')) {
                $table->date('effective_until')->nullable()->after('effective_from');
                $table->index('effective_until');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'is_perpetual')) {
                $table->tinyInteger('is_perpetual')->default(1)->after('effective_until');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'target_departments')) {
                $table->text('target_departments')->nullable()->after('is_perpetual');
            }
            if (!Schema::hasColumn('hcga_news_announcements', 'require_read_receipt')) {
                $table->tinyInteger('require_read_receipt')->default(0)->after('target_departments');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('hcga_news_announcements')) {
            return;
        }

        $columns = [
            'storage_document_id',
            'storage_version_id',
            'storage_module_type',
            'storage_sub_type',
            'document_number',
            'version_label',
            'effective_from',
            'effective_until',
            'is_perpetual',
            'target_departments',
            'require_read_receipt',
        ];

        Schema::table('hcga_news_announcements', function (Blueprint $table) use ($columns) {
            foreach ($columns as $col) {
                if (Schema::hasColumn('hcga_news_announcements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
