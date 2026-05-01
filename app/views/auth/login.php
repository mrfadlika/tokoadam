<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #cf7a48;
            --primary-strong: #b55e2c;
            --accent: #0f3434;
            --ink: #172026;
            --muted: #6f7a84;
            --surface: rgba(255, 255, 255, 0.9);
            --line: rgba(23, 32, 38, 0.1);
            --shadow: 0 28px 80px rgba(10, 17, 24, 0.24);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 214, 190, 0.82), transparent 28%),
                radial-gradient(circle at bottom right, rgba(122, 181, 171, 0.35), transparent 24%),
                linear-gradient(135deg, #10282b 0%, #173c40 40%, #d27c48 100%);
        }

        .login-shell {
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: minmax(320px, 1.1fr) minmax(320px, 440px);
            border-radius: 34px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
            backdrop-filter: blur(24px);
        }

        .login-showcase {
            position: relative;
            padding: 48px;
            color: #fff9f5;
            background:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.14), transparent 18%),
                linear-gradient(150deg, rgba(7, 30, 34, 0.78) 0%, rgba(24, 70, 68, 0.7) 46%, rgba(207, 122, 72, 0.5) 100%);
        }

        .login-showcase::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -70px;
            bottom: -90px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .showcase-chip {
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .login-showcase h1 {
            margin-top: 18px;
            max-width: 11ch;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.8rem, 6vw, 4.4rem);
            line-height: 0.94;
            letter-spacing: -0.08em;
        }

        .login-showcase p {
            max-width: 54ch;
            margin-top: 18px;
            color: rgba(255, 249, 245, 0.76);
            font-size: 15px;
        }

        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 28px;
        }

        .showcase-card {
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .showcase-card strong {
            display: block;
            font-size: 24px;
            font-family: 'Space Grotesk', sans-serif;
        }

        .showcase-card span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 249, 245, 0.68);
            font-size: 13px;
        }

        .login-panel {
            padding: 42px 36px;
            background: var(--surface);
        }

        .panel-badge {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-strong));
            box-shadow: 0 18px 34px rgba(181, 94, 44, 0.26);
        }

        .panel-badge svg {
            width: 24px;
            height: 24px;
        }

        .login-panel h2 {
            margin-top: 18px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 30px;
            letter-spacing: -0.05em;
        }

        .login-panel p {
            margin-top: 8px;
            color: var(--muted);
        }

        .error-msg {
            margin-top: 20px;
            padding: 13px 15px;
            border-radius: 16px;
            color: #962e27;
            background: #fde8e6;
            border: 1px solid #f5c9c5;
            font-size: 14px;
            font-weight: 700;
        }

        .flash-msg {
            margin-top: 20px;
            padding: 13px 15px;
            border-radius: 16px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 700;
        }

        .flash-msg.flash-error {
            color: #962e27;
            background: #fde8e6;
            border-color: #f5c9c5;
        }

        .flash-msg.flash-success {
            color: #165f3e;
            background: #edf9f2;
            border-color: #caebd6;
        }

        .flash-msg.flash-info,
        .flash-msg.flash-warning {
            color: #1f4c8f;
            background: #edf4ff;
            border-color: #d2e0fb;
        }

        form { margin-top: 26px; }
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            min-height: 50px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.82);
            transition: 180ms ease;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(207, 122, 72, 0.45);
            box-shadow: 0 0 0 4px rgba(207, 122, 72, 0.12);
            background: #fff;
        }

        .btn-login {
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            color: #fff8f2;
            background: linear-gradient(135deg, var(--primary), var(--primary-strong));
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 18px 36px rgba(181, 94, 44, 0.24);
        }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
            color: var(--muted);
            font-size: 13px;
        }

        .meta-pill {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(15, 52, 52, 0.08);
            color: var(--accent);
            font-weight: 700;
        }

        .login-footer {
            margin-top: 28px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 920px) {
            .login-shell { grid-template-columns: 1fr; }
            .login-showcase { padding: 34px 28px; }
            .login-panel { padding: 34px 28px; }
        }

        @media (max-width: 560px) {
            body { padding: 14px; }
            .login-shell { border-radius: 24px; }
            .showcase-grid { grid-template-columns: 1fr; }
            .meta-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <section class="login-showcase">
            <span class="showcase-chip">Best UI Experience</span>
            <h1><?= APP_NAME ?></h1>
            <p><?= APP_TAGLINE ?> dengan tampilan baru yang lebih tegas, modern, dan nyaman dipakai untuk operasional harian.</p>
            <div class="showcase-grid">
                <div class="showcase-card">
                    <strong>Fast</strong>
                    <span>Akses transaksi, stok, dan pelanggan dari satu flow yang lebih fokus.</span>
                </div>
                <div class="showcase-card">
                    <strong>Clean</strong>
                    <span>Visual hierarchy lebih kuat supaya data penting langsung terlihat.</span>
                </div>
                <div class="showcase-card">
                    <strong>Modern</strong>
                    <span>Palet warna hangat-premium dengan feel dashboard yang lebih profesional.</span>
                </div>
                <div class="showcase-card">
                    <strong>Ready</strong>
                    <span>Tetap ringan dan responsif untuk desktop maupun mobile.</span>
                </div>
            </div>
        </section>

        <section class="login-panel">
            <div class="panel-badge" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 10.5V20h16v-9.5M3 9l2-5h14l2 5M7 20v-5h10v5M8 9a2 2 0 0 0 4 0M12 9a2 2 0 0 0 4 0M4 9a2 2 0 0 0 4 0M16 9a2 2 0 0 0 4 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg>
            </div>
            <h2>Masuk ke workspace</h2>
            <p>Silakan login untuk melanjutkan pengelolaan toko, transaksi, dan laporan.</p>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
                <div class="flash-msg flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?page=login">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-login">Masuk Sekarang</button>
            </form>

            <div class="meta-row">
                <span class="meta-pill">Version <?= APP_VERSION ?></span>
                <span>Retail system yang sudah dipoles ulang dengan tampilan premium.</span>
            </div>

            <div class="login-footer"><?= APP_NAME ?> Control Center</div>
        </section>
    </div>
</body>
</html>
