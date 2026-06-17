<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user    = currentUser();
$initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precio — Pesquisar produto</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── Results Section ──────────────────────────────────────────────── */
        #results-section {
            display: none;
            padding: 0 48px 80px;
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
        }

        .results-meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            font-size: .9rem;
            color: var(--muted);
            padding: 18px 20px;
            border: 1px solid var(--stone);
            border-radius: var(--radius-md);
            background: rgba(255,255,255,.78);
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(12px);
        }

        .results-meta strong { color: var(--text); }

        .results-meta__title {
            display: block;
            color: var(--text);
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .results-timing {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .82rem;
            color: var(--muted);
        }

        .results-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-tile {
            background: var(--white);
            border: 1px solid var(--stone);
            border-radius: var(--radius-sm);
            padding: 12px;
            box-shadow: var(--shadow-sm);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .summary-tile:hover {
            transform: translateY(-2px);
            border-color: var(--sky-pale);
            box-shadow: var(--shadow-md);
        }

        .summary-tile__label {
            display: block;
            color: var(--muted);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 3px;
        }

        .summary-tile__value {
            color: var(--text);
            font-size: .98rem;
            font-weight: 700;
        }

        .store-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0 0 18px;
        }

        .store-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: var(--white);
            border: 1px solid var(--stone);
            color: var(--text-soft);
            font-size: .8rem;
            font-weight: 700;
            box-shadow: var(--shadow-sm);
            transition: transform .2s ease, border-color .2s ease;
        }

        .store-chip:hover {
            transform: translateY(-1px);
            border-color: var(--sky);
        }

        .store-chip__count {
            color: var(--muted);
            font-weight: 600;
        }

        .results-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Product card */
        .product-card {
            background: var(--white);
            border: 1.5px solid var(--stone);
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: stretch;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
            text-decoration: none;
            color: inherit;
            min-height: 136px;
            width: 100%;
            opacity: 0;
            animation: productIn .34s ease forwards;
            position: relative;
            isolation: isolate;
        }

        .product-card::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(90deg, rgba(123,167,216,.10), transparent 38%);
            opacity: 0;
            transition: opacity .22s ease;
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: var(--sky);
            box-shadow: var(--shadow-md);
        }

        .product-card:hover::after { opacity: 1; }

        .product-card__img {
            background: var(--off-white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .75rem;
            width: 150px;
            min-width: 150px;
            font-size: 2.5rem;
        }

        .product-card__img img {
            max-height: 112px;
            max-width: 100%;
            object-fit: contain;
            transition: transform .24s ease;
        }

        .product-card:hover .product-card__img img { transform: scale(1.045); }

        .product-card__body {
            padding: 1rem;
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
        }

        .product-card__name {
            font-size: .94rem;
            color: var(--text-soft);
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card__store {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            margin-bottom: 6px;
            padding: 3px 7px;
            border-radius: 4px;
            background: var(--off-white);
            color: var(--muted);
            border: 1px solid var(--stone);
            font-size: .72rem;
            font-weight: 600;
        }

        .product-card__best {
            display: inline-flex;
            align-items: center;
            margin-left: 6px;
            padding: 3px 7px;
            border-radius: 4px;
            background: #EEF7F3;
            color: var(--success);
            border: 1px solid #B3DCC8;
            font-size: .72rem;
            font-weight: 700;
        }

        .product-card__pricing {
            min-width: 180px;
            text-align: right;
        }

        .product-card__orig {
            font-size: .78rem;
            color: var(--muted);
            text-decoration: line-through;
            margin-bottom: 2px;
        }

        .product-card__price {
            font-size: 1.22rem;
            font-weight: 800;
            color: var(--indigo);
            letter-spacing: 0;
        }

        .product-card__badge {
            display: inline-block;
            background: #EEF7F3;
            color: var(--success);
            border: 1px solid #B3DCC8;
            border-radius: 4px;
            font-size: .7rem;
            font-weight: 600;
            padding: 1px 6px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .product-card__out {
            display: inline-block;
            background: #FEF0F3;
            color: var(--danger);
            border: 1px solid #F2C0C9;
            border-radius: 4px;
            font-size: .7rem;
            padding: 1px 6px;
            margin-top: 4px;
        }

        .product-card__cta {
            display: block;
            margin-top: 10px;
            padding: 8px;
            background: rgba(61,74,107,.05);
            border: 1px solid rgba(61,74,107,.10);
            color: var(--indigo);
            border-radius: var(--radius-sm);
            text-align: center;
            font-size: .82rem;
            font-weight: 500;
            transition: background .18s, border-color .18s;
        }

        .product-card:hover .product-card__cta {
            background: var(--sky-pale);
            border-color: var(--sky);
        }

        @keyframes productIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .search-bar {
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .search-bar:focus-within {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .search-bar__btn {
            position: relative;
            overflow: hidden;
        }

        .search-bar__btn::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.28), transparent);
            transform: translateX(-120%);
            transition: transform .45s ease;
        }

        .search-bar__btn:hover::after { transform: translateX(120%); }

        /* Loading state */
        .results-loading {
            padding: 12px 0 20px;
        }

        .loading-panel {
            background: var(--white);
            border: 1px solid var(--stone);
            border-radius: var(--radius-md);
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: var(--shadow-sm);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .loading-panel strong {
            display: block;
            color: var(--text);
            margin-bottom: 3px;
        }

        .skeleton-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .skeleton-card {
            height: 136px;
            border-radius: var(--radius-md);
            border: 1px solid var(--stone);
            background:
                linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.82), rgba(255,255,255,0)),
                linear-gradient(90deg, #F0EFEC 0 150px, #FFFFFF 150px 100%);
            background-size: 220px 100%, 100% 100%;
            background-position: -220px 0, 0 0;
            animation: shimmer 1.05s infinite linear;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--stone);
            border-top-color: var(--indigo);
            border-radius: 50%;
            animation: spin .75s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes shimmer { to { background-position: calc(100% + 220px) 0, 0 0; } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Error state */
        .results-error {
            background: #FEF0F3;
            border: 1px solid #F2C0C9;
            color: var(--danger);
            border-radius: var(--radius-sm);
            padding: 14px 18px;
            font-size: .9rem;
        }

        .results-notice {
            background: #F4F7FB;
            border: 1px solid #DDE6F3;
            color: var(--muted);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: .9rem;
        }

        /* Empty state */
        .results-empty {
            text-align: center;
            padding: 60px 0;
            color: var(--muted);
        }

        .results-empty .results-empty__icon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
        }

        .results-empty p { font-size: .95rem; }
        .results-empty strong { color: var(--text-soft); }

        /* Button loading */
        .search-bar__btn[disabled] {
            opacity: .7;
            pointer-events: none;
        }

        @media (max-width: 860px) {
            #results-section { padding: 0 20px 60px; }
            .results-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .results-meta { grid-template-columns: 1fr; }
            .results-summary { grid-template-columns: 1fr; }

            .product-card__img {
                width: 104px;
                min-width: 104px;
            }

            .product-card__body {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .product-card__pricing {
                min-width: 0;
                text-align: left;
            }
        }
    </style>
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
        <circle cx="120"  cy="200" r="260" fill="none" stroke="#DDEAF5" stroke-width="1"/>
        <circle cx="120"  cy="200" r="190" fill="none" stroke="#DDEAF5" stroke-width="1" opacity=".6"/>
        <circle cx="1320" cy="700" r="220" fill="none" stroke="#DDEAF5" stroke-width="1"/>
        <circle cx="1320" cy="700" r="150" fill="none" stroke="#DDEAF5" stroke-width="1" opacity=".6"/>
        <circle cx="80"   cy="820" r="120" fill="#EDEBE7" opacity=".55"/>
        <circle cx="1380" cy="120" r="100" fill="#EDEBE7" opacity=".45"/>
        <circle cx="1360" cy="80"  r="45"  fill="#C8DDEF" opacity=".3"/>
        <circle cx="60"   cy="850" r="40"  fill="#C8DDEF" opacity=".25"/>
        <circle cx="300"  cy="100" r="3" fill="#7BA7D8" opacity=".12"/>
        <circle cx="360"  cy="140" r="2" fill="#7BA7D8" opacity=".10"/>
        <circle cx="1100" cy="780" r="3" fill="#7BA7D8" opacity=".12"/>
        <circle cx="1160" cy="820" r="2" fill="#7BA7D8" opacity=".10"/>
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
            Digite o nome do produto e o Precio busca os melhores preços em tempo real.
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
                    placeholder="Ex: RTX 4060, Ryzen 5 7600, SSD NVMe…"
                    autocomplete="off"
                    maxlength="120"
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

                <button class="search-bar__btn" id="search-btn">Buscar</button>
            </div>

            <div class="search-hint">
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Preços em tempo real
                </span>
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Lojas Parceiras
                </span>
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Resultado em segundos
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
                Dezenas de produtos
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

    <!-- ── Results ────────────────────────────────────────────────────────────── -->
    <section id="results-section" aria-live="polite" aria-label="Resultados da busca"></section>

</div><!-- /.page -->

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
        document.getElementById('search-input').value = '';
        document.getElementById('search-input').placeholder = `📎 ${this.files[0].name}`;
    }
});

