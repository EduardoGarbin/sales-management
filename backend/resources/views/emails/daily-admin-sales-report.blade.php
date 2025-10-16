<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Administrativo de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .header .date {
            margin-top: 10px;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .summary {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .summary-title {
            color: #667eea;
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: bold;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .top-sellers {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .seller-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .seller-item:hover {
            background: #f8f9fa;
        }
        .seller-item:last-child {
            border-bottom: none;
        }
        .seller-rank {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .seller-info {
            flex: 1;
        }
        .seller-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        .seller-stats {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        .seller-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }
        .no-sales {
            background: #f39c12;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            color: #777;
            font-size: 0.9em;
        }
        @media (max-width: 600px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Administrativo</h1>
        <div class="date">{{ $date }}</div>
    </div>

    <div class="content">
        <p>Olá, <strong>Administrador</strong>!</p>
        <p>Aqui está o resumo consolidado de todas as vendas realizadas hoje:</p>

        @if($totalSales > 0)
            <div class="summary">
                <div class="summary-title">Resumo Geral</div>

                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-label">Total de Vendas</div>
                        <div class="stat-value">{{ $totalSales }}</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-label">Valor Total</div>
                        <div class="stat-value">R$ {{ number_format($totalAmount, 0, ',', '.') }}</div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-label">Comissões</div>
                        <div class="stat-value">R$ {{ number_format($totalCommission, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            @if(count($topSellers) > 0)
                <div class="top-sellers">
                    <div class="summary-title">Top Vendedores do Dia</div>

                    @foreach($topSellers as $index => $seller)
                        <div class="seller-item">
                            <div class="seller-rank">{{ $index + 1 }}</div>
                            <div class="seller-info">
                                <div class="seller-name">{{ $seller['name'] }}</div>
                                <div class="seller-stats">
                                    {{ $seller['sales_count'] }} {{ $seller['sales_count'] === 1 ? 'venda' : 'vendas' }} •
                                    Comissão: R$ {{ number_format($seller['commission'], 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="seller-amount">
                                R$ {{ number_format($seller['amount'], 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; border: 1px solid #c3e6cb;">
                <p style="margin: 0; font-size: 1.1rem;">
                    <strong>Excelente desempenho!</strong>
                </p>
                <p style="margin: 10px 0 0 0;">
                    Continue monitorando o progresso da equipe.
                </p>
            </div>
        @else
            <div class="no-sales">
                <p style="margin: 0; font-size: 1.2rem;">
                    Nenhuma venda foi registrada hoje
                </p>
                <p style="margin: 10px 0 0 0;">
                    Acompanhe a equipe e incentive novas vendas.
                </p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Este é um relatório automático gerado pelo sistema.</p>
        <p>&copy; {{ date('Y') }} Sales Management System</p>
    </div>
</body>
</html>
