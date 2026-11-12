<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calculadora de precios</title>
    <style>
        *{box-sizing:border-box} body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:#eef2f7;color:#1f2933}
        header{background:#1e3a5f;color:#fff;padding:22px 32px} header h1{margin:0;font-size:1.5rem} header p{margin:4px 0 0;opacity:.8}
        main{max-width:900px;margin:32px auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;padding:0 16px}
        .card{background:#fff;border-radius:12px;padding:26px;box-shadow:0 6px 18px #0001}
        h2{margin-top:0;color:#1e3a5f} form{display:grid;gap:12px}
        label{font-weight:600;font-size:.9rem} input,select{width:100%;padding:10px;border:1px solid #cbd2d9;border-radius:6px;font:inherit}
        .fila{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        button{background:#1e3a5f;color:#fff;border:0;padding:11px;border-radius:6px;font-weight:600;cursor:pointer}
        .resultado{margin-top:16px;padding:14px;border-radius:8px;background:#e6f4ea;color:#1e6b34;font-size:1.25rem;font-weight:700}
        .error{max-width:900px;margin:20px auto 0;padding:12px 16px;border-radius:8px;background:#fde8e8;color:#9b1c1c}
        small{color:#52606d}
    </style>
</head>
<body>
<header>
    <h1>💶 Calculadora de precios</h1>
    <p>Conversión EUR ⇄ USD y cálculo de descuentos · PriceController</p>
</header>

@if ($error)
    <div class="error">{{ $error }}</div>
@endif

<main>
    <section class="card">
        <h2>Conversor de monedas</h2>
        <form method="GET" action="{{ route('precios') }}">
            <label for="cantidad">Cantidad</label>
            <input id="cantidad" type="number" name="cantidad" step="0.01" min="0" value="{{ request('cantidad', 100) }}" required>
            <div class="fila">
                <div>
                    <label for="de">De</label>
                    <select id="de" name="de">
                        @foreach (['EUR' => 'Euros (€)', 'USD' => 'Dólares ($)'] as $codigo => $nombre)
                            <option value="{{ $codigo }}" @selected(request('de', 'EUR') === $codigo)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="a">A</label>
                    <select id="a" name="a">
                        @foreach (['USD' => 'Dólares ($)', 'EUR' => 'Euros (€)'] as $codigo => $nombre)
                            <option value="{{ $codigo }}" @selected(request('a', 'USD') === $codigo)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit">Convertir</button>
        </form>
        @if (! is_null($conversion))
            <div class="resultado">
                {{ number_format((float) request('cantidad'), 2, ',', '.') }} {{ strtoupper(request('de')) }} =
                {{ number_format($conversion, 2, ',', '.') }} {{ strtoupper(request('a')) }}
            </div>
        @endif
        <p><small>Tipo de cambio: 1 EUR = {{ number_format($tasa, 2, ',', '.') }} USD</small></p>
    </section>

    <section class="card">
        <h2>Calcular descuento</h2>
        <form method="GET" action="{{ route('precios') }}">
            <label for="precio">Precio (€)</label>
            <input id="precio" type="number" name="precio" step="0.01" min="0" value="{{ request('precio', 80) }}" required>
            <label for="porcentaje">Descuento (%)</label>
            <input id="porcentaje" type="number" name="porcentaje" step="0.01" min="0" max="100" value="{{ request('porcentaje', 25) }}" required>
            <button type="submit">Aplicar descuento</button>
        </form>
        @if (! is_null($descuento))
            <div class="resultado">
                Precio final: {{ number_format($descuento, 2, ',', '.') }} €
                <br><small>Ahorras {{ number_format((float) request('precio') - $descuento, 2, ',', '.') }} €</small>
            </div>
        @endif
    </section>
</main>
</body>
</html>
