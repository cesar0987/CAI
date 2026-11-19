<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación en dos pasos · CAI</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; color: #111827; margin: 0; display: grid; place-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: .75rem; box-shadow: 0 10px 25px rgba(0,0,0,.08); width: min(24rem, 92vw); }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p { color: #4b5563; font-size: .9rem; line-height: 1.5; }
        input { width: 100%; box-sizing: border-box; padding: .7rem; font-size: 1.1rem; letter-spacing: .35em; text-align: center; border: 1px solid #d1d5db; border-radius: .4rem; margin: 1rem 0; }
        button { width: 100%; padding: .7rem; background: #2563eb; color: white; border: 0; border-radius: .4rem; font-weight: 600; cursor: pointer; }
        .error { color: #b91c1c; font-size: .85rem; }
        a { color: #2563eb; font-size: .85rem; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Verificación en dos pasos</h1>
        <p>Ingrese el código de 6 dígitos de su app autenticadora para continuar como <strong>{{ $email }}</strong>.</p>

        @if ($errors->any())
            <p class="error">{{ $errors->first('code') }}</p>
        @endif

        <form method="POST" action="{{ route('mfa.verify') }}">
            @csrf
            <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus required>
            <button type="submit">Verificar e ingresar</button>
        </form>

        <p><a href="{{ route('filament.panel.auth.login') }}">Volver al inicio de sesión</a></p>
    </main>
</body>
</html>
