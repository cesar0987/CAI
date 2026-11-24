<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema CAI — Gestión integral del centro</title>
    <style>
        :root {
            --ink: #10233a;
            --muted: #4b6178;
            --line: #dce5ee;
            --accent: #1668b8;
            --accent-dark: #0f4d8a;
            --bg: #f6f9fc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.55;
        }
        .wrap { width: min(100% - 2.5rem, 68rem); margin: 0 auto; }
        header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: 1.4rem 0; border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }
        .brand { display: flex; align-items: center; gap: .8rem; }
        .mark {
            width: 2.6rem; height: 2.6rem; border-radius: .8rem;
            background: var(--accent); color: white; display: grid; place-items: center;
            font-weight: 800; letter-spacing: .04em;
        }
        .brand strong { font-size: 1.15rem; display: block; }
        .brand span { font-size: .82rem; color: var(--muted); }
        .badge {
            background: #fff5da; color: #7a5300; border: 1px solid #f0d79a;
            border-radius: 999px; padding: .35rem .85rem; font-size: .8rem; font-weight: 600;
        }
        .hero { padding: 4.5rem 0 3rem; max-width: 47rem; }
        .hero h1 { font-size: clamp(2rem, 4.5vw, 3.2rem); line-height: 1.15; margin: 0 0 1rem; }
        .hero p { font-size: 1.1rem; color: var(--muted); margin: 0 0 2rem; }
        .actions { display: flex; gap: .8rem; flex-wrap: wrap; align-items: center; }
        .btn {
            display: inline-block; background: var(--accent); color: white; text-decoration: none;
            padding: .85rem 1.5rem; border-radius: .65rem; font-weight: 650;
        }
        .btn:hover { background: var(--accent-dark); }
        .hint { color: var(--muted); font-size: .9rem; }
        .grid {
            display: grid; gap: 1rem; padding-bottom: 3.5rem;
            grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
        }
        .card {
            background: white; border: 1px solid var(--line); border-radius: .9rem;
            padding: 1.4rem; box-shadow: 0 1px 2px rgba(16, 35, 58, .04);
        }
        .card h2 { font-size: 1.02rem; margin: 0 0 .45rem; }
        .card p { margin: 0; color: var(--muted); font-size: .92rem; }
        .card .num {
            display: inline-block; font-size: .74rem; font-weight: 700; color: var(--accent);
            background: #eaf3fc; border-radius: .35rem; padding: .1rem .45rem; margin-bottom: .7rem;
        }
        footer {
            border-top: 1px solid var(--line); padding: 1.5rem 0 2.5rem;
            color: var(--muted); font-size: .85rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div class="brand">
                <div class="mark">CAI</div>
                <div>
                    <strong>Sistema CAI</strong>
                    <span>Gestión integral del centro de atención</span>
                </div>
            </div>
            <span class="badge">Entorno de demostración · datos ficticios</span>
        </header>

        <main>
            <section class="hero">
                <h1>Toda la atención del centro, en un solo lugar</h1>
                <p>
                    Alumnos y familias, agenda y asistencia, seguimiento clínico y finanzas
                    familiares, con acceso por rol y auditoría de las acciones sensibles.
                </p>
                <div class="actions">
                    <a class="btn" href="{{ route('filament.panel.auth.login') }}">Ingresar al panel</a>
                    <span class="hint">Acceso restringido: cada rol ve solo lo que le corresponde.</span>
                </div>
            </section>

            <section class="grid" aria-label="Módulos del sistema">
                <article class="card">
                    <span class="num">01</span>
                    <h2>Alumnos y familias</h2>
                    <p>Fichas con código verificable, tutores, cuentas familiares compartidas e historial de pertenencia.</p>
                </article>
                <article class="card">
                    <span class="num">02</span>
                    <h2>Agenda y asistencia</h2>
                    <p>Servicios, profesionales, inscripciones, sesiones recurrentes y registro de asistencia auditado.</p>
                </article>
                <article class="card">
                    <span class="num">03</span>
                    <h2>Seguimiento clínico</h2>
                    <p>Planes y objetivos, notas cifradas en reposo, correcciones trazables y accesos excepcionales con vencimiento.</p>
                </article>
                <article class="card">
                    <span class="num">04</span>
                    <h2>Finanzas familiares</h2>
                    <p>Cargos por alumno, pagos de la familia, imputaciones y saldos calculados, con recibos imprimibles.</p>
                </article>
                <article class="card">
                    <span class="num">05</span>
                    <h2>Seguridad y auditoría</h2>
                    <p>Permisos por rol, segundo factor para roles privilegiados y registro de lecturas y cambios sensibles.</p>
                </article>
            </section>
        </main>

        <footer>
            Demostración con datos 100 % ficticios: no contiene información real de alumnos,
            familias ni profesionales. Las decisiones de negocio pendientes se documentan
            en el cuestionario para Dirección.
        </footer>
    </div>
</body>
</html>
