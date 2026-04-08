<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Vianorte</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root{
            --bg-1:#08111f;
            --bg-2:#0d1728;
            --card:#16202d;
            --card-2:#1b2635;
            --border:rgba(255,255,255,.08);
            --text:#f3f4f6;
            --muted:#9ca3af;
            --red-soft:rgba(239,68,68,.12);
            --red-border:rgba(239,68,68,.25);
            --red-text:#fecaca;
            --green:#27c88a;
            --green-dark:#1fa06f;
        }

        *{ box-sizing:border-box; }

        body{
            margin:0;
            min-height:100vh;
            font-family:Inter, Arial, Helvetica, sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at top right, rgba(239,68,68,.10), transparent 22%),
                linear-gradient(180deg, var(--bg-1) 0%, #07101b 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .card{
            width:100%;
            max-width:520px;
            background:linear-gradient(180deg, var(--card) 0%, var(--card-2) 100%);
            border:1px solid var(--border);
            border-radius:24px;
            box-shadow:0 20px 60px rgba(0,0,0,.28);
            padding:32px 24px;
            text-align:center;
        }

        .badge{
            display:inline-block;
            padding:8px 14px;
            border-radius:999px;
            background:var(--red-soft);
            border:1px solid var(--red-border);
            color:var(--red-text);
            font-weight:700;
            margin-bottom:18px;
        }

        h1{
            font-size:3.5rem;
            margin:0 0 10px;
        }

        h2{
            margin:0 0 12px;
            font-size:1.4rem;
        }

        p{
            margin:0;
            color:var(--muted);
            line-height:1.6;
        }

        .btn{
            margin-top:24px;
            width:100%;
            border:none;
            border-radius:14px;
            min-height:48px;
            padding:12px;
            font-size:1rem;
            font-weight:700;
            cursor:pointer;
            background:var(--green);
            color:#fff;
            transition:.2s;
        }

        .btn:hover{
            background:var(--green-dark);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">Erro interno do servidor</div>

        <h1>500</h1>

        <h2>Ops... algo deu errado 😕</h2>

        <p>
            Ocorreu um erro inesperado no sistema.<br>
            Tente novamente em alguns instantes ou faça login novamente.
        </p>

        {{-- BOTÃO QUE FAZ LOGOUT E VOLTA PRO LOGIN --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn">
                Fazer login novamente
            </button>
        </form>
    </div>
</body>
</html>