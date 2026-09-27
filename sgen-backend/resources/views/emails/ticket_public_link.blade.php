<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; font-size: 14px; color: #f0f2f5; background: #0b0d11; margin: 0; padding: 0; }
        .card { max-width: 560px; margin: 32px auto; background: #12141a; border: 1px solid #1d2027; border-radius: 12px; padding: 24px; }
        .code { display: inline-block; padding: 4px 10px; background: #182131; border: 1px solid #2b3c56; border-radius: 8px; font-family: SFMono-Regular, Consolas, monospace; color: #5fa8fb; font-weight: 600; }
        h1 { margin: 12px 0 8px; font-size: 18px; }
        p { margin: 6px 0; color: #c3c7d0; line-height: 1.5; }
        .btn { display: inline-block; margin: 14px 0 0; padding: 10px 18px; background: #10b981; color: #061a12; text-decoration: none; font-weight: 700; border-radius: 12px; }
        .note { font-size: 11.5px; color: #7d8496; margin-top: 18px; }
    </style>
</head>
<body>
<div class="card">
    <span class="code">{{ $codigo }}</span>
    <h1>Tu solicitud está en seguimiento</h1>
    <p>El técnico asignado ya está al tanto. Prioridad: <strong>{{ $prioridad }}</strong>.</p>
    <p>Seguimiento en vivo, sin registro — solo con este enlace:</p>
    <a class="btn" href="{{ $url }}">Ver estado del ticket</a>
    <p class="note">Link personal. Si lo compartes, cualquiera que lo use puede leerlo.</p>
</div>
</body>
</html>