document.getElementById('search-input').addEventListener('focus', function () {
    if (this.placeholder.startsWith('📎')) {
        this.placeholder = 'Ex: RTX 4060, Ryzen 5 7600, SSD NVMe…';
    }
});

// ── Search ────────────────────────────────────────────────────────────────────
const searchInput    = document.getElementById('search-input');
const searchBtn      = document.getElementById('search-btn');
const resultsSection = document.getElementById('results-section');

// Formata valor em BRL: 1234.5 → "R$ 1.234,50"
function formatBRL(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(Number(valor));
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function mostrarLoading() {
    resultsSection.style.display = 'block';
    resultsSection.innerHTML = `
        <div class="results-loading">
            <div class="loading-panel">
                <div>
                    <strong>Comparando lojas em paralelo</strong>
                    <span>Pichau, KaBuM!, Amazon e Casas Bahia estão sendo consultadas agora.</span>
                </div>
                <div class="spinner"></div>
            </div>
            <div class="skeleton-list">
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
            </div>
        </div>
    `;
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function mostrarErro(msg) {
    resultsSection.style.display = 'block';
    resultsSection.innerHTML = `<div class="results-error">⚠️ ${escHtml(msg)}</div>`;
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function mostrarResultados(data, tempo) {
    const produtos = data.produtos || [];
    const total    = data.total    || 0;
    const pagina = data.pagina_atual || 1;
    const totalPaginas = data.total_paginas || 1;
    const sitesDisponiveis = data.sites_disponiveis || [];

    if (produtos.length === 0) {
        resultsSection.style.display = 'block';
        resultsSection.innerHTML = `
            <div class="results-empty">
                <span class="results-empty__icon">🔎</span>
                <p>Nenhum produto encontrado. Tente outro termo de busca.</p>
            </div>
        `;
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    const termoExibido = escHtml(searchInput.value.trim());
    const melhorPreco = produtos.reduce((menor, p) => {
        if (p.preco === null || p.preco === undefined) return menor;
        const preco = Number(p.preco);
        return menor === null || preco < menor ? preco : menor;
    }, null);
    const lojas = data.lojas || {};
    const lojasHtml = Object.entries(lojas)
        .sort((a, b) => b[1] - a[1])
        .map(([loja, qtd]) => `<span class="store-chip">${escHtml(loja)} <span class="store-chip__count">${qtd}</span></span>`)
        .join('');
    const menorPrecoHtml = melhorPreco !== null ? formatBRL(melhorPreco) : '-';
    const fonte = data.fonte ? escHtml(data.fonte) : 'lojas disponíveis';

    const ordemAtual = new URLSearchParams(window.location.search).get('ordem') || 'preco_asc';
    const siteFiltro = new URLSearchParams(window.location.search).get('site') || '';

    let html = `
        <div class="results-meta">
            <span>
                <span class="results-meta__title">${total} oferta${total !== 1 ? 's' : ''} para "${termoExibido}"</span>
                Ordenadas por menor preço em ${fonte}
            </span>
        </div>
        <div class="results-summary">
            <div class="summary-tile">
                <span class="summary-tile__label">Menor preço</span>
                <span class="summary-tile__value">${menorPrecoHtml}</span>
            </div>
            <div class="summary-tile">
                <span class="summary-tile__label">Ofertas</span>
                <span class="summary-tile__value">${total}</span>
            </div>
            <div class="summary-tile">
                <span class="summary-tile__label">Lojas</span>
                <span class="summary-tile__value">${Object.keys(lojas).length || '-'}</span>
            </div>
            <div class="summary-tile">
                <span class="summary-tile__label">Tempo</span>
                <span class="summary-tile__value">${tempo}s</span>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
            <div>
                <label style="font-weight: 600; color: var(--text-soft); margin-right: 10px;">Ordenar por:</label>
                <select id="ordenar-select" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); font-size: 14px;">
                    <option value="preco_asc" ${ordemAtual === 'preco_asc' ? 'selected' : ''}>💰 Menor Preço</option>
                    <option value="preco_desc" ${ordemAtual === 'preco_desc' ? 'selected' : ''}>💰 Maior Preço</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 600; color: var(--text-soft); margin-right: 10px;">Filtrar Lojas:</label>
                <select id="filtro-lojas" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); font-size: 14px;">
                    <option value="">Todas as lojas (${sitesDisponiveis.length})</option>
                    ${sitesDisponiveis.map(s => `<option value="${escHtml(s)}" ${siteFiltro === s ? 'selected' : ''}>${escHtml(s)}</option>`).join('')}
                </select>
            </div>
            <div style="margin-left: auto; color: var(--text-soft); font-size: 14px;">
                Página <strong>${pagina}</strong> de <strong>${totalPaginas}</strong>
            </div>
        </div>

        ${lojasHtml ? `<div class="store-chips">${lojasHtml}</div>` : ''}
        ${data.aviso ? `<div class="results-notice">${escHtml(data.aviso)}</div>` : ''}
        <div class="results-grid">
    `;

    for (const [index, p] of produtos.entries()) {
        const preco     = p.preco !== null && p.preco !== undefined ? formatBRL(p.preco) : null;
        const precoOrig = p.preco_orig ? formatBRL(p.preco_orig) : null;
        const isBest    = melhorPreco !== null && Number(p.preco) === melhorPreco;
        const imgHtml   = p.imagem
            ? `<img src="${escHtml(p.imagem)}" alt="${escHtml(p.nome)}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
               <span style="display:none;font-size:2.5rem">📦</span>`
            : `<span style="font-size:2.5rem">📦</span>`;

        html += `
            <a class="product-card" style="animation-delay:${Math.min(index * 22, 420)}ms" href="${escHtml(p.url)}" target="_blank" rel="noopener noreferrer">
                <div class="product-card__img">${imgHtml}</div>
                <div class="product-card__body">
                    <div>
                        ${p.site ? `<span class="product-card__store">${escHtml(p.site)}</span>` : ''}
                        ${isBest ? `<span class="product-card__best">Melhor preço</span>` : ''}
                        <div class="product-card__name">${escHtml(p.nome)}</div>
                    </div>
                    <div class="product-card__pricing">
                        ${precoOrig ? `<div class="product-card__orig">${precoOrig}</div>` : ''}
                        <div>
                            <span class="product-card__price">${preco || 'Ver preço no site'}</span>
                            ${p.desconto ? `<span class="product-card__badge">-${p.desconto}%</span>` : ''}
                        </div>
                        ${!p.em_estoque ? `<span class="product-card__out">Sem estoque</span>` : ''}
                    </div>
                    <span class="product-card__cta">Ver na loja →</span>
                </div>
            </a>
        `;
    }

    html += '</div>';

    if (totalPaginas > 1) {
        html += '<div style="display: flex; gap: 10px; justify-content: center; margin: 40px 0; flex-wrap: wrap;">';

        if (pagina > 1) {
            html += `<button onclick="irParaPagina(1)" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); background: var(--white); cursor: pointer;">⬅️ Primeira</button>`;
            html += `<button onclick="irParaPagina(${pagina - 1})" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); background: var(--white); cursor: pointer;">◀️ Anterior</button>`;
        }

        const inicio = Math.max(1, pagina - 2);
        const fim = Math.min(totalPaginas, pagina + 2);

        for (let i = inicio; i <= fim; i++) {
            if (i === pagina) {
                html += `<span style="padding: 8px 12px; border: 1px solid var(--indigo); border-radius: var(--radius-md); background: var(--indigo); color: var(--white);">${i}</span>`;
            } else {
                html += `<button onclick="irParaPagina(${i})" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); background: var(--white); cursor: pointer;">${i}</button>`;
            }
        }

        if (pagina < totalPaginas) {
            html += `<button onclick="irParaPagina(${pagina + 1})" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); background: var(--white); cursor: pointer;">Próxima ▶️</button>`;
            html += `<button onclick="irParaPagina(${totalPaginas})" style="padding: 8px 12px; border: 1px solid var(--stone); border-radius: var(--radius-md); background: var(--white); cursor: pointer;">Última ⬅️</button>`;
        }

        html += '</div>';
    }

    resultsSection.style.display = 'block';
    resultsSection.innerHTML = html;

    document.getElementById('ordenar-select').addEventListener('change', (e) => {
        irParaPagina(1, e.target.value, document.getElementById('filtro-lojas').value);
    });

    document.getElementById('filtro-lojas').addEventListener('change', (e) => {
        irParaPagina(1, document.getElementById('ordenar-select').value, e.target.value);
    });

    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

let ultimaBusca = '';

async function realizarBusca(pagina = 1, ordem = 'preco_asc', sites = '') {
    const termo = searchInput.value.trim();

    if (termo.length < 2) {
        mostrarErro('Digite pelo menos 2 caracteres para pesquisar.');
        return;
    }

    ultimaBusca = termo;
    searchBtn.disabled    = true;
    searchBtn.textContent = 'Buscando…';
    mostrarLoading();

    try {
        const fd = new FormData();
        fd.append('action', 'search');
        fd.append('busca', termo);
        fd.append('pagina', pagina);
        fd.append('ordem', ordem);
        fd.append('sites', sites);

        const res  = await fetch('api.php', { method: 'POST', body: fd });
        const json = await res.json();

        if (json.success) {
            mostrarResultados(json.data, json.tempo);
        } else {
            mostrarErro(json.message || 'Erro ao realizar a busca.');
        }
    } catch (err) {
        mostrarErro('Erro de comunicação. Verifique sua conexão e tente novamente.');
    } finally {
        searchBtn.disabled    = false;
        searchBtn.textContent = 'Buscar';
    }
}

function irParaPagina(pagina, ordem = null, sites = null) {
    if (ordem === null) ordem = document.getElementById('ordenar-select')?.value || 'preco_asc';
    if (sites === null) sites = document.getElementById('filtro-lojas')?.value || '';
    realizarBusca(pagina, ordem, sites);
}

searchBtn.addEventListener('click', realizarBusca);

searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') realizarBusca();
});
</script>
</body>
</html>
