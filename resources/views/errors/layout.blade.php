<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — SIGEPEI</title>
    <style>
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-top: 4px solid #006633;
            border-radius: 4px;
            max-width: 460px;
            width: 100%;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            text-align: center;
        }
        .code {
            font-size: 3rem;
            font-weight: 800;
            color: #006633;
            line-height: 1;
            margin-bottom: 0.75rem;
        }
        h1 {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            color: #0f172a;
        }
        p {
            font-size: 0.875rem;
            color: #475569;
            margin: 0 0 1.5rem 0;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 0.55rem 1.1rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #006633;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #005028;
        }
        .btn-secondary {
            background-color: #e2e8f0;
            color: #334155;
        }
        .btn-secondary:hover {
            background-color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-secondary">Voltar</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Ir para o Início</a>
        </div>
    </div>
</body>
</html>

