<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Diário de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .stats {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-label {
            font-weight: bold;
            color: #555;
        }
        .stat-value {
            color: #2c3e50;
            font-size: 1.1em;
        }
        .highlight {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #777;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Diário de Vendas</h1>
        <p>{{ $date }}</p>
    </div>

    <div class="content">
        <p>Olá, <strong>{{ $seller->name }}</strong>!</p>

        <p>Aqui está o resumo das suas vendas realizadas hoje:</p>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-label">Quantidade de Vendas:</span>
                <span class="stat-value">{{ $salesCount }} {{ $salesCount === 1 ? 'venda' : 'vendas' }}</span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Valor Total:</span>
                <span class="stat-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Comissão Total:</span>
                <span class="stat-value">R$ {{ number_format($totalCommission, 2, ',', '.') }}</span>
            </div>
        </div>

        @if($salesCount > 0)
            <div class="highlight">
                <p style="margin: 0; font-size: 1.2em;">
                    <strong>Parabéns pelo seu desempenho!</strong>
                </p>
                <p style="margin: 10px 0 0 0;">
                    Continue assim e alcance suas metas!
                </p>
            </div>
        @else
            <div style="background-color: #f39c12; color: white; padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px;">
                <p style="margin: 0;">
                    Nenhuma venda foi realizada hoje. Vamos buscar novos clientes amanhã!
                </p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Este é um e-mail automático. Por favor, não responda.</p>
        <p>&copy; {{ date('Y') }} Sales Management System</p>
    </div>
</body>
</html>
