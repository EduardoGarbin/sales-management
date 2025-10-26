<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona índices na tabela sales para otimizar queries frequentes:
     * - sale_date: usado em whereDate() no scheduler e reenvio de emails
     * - seller_id + sale_date (composto): otimiza queries que filtram por ambos
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Índice simples para queries por data
            $table->index('sale_date', 'sales_sale_date_index');

            // Índice composto para queries que filtram por vendedor E data
            // Útil para: getSalesByDate(), relatórios diários, etc
            $table->index(['seller_id', 'sale_date'], 'sales_seller_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_sale_date_index');
            $table->dropIndex('sales_seller_date_index');
        });
    }
};
