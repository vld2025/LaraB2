<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documenti', function (Blueprint $table) {
            if (!Schema::hasColumn('documenti', 'notifica_scadenza')) {
                $table->boolean('notifica_scadenza')->default(false)->after('data_scadenza');
            }
            if (!Schema::hasColumn('documenti', 'giorni_preavviso_scadenza')) {
                $table->integer('giorni_preavviso_scadenza')->default(30)->after('notifica_scadenza');
            }
            if (!Schema::hasColumn('documenti', 'versione')) {
                $table->string('versione')->default('1.0')->after('importante');
            }
            if (!Schema::hasColumn('documenti', 'documento_padre_id')) {
                $table->unsignedBigInteger('documento_padre_id')->nullable()->after('versione');
            }
            if (!Schema::hasColumn('documenti', 'hash_file')) {
                $table->string('hash_file', 64)->nullable()->after('documento_padre_id');
            }
            if (!Schema::hasColumn('documenti', 'backup_nas_completato')) {
                $table->boolean('backup_nas_completato')->default(false);
            }
            if (!Schema::hasColumn('documenti', 'backup_cloud_completato')) {
                $table->boolean('backup_cloud_completato')->default(false);
            }
            if (!Schema::hasColumn('documenti', 'ultima_sincronizzazione')) {
                $table->timestamp('ultima_sincronizzazione')->nullable();
            }
            if (!Schema::hasColumn('documenti', 'metadati')) {
                $table->json('metadati')->nullable();
            }
            if (!Schema::hasColumn('documenti', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('documenti', function (Blueprint $table) {
            $table->dropColumn([
                'notifica_scadenza',
                'giorni_preavviso_scadenza',
                'versione',
                'documento_padre_id',
                'hash_file',
                'backup_nas_completato',
                'backup_cloud_completato',
                'ultima_sincronizzazione',
                'metadati',
                'is_active'
            ]);
        });
    }
};
