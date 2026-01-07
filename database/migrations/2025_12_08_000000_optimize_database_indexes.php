<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        );

        return (int) ($result->cnt ?? 0) > 0;
    }

    public function up(): void
    {
        // Optimize audit_histories for faster filtering and deletion
        if (Schema::hasColumn('audit_histories', 'created_at') && Schema::hasColumn('audit_histories', 'user_id')) {
            Schema::table('audit_histories', function (Blueprint $table) {
                if (! $this->indexExists('audit_histories', 'idx_audit_histories_created_user')) {
                    $table->index(['created_at', 'user_id'], 'idx_audit_histories_created_user');
                }
            });
        }

        if (Schema::hasColumn('audit_histories', 'module') && Schema::hasColumn('audit_histories', 'created_at')) {
            Schema::table('audit_histories', function (Blueprint $table) {
                if (! $this->indexExists('audit_histories', 'idx_audit_histories_module_created')) {
                    $table->index(['module', 'created_at'], 'idx_audit_histories_module_created');
                }
            });
        }

        // Optimize ec_products_translations
        if (Schema::hasColumn('ec_products_translations', 'name')) {
            Schema::table('ec_products_translations', function (Blueprint $table) {
                if (! $this->indexExists('ec_products_translations', 'idx_product_translations_name')) {
                    $table->index('name', 'idx_product_translations_name');
                }
            });
        }

        // Optimize ec_product_views  
        if (Schema::hasColumn('ec_product_views', 'created_at')) {
            Schema::table('ec_product_views', function (Blueprint $table) {
                if (! $this->indexExists('ec_product_views', 'idx_product_views_created')) {
                    $table->index('created_at', 'idx_product_views_created');
                }
            });
        }

        // Optimize failed_jobs for cleanup
        if (Schema::hasColumn('failed_jobs', 'failed_at')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                if (! $this->indexExists('failed_jobs', 'idx_failed_jobs_failed_at')) {
                    $table->index('failed_at', 'idx_failed_jobs_failed_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('audit_histories', function (Blueprint $table) {
            if ($this->indexExists('audit_histories', 'idx_audit_histories_created_user')) {
                $table->dropIndex('idx_audit_histories_created_user');
            }

            if ($this->indexExists('audit_histories', 'idx_audit_histories_module_created')) {
                $table->dropIndex('idx_audit_histories_module_created');
            }
        });

        Schema::table('ec_products_translations', function (Blueprint $table) {
            if ($this->indexExists('ec_products_translations', 'idx_product_translations_name')) {
                $table->dropIndex('idx_product_translations_name');
            }
        });

        Schema::table('ec_product_views', function (Blueprint $table) {
            if ($this->indexExists('ec_product_views', 'idx_product_views_created')) {
                $table->dropIndex('idx_product_views_created');
            }
        });

        Schema::table('failed_jobs', function (Blueprint $table) {
            if ($this->indexExists('failed_jobs', 'idx_failed_jobs_failed_at')) {
                $table->dropIndex('idx_failed_jobs_failed_at');
            }
        });
    }
};
