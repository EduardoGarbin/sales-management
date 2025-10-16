<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendar envio de relatórios diários
// Executa todos os dias às 23:55 (5 minutos antes da meia-noite)
Schedule::command('sales:send-daily-reports')
    ->dailyAt('23:55')
    ->timezone('America/Sao_Paulo')
    ->name('envio-relatorios-diarios')
    ->withoutOverlapping()
    ->onSuccess(function () {
        info('Relatórios diários enviados com sucesso');
    })
    ->onFailure(function () {
        error('Falha ao enviar relatórios diários');
    });
