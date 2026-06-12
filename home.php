<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
$initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precio — Pesquisar produto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Background decorativo -->
<div class="bg-canvas" aria-hidden="true">
    <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <radialGradient id="g1" cx="15%" cy="30%" r="60%">
                <stop offset="0%" stop-color="#C8DDEF" stop-opacity=".35"/>
                <stop offset="100%" stop-color="#F7F6F4" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="g2" cx="85%" cy="70%" r="50%">
                <stop offset="0%" stop-color="#E3EBF6" stop-opacity=".4"/>
                <stop offset="100%" stop-color="#F7F6F4" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="g3" cx="50%" cy="100%" r="45%">
                <stop offset="0%" stop-color="#EDEBE7" stop-opacity=".7"/>
                <stop offset="100%" stop-color="#F7F6F4" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <rect width="1440" height="900" fill="#F7F6F4"/>
        <rect width="1440" height="900" fill="url(#g1)"/>
        <rect width="1440" height="900" fill="url(#g2)"/>
        <rect width="1440" height="900" fill="url(#g3)"/>

        <!-- Formas decorativas -->
        <circle cx="120"  cy="200" r="260" fill="none" stroke="#DDEAF5" stroke-width="1"/>
        <circle cx="120"  cy="200" r="190" fill="none" stroke="#DDEAF5" stroke-width="1" opacity=".6"/>
        <circle cx="1320" cy="700" r="220" fill="none" stroke="#DDEAF5" stroke-width="1"/>
        <circle cx="1320" cy="700" r="150" fill="none" stroke="#DDEAF5" stroke-width="1" opacity=".6"/>

        <!-- Bolhas sólidas sutis -->
        <circle cx="80"   cy="820" r="120" fill="#EDEBE7" opacity=".55"/>
        <circle cx="1380" cy="120" r="100" fill="#EDEBE7" opacity=".45"/>
        <circle cx="1360" cy="80"  r="45"  fill="#C8DDEF" opacity=".3"/>
        <circle cx="60"   cy="850" r="40"  fill="#C8DDEF" opacity=".25"/>

        <!-- Pontilhado decorativo -->
        <circle cx="300"  cy="100" r="3" fill="#7BA7D8" opacity=".12"/>
        <circle cx="360"  cy="140" r="2" fill="#7BA7D8" opacity=".10"/>
        <circle cx="1100" cy="780" r="3" fill="#7BA7D8" opacity=".12"/>
        <circle cx="1160" cy="820" r="2" fill="#7BA7D8" opacity=".10"/>

        <!-- Cruz sutil decorativa -->
        <line x1="700" y1="800" x2="740" y2="800" stroke="#C8DDEF" stroke-width="1" opacity=".6"/>
        <line x1="720" y1="780" x2="720" y2="820" stroke="#C8DDEF" stroke-width="1" opacity=".6"/>
        <line x1="200" y1="400" x2="240" y2="400" stroke="#C8DDEF" stroke-width="1" opacity=".4"/>
        <line x1="220" y1="380" x2="220" y2="420" stroke="#C8DDEF" stroke-width="1" opacity=".4"/>
    </svg>
</div>

<div class="page">

    <!-- ── Navbar ────────────────────────────────────────────────────────────── -->
    <nav class="navbar">
        <a href="home.php" class="navbar__logo">
            <svg width="26" height="26" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="15" stroke="#C8DDEF" stroke-width="1.5"/>
                <path d="M9 16 C9 12.13 12.13 9 16 9 C19.87 9 23 12.13 23 16" stroke="#7BA7D8" stroke-width="2" stroke-linecap="round"/>
                <path d="M16 9 L16 23" stroke="#5B6A8E" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="16" cy="16" r="3" fill="#3D4A6B"/>
            </svg>
            Precio
        </a>

        <div class="navbar__right">
            <div class="navbar__user">
                <div class="navbar__avatar"><?= htmlspecialchars($initial) ?></div>
                <span><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <button class="btn-logout" id="btn-logout">Sair</button>
        </div>
    </nav>

    <!-- ── Hero ──────────────────────────────────────────────────────────────── -->
    <main class="hero">

        <div class="hero__eyebrow">
            <span class="hero__eyebrow-dot"></span>
            Comparador de preços
        </div>

        <h1 class="hero__title">
            O que você quer<br>
            <em>comprar hoje?</em>
        </h1>
        <p class="hero__sub">
            Cole um link de produto ou faça upload de uma imagem — o Precio busca o melhor preço para você.
        </p>

        <!-- Search bar -->
        <div class="search-wrap">
            <div class="search-bar">
                <!-- Ícone lupa -->
                <span class="search-bar__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>

                <input
                    class="search-bar__input"
                    id="search-input"
                    type="text"
                    placeholder="Cole um link ou descreva o produto…"
                    autocomplete="off"
                >

                <span class="search-bar__divider"></span>

                <!-- Upload de imagem (visual apenas) -->
                <label class="search-bar__upload" for="img-upload" title="Enviar imagem">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Imagem</span>
                    <input type="file" id="img-upload" accept="image/*" style="display:none">
                </label>

                <button class="search-bar__btn">Buscar</button>
            </div>

            <div class="search-hint">
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    Link de produto
                </span>
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    Imagem do produto
                </span>
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Nome do produto
                </span>
            </div>
        </div>

        <!-- Feature pills -->
        <div class="feature-pills">
            <div class="feature-pill">
                <span class="feature-pill__icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#3D4A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                Dezenas de lojas
            </div>
            <div class="feature-pill">
                <span class="feature-pill__icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#3D4A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                Preços em tempo real
            </div>
            <div class="feature-pill">
                <span class="feature-pill__icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#3D4A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                Resultado em segundos
            </div>
            <div class="feature-pill">
                <span class="feature-pill__icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#3D4A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                100% gratuito
            </div>
        </div>

    </main>

</div>

<script>
// ── Logout ────────────────────────────────────────────────────────────────────
document.getElementById('btn-logout').addEventListener('click', async () => {
    const btn = document.getElementById('btn-logout');
    btn.textContent = 'Saindo…';
    btn.disabled = true;
    try {
        const fd = new FormData();
        fd.append('action', 'logout');
        const res  = await fetch('api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) window.location.href = json.redirect;
    } catch {
        window.location.href = 'index.php';
    }
});

// ── Preview de imagem enviada ─────────────────────────────────────────────────
document.getElementById('img-upload').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const name = this.files[0].name;
        document.getElementById('search-input').value = '';
        document.getElementById('search-input').placeholder = `📎 ${name}`;
    }
});

// ── Limpar placeholder ao digitar ─────────────────────────────────────────────
document.getElementById('search-input').addEventListener('focus', function () {
    if (this.placeholder.startsWith('📎')) {
        this.placeholder = 'Cole um link ou descreva o produto…';
    }
});
</script>
</body>
</html>
