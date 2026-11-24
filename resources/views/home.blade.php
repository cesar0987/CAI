<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema CAI: fichas, agenda, seguimiento clínico y finanzas familiares en un solo lugar, con acceso por rol y auditoría.">
    <title>Sistema CAI — Centro de Atención Inclusiva</title>
    <style>
        :root {
            --ink: #0f2438;
            --muted: #536a80;
            --line: #dde6ef;
            --accent: #1367b5;
            --accent-dark: #0c4d8c;
            --soft: #eef5fc;
            --bg: #f7fafd;
            --gold: #c98a12;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: min(100% - 2.5rem, 70rem); margin: 0 auto; }
        a { color: inherit; }

        /* Barra superior */
        .topbar { background: white; border-bottom: 1px solid var(--line); }
        .topbar .wrap {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; min-height: 4.6rem; flex-wrap: wrap;
        }
        .brand { display: flex; align-items: center; gap: .85rem; text-decoration: none; }
        .mark {
            width: 2.9rem; height: 2.9rem; border-radius: .85rem;
            background: linear-gradient(145deg, var(--accent), var(--accent-dark));
            color: white; display: grid; place-items: center;
            font-weight: 800; font-size: .95rem; letter-spacing: .05em;
            box-shadow: 0 4px 12px rgba(19, 103, 181, .22);
        }
        .brand strong { display: block; font-size: 1.12rem; line-height: 1.2; }
        .brand small { display: block; color: var(--muted); font-size: .78rem; }
        .btn {
            display: inline-block; text-decoration: none; border-radius: .7rem;
            padding: .8rem 1.4rem; font-weight: 650; font-size: .95rem;
            background: var(--accent); color: white; border: 1px solid var(--accent);
            transition: background .15s ease, transform .15s ease;
        }
        .btn:hover { background: var(--accent-dark); transform: translateY(-1px); }
        .btn-ghost { background: transparent; color: var(--accent); }
        .btn-ghost:hover { background: var(--soft); color: var(--accent-dark); }
        .demo-badge {
            font-size: .75rem; font-weight: 700; color: #7a5300;
            background: #fff5da; border: 1px solid #f0d79a;
            border-radius: 999px; padding: .35rem .8rem;
        }

        /* Hero */
        .hero {
            background: linear-gradient(160deg, #ffffff 0%, #eef5fc 60%, #e6f0fa 100%);
            border-bottom: 1px solid var(--line);
        }
        .hero .wrap {
            display: grid; gap: 2.5rem; padding: 4.6rem 0 4rem;
            grid-template-columns: minmax(0, 1.25fr) minmax(16rem, .75fr);
            align-items: center;
        }
        .eyebrow {
            text-transform: uppercase; letter-spacing: .16em; font-size: .74rem;
            font-weight: 800; color: var(--accent); margin: 0 0 1rem;
        }
        .hero h1 {
            font-size: clamp(2.1rem, 4.4vw, 3.35rem); line-height: 1.12;
            letter-spacing: -.02em; margin: 0 0 1.15rem;
        }
        .hero h1 em { font-style: normal; color: var(--accent); }
        .lede { font-size: 1.1rem; color: var(--muted); max-width: 38rem; margin: 0 0 1.9rem; }
        .actions { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }
        .quote {
            background: white; border: 1px solid var(--line); border-left: 4px solid var(--accent);
            border-radius: .9rem; padding: 1.5rem 1.6rem;
            box-shadow: 0 10px 30px rgba(15, 36, 56, .06);
        }
        .quote p { margin: 0; font-size: 1.02rem; font-weight: 550; line-height: 1.55; }
        .quote span { display: block; margin-top: .9rem; font-size: .8rem; color: var(--muted); }

        /* Cifras */
        .facts { background: white; border-bottom: 1px solid var(--line); }
        .facts .wrap {
            display: grid; gap: 1px; background: var(--line);
            grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
            padding: 1px; margin-top: -1px;
        }
        .fact { background: white; padding: 1.6rem 1.2rem; text-align: center; }
        .fact strong { display: block; font-size: 1.7rem; color: var(--accent); line-height: 1.1; }
        .fact span { font-size: .85rem; color: var(--muted); }

        /* Secciones */
        section.block { padding: 4rem 0; }
        .section-head { max-width: 42rem; margin-bottom: 2.2rem; }
        .section-head h2 { font-size: clamp(1.7rem, 3vw, 2.3rem); line-height: 1.2; margin: 0 0 .7rem; }
        .section-head p { color: var(--muted); margin: 0; }
        .grid {
            display: grid; gap: 1.1rem;
            grid-template-columns: repeat(auto-fit, minmax(15.5rem, 1fr));
        }
        .card {
            background: white; border: 1px solid var(--line); border-radius: 1rem;
            padding: 1.6rem; box-shadow: 0 2px 6px rgba(15, 36, 56, .03);
        }
        .icon {
            width: 2.5rem; height: 2.5rem; border-radius: .7rem; background: var(--soft);
            color: var(--accent); display: grid; place-items: center;
            font-weight: 800; margin-bottom: 1rem; font-size: .85rem;
        }
        .card h3 { font-size: 1.05rem; margin: 0 0 .5rem; }
        .card p { margin: 0; color: var(--muted); font-size: .92rem; }

        .alt { background: white; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .principles { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(17rem, 1fr)); }
        .principle { display: flex; gap: .9rem; align-items: flex-start; }
        .principle .dot {
            flex: none; width: 1.7rem; height: 1.7rem; border-radius: 50%;
            background: var(--accent); color: white; display: grid; place-items: center;
            font-size: .78rem; font-weight: 800; margin-top: .15rem;
        }
        .principle h3 { margin: 0 0 .25rem; font-size: 1rem; }
        .principle p { margin: 0; color: var(--muted); font-size: .9rem; }

        .cta {
            background: linear-gradient(135deg, var(--accent-dark), var(--accent));
            color: white; border-radius: 1.2rem; padding: 2.6rem 2.4rem;
            display: flex; gap: 1.5rem; align-items: center; justify-content: space-between;
            flex-wrap: wrap; margin-bottom: 4rem;
        }
        .cta h2 { margin: 0 0 .4rem; font-size: 1.7rem; }
        .cta p { margin: 0; opacity: .9; max-width: 34rem; }
        .cta .btn { background: white; color: var(--accent-dark); border-color: white; }
        .cta .btn:hover { background: var(--soft); }

        footer { background: #0f2438; color: #c7d6e5; padding: 2.4rem 0 3rem; font-size: .86rem; }
        footer .wrap { display: flex; gap: 1rem; justify-content: space-between; flex-wrap: wrap; }
        footer strong { color: white; display: block; margin-bottom: .35rem; }
        footer p { margin: 0; max-width: 46rem; }

        @media (max-width: 820px) {
            .hero .wrap { grid-template-columns: 1fr; padding: 3.2rem 0 3rem; }
            .hero h1 { font-size: 2.3rem; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="wrap">
            <a class="brand" href="/" aria-label="Inicio del Sistema CAI">
                <span class="mark">CAI</span>
                <span>
                    <strong>Sistema CAI</strong>
                    <small>Centro de Atención Inclusiva</small>
                </span>
            </a>
            <div class="actions">
                <span class="demo-badge">Demo · datos ficticios</span>
                <a class="btn" href="{{ route('filament.panel.auth.login') }}">Ingresar al panel</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="wrap">
            <div>
                <p class="eyebrow">Gestión integral del centro</p>
                <h1>Cada alumno, toda su historia, <em>en un solo lugar</em></h1>
                <p class="lede">
                    Fichas, familias, agenda, asistencia, evolución clínica y cobros dejan
                    de vivir en planillas y carpetas separadas. El centro tiene una única
                    foto de cada alumno, con la seguridad de que cada persona ve solo lo
                    que le corresponde.
                </p>
                <div class="actions">
                    <a class="btn" href="{{ route('filament.panel.auth.login') }}">Ingresar al panel</a>
                    <a class="btn btn-ghost" href="#areas">Conocer el sistema</a>
                </div>
            </div>
            <aside class="quote" aria-label="Visión del sistema">
                <p>«Un único sistema donde el centro registre la vida de cada alumno —su ficha, su atención, su evolución y su cuenta— con la seguridad de que cada persona ve exactamente lo que le corresponde ver.»</p>
                <span>Visión del proyecto CAI · 01.01 Visión, Objetivos y Principios</span>
            </aside>
        </div>
    </section>

    <section class="facts" aria-label="El sistema en cifras">
        <div class="wrap">
            <div class="fact"><strong>≈ 300</strong><span>alumnos atendidos por el centro</span></div>
            <div class="fact"><strong>5</strong><span>áreas integradas en una sola herramienta</span></div>
            <div class="fact"><strong>1</strong><span>historial por alumno, sin carpetas dispersas</span></div>
            <div class="fact"><strong>0</strong><span>datos clínicos visibles para áreas ajenas</span></div>
        </div>
    </section>

    <section class="block" id="areas">
        <div class="wrap">
            <div class="section-head">
                <h2>Un sistema, cinco áreas conectadas</h2>
                <p>
                    Cada área resuelve un problema real del centro y comparte la misma ficha
                    de alumno. El acceso se decide por rol y por alcance, no por costumbre.
                </p>
            </div>
            <div class="grid">
                <article class="card">
                    <div class="icon">01</div>
                    <h3>Alumnos y familias</h3>
                    <p>Fichas con código verificable, tutores, cuentas compartidas e historial de pertenencia que nunca se borra.</p>
                </article>
                <article class="card">
                    <div class="icon">02</div>
                    <h3>Agenda y asistencia</h3>
                    <p>Servicios, profesionales, inscripciones, sesiones recurrentes y asistencia registrada en minutos.</p>
                </article>
                <article class="card">
                    <div class="icon">03</div>
                    <h3>Seguimiento clínico</h3>
                    <p>Planes, objetivos y notas cifradas en reposo. Las correcciones crean una nota nueva; nada se sobrescribe.</p>
                </article>
                <article class="card">
                    <div class="icon">04</div>
                    <h3>Finanzas familiares</h3>
                    <p>Cargos por alumno, pagos de la familia, imputaciones y saldos calculados, con recibos imprimibles.</p>
                </article>
                <article class="card">
                    <div class="icon">05</div>
                    <h3>Seguridad y auditoría</h3>
                    <p>Permisos por rol, segundo factor para roles privilegiados y registro de lecturas y cambios sensibles.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="block alt">
        <div class="wrap">
            <div class="section-head">
                <h2>Por qué existe</h2>
                <p>Tres problemas concretos que el centro tiene hoy, y que el sistema viene a resolver.</p>
            </div>
            <div class="grid">
                <article class="card">
                    <div class="icon">A</div>
                    <h3>La agenda se rehace a mano</h3>
                    <p>Cada semana se reconstruye con planillas y cuadernos, con errores y horas de trabajo administrativo.</p>
                </article>
                <article class="card">
                    <div class="icon">B</div>
                    <h3>El historial está disperso</h3>
                    <p>La ficha de un alumno se reparte entre secretaría, profesional y caja. Nadie tiene la foto completa.</p>
                </article>
                <article class="card">
                    <div class="icon">C</div>
                    <h3>El cobro no es trazable</h3>
                    <p>Las planillas se desajustan y no queda registro de quién cobró qué, cuándo ni con qué autorización.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="block">
        <div class="wrap">
            <div class="section-head">
                <h2>Principios que no se negocian</h2>
                <p>Decisiones ya tomadas por el proyecto: orientan cualquier cambio futuro.</p>
            </div>
            <div class="principles">
                <div class="principle">
                    <span class="dot">1</span>
                    <div><h3>Separación de mundos</h3><p>Lo administrativo y lo clínico viven aparte: Finanzas no ve notas y el profesional no ve cobros.</p></div>
                </div>
                <div class="principle">
                    <span class="dot">2</span>
                    <div><h3>El saldo se calcula</h3><p>Nadie puede corregir un saldo a mano: siempre sale de cargos e imputaciones reales.</p></div>
                </div>
                <div class="principle">
                    <span class="dot">3</span>
                    <div><h3>Nada se borra, se anula</h3><p>Cargos, pagos, recibos y notas se anulan con motivo. El historial es un activo.</p></div>
                </div>
                <div class="principle">
                    <span class="dot">4</span>
                    <div><h3>Mínimo dato necesario</h3><p>Solo se guarda información que el centro va a usar y con un propósito declarado.</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="block" style="padding-top:0;">
        <div class="wrap">
            <div class="cta">
                <div>
                    <h2>El sistema ya está en marcha</h2>
                    <p>El acceso está restringido por rol. Ingresá con tu usuario de demostración para recorrer alumnos, agenda, notas clínicas y finanzas.</p>
                </div>
                <a class="btn" href="{{ route('filament.panel.auth.login') }}">Ir al panel</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <div>
                <strong>Sistema CAI</strong>
                Centro de Atención Inclusiva · Gestión administrativa y clínica
            </div>
            <p>
                Demostración con datos 100 % ficticios: no contiene información real de
                alumnos, familias ni profesionales. Las decisiones de negocio pendientes
                están documentadas para Dirección.
            </p>
        </div>
    </footer>
</body>
</html>
