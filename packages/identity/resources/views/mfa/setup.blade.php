<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar verificación en dos pasos · CAI</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; color: #111827; margin: 0; display: grid; place-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: .75rem; box-shadow: 0 10px 25px rgba(0,0,0,.08); width: min(30rem, 92vw); }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p, li { color: #4b5563; font-size: .9rem; line-height: 1.5; }
        code { display: block; background: #f3f4f6; padding: .6rem; border-radius: .3rem; word-break: break-all; font-size: .8rem; }
        input { width: 100%; box-sizing: border-box; padding: .7rem; font-size: 1.1rem; letter-spacing: .35em; text-align: center; border: 1px solid #d1d5db; border-radius: .4rem; margin: 1rem 0; }
        button { width: 100%; padding: .7rem; background: #2563eb; color: white; border: 0; border-radius: .4rem; font-weight: 600; cursor: pointer; }
        .error { color: #b91c1c; font-size: .85rem; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Configurar la verificación en dos pasos</h1>
        <p>Su rol requiere MFA. Agregue esta cuenta a una app autenticadora (Google Authenticator, Authy, etc.):</p>
        <ol>
            <li>Agregue una cuenta nueva y escanee el código URI o ingrese la clave secreta.</li>
            <li>Luego confirme con el código de 6 dígitos que muestra la app.</li>
        </ol>
        <p>Secreto:</p>
        <code>{{ $secret }}</code>
        <p>URI de aprovisionamiento:</p>
        <code>{{ $uri }}</code>

        @if ($errors->any())
            <p class="error">{{ $errors->first('code') }}</p>
        @endif

        <form method="POST" action="{{ route('mfa.setup.confirm') }}">
            @csrf
            <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus required>
            <button type="submit">Activar MFA e ingresar</button>
        </form>
    </main>
</body>
</html>
