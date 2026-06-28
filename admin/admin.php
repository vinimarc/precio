<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$user = currentUser() ?? ['name' => 'Administrador', 'email' => 'admin@precio.local'];
$adminName = $user['name'] ?: 'Administrador';
$adminInitials = mb_strtoupper(mb_substr($adminName, 0, 1));

$stats = [
    ['label' => 'Pesquisas Hoje', 'value' => '1.284', 'growth' => '+12,8%', 'trend' => 'up', 'icon' => 'bi-search'],
    ['label' => 'Pesquisas Totais', 'value' => '482.910', 'growth' => '+18,4%', 'trend' => 'up', 'icon' => 'bi-graph-up-arrow'],
    ['label' => 'Usuários', 'value' => '24.891', 'growth' => '+7,2%', 'trend' => 'up', 'icon' => 'bi-people'],
    ['label' => 'Usuários Ativos', 'value' => '8.472', 'growth' => '+4,1%', 'trend' => 'up', 'icon' => 'bi-person-check'],
    ['label' => 'Produtos Indexados', 'value' => '1,8 mi', 'growth' => '+21,6%', 'trend' => 'up', 'icon' => 'bi-box-seam'],
    ['label' => 'Lojas Monitoradas', 'value' => '36', 'growth' => '+3', 'trend' => 'up', 'icon' => 'bi-shop'],
    ['label' => 'Scrapers Online', 'value' => '31/36', 'growth' => '-2', 'trend' => 'down', 'icon' => 'bi-cpu'],
    ['label' => 'Tempo Médio de Resposta', 'value' => '842 ms', 'growth' => '-9,5%', 'trend' => 'up', 'icon' => 'bi-lightning-charge'],
];

$scrapers = [
    ['store' => 'Amazon Brasil', 'status' => 'Online', 'last_run' => '25/06/2026 14:42', 'response' => '612 ms', 'products' => 18420],
    ['store' => 'KaBuM!', 'status' => 'Online', 'last_run' => '25/06/2026 14:39', 'response' => '738 ms', 'products' => 12984],
    ['store' => 'Pichau', 'status' => 'Atenção', 'last_run' => '25/06/2026 14:31', 'response' => '1.840 ms', 'products' => 8391],
    ['store' => 'Magazine Luiza', 'status' => 'Online', 'last_run' => '25/06/2026 14:28', 'response' => '821 ms', 'products' => 15302],
    ['store' => 'Mercado Livre', 'status' => 'Offline', 'last_run' => '25/06/2026 13:57', 'response' => 'Timeout', 'products' => 0],
    ['store' => 'TerabyteShop', 'status' => 'Online', 'last_run' => '25/06/2026 14:23', 'response' => '690 ms', 'products' => 6430],
];

$searchHistory = [
    ['term' => 'iphone 15 pro max', 'user' => 'Ana Martins', 'date' => '25/06/2026 14:45', 'results' => 128],
    ['term' => 'rtx 4070 super', 'user' => 'Caio Lima', 'date' => '25/06/2026 14:40', 'results' => 76],
    ['term' => 'cadeira gamer', 'user' => 'Marina Lopes', 'date' => '25/06/2026 14:35', 'results' => 214],
    ['term' => 'monitor 144hz', 'user' => 'Felipe Rocha', 'date' => '25/06/2026 14:28', 'results' => 91],
    ['term' => 'notebook i7', 'user' => 'Bianca Nunes', 'date' => '25/06/2026 14:20', 'results' => 163],
    ['term' => 'ssd nvme 1tb', 'user' => 'Diego Alves', 'date' => '25/06/2026 14:12', 'results' => 119],
    ['term' => 'placa mae am5', 'user' => 'Rafael Costa', 'date' => '25/06/2026 14:07', 'results' => 57],
    ['term' => 'air fryer', 'user' => 'Juliana Reis', 'date' => '25/06/2026 13:58', 'results' => 184],
    ['term' => 'xbox series s', 'user' => 'Lucas Duarte', 'date' => '25/06/2026 13:49', 'results' => 36],
    ['term' => 'kindle', 'user' => 'Patricia Melo', 'date' => '25/06/2026 13:33', 'results' => 42],
];

$products = [
    ['name' => 'Notebook Dell Inspiron 15 i7', 'category' => 'Notebooks', 'price' => 'R$ 4.899,90', 'store' => 'Amazon Brasil', 'updated' => '25/06/2026 14:30', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=NB'],
    ['name' => 'Placa de Video RTX 4070 Super', 'category' => 'Hardware', 'price' => 'R$ 4.299,99', 'store' => 'KaBuM!', 'updated' => '25/06/2026 14:24', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=GPU'],
    ['name' => 'Monitor Gamer LG UltraGear 27"', 'category' => 'Monitores', 'price' => 'R$ 1.349,00', 'store' => 'Pichau', 'updated' => '25/06/2026 14:20', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=TV'],
    ['name' => 'SSD Kingston NV2 1TB NVMe', 'category' => 'Armazenamento', 'price' => 'R$ 419,90', 'store' => 'TerabyteShop', 'updated' => '25/06/2026 14:16', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=SSD'],
    ['name' => 'iPhone 15 Pro Max 256GB', 'category' => 'Smartphones', 'price' => 'R$ 7.899,00', 'store' => 'Magazine Luiza', 'updated' => '25/06/2026 14:10', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=IP'],
    ['name' => 'Cadeira Gamer ThunderX3', 'category' => 'Moveis', 'price' => 'R$ 899,99', 'store' => 'Mercado Livre', 'updated' => '25/06/2026 13:58', 'image' => 'https://placehold.co/96x96/f4f7fb/3d4a6b?text=CG'],
];

$users = [
    ['name' => 'Ana Martins', 'email' => 'ana@email.com', 'created' => '02/05/2026', 'status' => 'Ativo', 'type' => 'Cliente'],
    ['name' => 'Caio Lima', 'email' => 'caio@email.com', 'created' => '11/05/2026', 'status' => 'Ativo', 'type' => 'Cliente'],
    ['name' => 'Marina Lopes', 'email' => 'marina@email.com', 'created' => '18/05/2026', 'status' => 'Pendente', 'type' => 'Cliente'],
    ['name' => 'Felipe Rocha', 'email' => 'felipe@email.com', 'created' => '28/05/2026', 'status' => 'Suspenso', 'type' => 'Cliente'],
    ['name' => 'Bianca Nunes', 'email' => 'bianca@email.com', 'created' => '03/06/2026', 'status' => 'Ativo', 'type' => 'Admin'],
];

$logs = [
    ['level' => 'INFO', 'date' => '25/06/2026', 'time' => '14:45:09', 'source' => 'scraper.amazon', 'message' => 'Coleta concluida com 18.420 produtos processados.'],
    ['level' => 'WARNING', 'date' => '25/06/2026', 'time' => '14:41:22', 'source' => 'scraper.pichau', 'message' => 'Latencia acima do limite configurado para tres execucoes consecutivas.'],
    ['level' => 'ERROR', 'date' => '25/06/2026', 'time' => '14:03:51', 'source' => 'scraper.mercadolivre', 'message' => 'Timeout ao aguardar resposta do endpoint de busca.'],
    ['level' => 'CRITICAL', 'date' => '25/06/2026', 'time' => '13:57:10', 'source' => 'queue.worker', 'message' => 'Fila de reprocessamento atingiu 92% da capacidade.'],
    ['level' => 'INFO', 'date' => '25/06/2026', 'time' => '13:42:18', 'source' => 'auth', 'message' => 'Novo login administrativo autenticado com sucesso.'],
];

$notifications = [
    ['id' => 'scraper-ml', 'severity' => 'danger', 'title' => 'Mercado Livre offline', 'message' => 'Ultima coleta falhou por timeout ha 48 minutos.', 'time' => '14:45'],
    ['id' => 'queue-high', 'severity' => 'warning', 'title' => 'Fila em 92%', 'message' => 'Worker de reprocessamento precisa de atencao.', 'time' => '14:31'],
    ['id' => 'report-ready', 'severity' => 'success', 'title' => 'Relatorio diario pronto', 'message' => 'Resumo de pesquisas e cliques ja foi gerado.', 'time' => '13:58'],
    ['id' => 'api-budget', 'severity' => 'warning', 'title' => 'API SerpAPI em 81%', 'message' => 'Consumo mensal acima do previsto para junho.', 'time' => '12:20'],
];

$categories = [
    ['name' => 'Smartphones', 'products' => 184320, 'avg_price' => 'R$ 2.418,30', 'growth' => '+14,2%', 'top_store' => 'Magazine Luiza'],
    ['name' => 'Hardware', 'products' => 128940, 'avg_price' => 'R$ 1.128,70', 'growth' => '+21,8%', 'top_store' => 'KaBuM!'],
    ['name' => 'Notebooks', 'products' => 76310, 'avg_price' => 'R$ 3.894,10', 'growth' => '+9,6%', 'top_store' => 'Amazon Brasil'],
    ['name' => 'Monitores', 'products' => 48220, 'avg_price' => 'R$ 1.027,40', 'growth' => '+7,9%', 'top_store' => 'Pichau'],
    ['name' => 'Casa Inteligente', 'products' => 31880, 'avg_price' => 'R$ 286,90', 'growth' => '+18,1%', 'top_store' => 'Mercado Livre'],
];

$duplicates = [
    ['product' => 'SSD Kingston NV2 1TB NVMe', 'matches' => 7, 'stores' => 'Amazon, KaBuM!, Terabyte', 'confidence' => '96%'],
    ['product' => 'Monitor LG UltraGear 27 144Hz', 'matches' => 5, 'stores' => 'Pichau, Magalu, Amazon', 'confidence' => '93%'],
    ['product' => 'iPhone 15 Pro Max 256GB', 'matches' => 9, 'stores' => 'Magalu, Amazon, Mercado Livre', 'confidence' => '98%'],
    ['product' => 'Cadeira Gamer ThunderX3', 'matches' => 4, 'stores' => 'Mercado Livre, Pichau', 'confidence' => '89%'],
];

$sites = [
    ['store' => 'Amazon Brasil', 'base_url' => 'amazon.com.br', 'robots' => 'Permitido', 'rate_limit' => '90 req/min', 'success' => '99,2%'],
    ['store' => 'KaBuM!', 'base_url' => 'kabum.com.br', 'robots' => 'Permitido', 'rate_limit' => '60 req/min', 'success' => '98,6%'],
    ['store' => 'Pichau', 'base_url' => 'pichau.com.br', 'robots' => 'Parcial', 'rate_limit' => '45 req/min', 'success' => '94,1%'],
    ['store' => 'Mercado Livre', 'base_url' => 'mercadolivre.com.br', 'robots' => 'API preferencial', 'rate_limit' => '120 req/min', 'success' => '87,4%'],
];

$permissions = [
    ['role' => 'Administrador', 'users' => 3, 'scope' => 'Acesso total', 'risk' => 'Alto'],
    ['role' => 'Analista', 'users' => 8, 'scope' => 'Relatorios, produtos e logs', 'risk' => 'Medio'],
    ['role' => 'Operador', 'users' => 5, 'scope' => 'Scrapers e monitoramento', 'risk' => 'Medio'],
    ['role' => 'Suporte', 'users' => 12, 'scope' => 'Usuarios e historico', 'risk' => 'Baixo'],
];

$reports = [
    ['name' => 'Resumo executivo diario', 'period' => '25/06/2026', 'owner' => 'Analytics', 'status' => 'Pronto'],
    ['name' => 'Produtos sem oferta valida', 'period' => 'Ultimos 7 dias', 'owner' => 'Catalogo', 'status' => 'Processando'],
    ['name' => 'Performance dos scrapers', 'period' => 'Junho/2026', 'owner' => 'Operacao', 'status' => 'Pronto'],
    ['name' => 'Conversao por loja', 'period' => 'Junho/2026', 'owner' => 'Growth', 'status' => 'Agendado'],
];

$apiIntegrations = [
    ['name' => 'Proxy Rotativo', 'provider' => 'Bright Data', 'status' => 'Ativo', 'usage' => '68%'],
    ['name' => 'SERP Enrichment', 'provider' => 'SerpAPI', 'status' => 'Ativo', 'usage' => '81%'],
    ['name' => 'Alertas', 'provider' => 'Slack Webhook', 'status' => 'Ativo', 'usage' => '24%'],
    ['name' => 'Storage de imagens', 'provider' => 'S3 compativel', 'status' => 'Ativo', 'usage' => '43%'],
];

$chartData = [
    'searchesByDay' => [
        'labels' => ['19 Jun', '20 Jun', '21 Jun', '22 Jun', '23 Jun', '24 Jun', '25 Jun'],
        'values' => [812, 940, 1104, 986, 1288, 1416, 1640],
    ],
    'userGrowth' => [
        'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        'values' => [4200, 6100, 8400, 11900, 16800, 24891],
    ],
    'topProducts' => [
        'labels' => ['iPhone', 'RTX 4070', 'Notebook i7', 'SSD 1TB', 'Monitor 144Hz'],
        'values' => [32, 24, 18, 14, 12],
    ],
    'storesAccess' => [
        'labels' => ['Amazon', 'KaBuM!', 'Pichau', 'Magalu', 'Mercado Livre'],
        'values' => [46, 32, 28, 21, 16],
    ],
    'noResult' => [
        'labels' => ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'],
        'values' => [18, 22, 15, 31, 27, 19, 12],
    ],
    'storeClicks' => [
        'labels' => ['Amazon', 'KaBuM!', 'Pichau', 'Magalu', 'Terabyte'],
        'values' => [1084, 884, 731, 562, 444],
    ],
];

function badgeClass(string $status): string {
    return match ($status) {
        'Online', 'Ativo', 'INFO' => 'success',
        'Atenção', 'Pendente', 'WARNING' => 'warning',
        'Offline', 'Suspenso', 'ERROR' => 'danger',
        'CRITICAL' => 'critical',
        default => 'neutral',
    };
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precio Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-shell" data-sidebar="expanded">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <a href="#dashboard" class="brand-mark" data-section-link="dashboard" aria-label="Precio Admin">
                <span class="brand-icon"><i class="bi bi-compass"></i></span>
                <span class="brand-copy">
                    <strong>Precio</strong>
                    <small>Admin Suite</small>
                </span>
            </a>
            <button class="icon-btn sidebar-close d-lg-none" type="button" data-sidebar-close aria-label="Fechar menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="sidebar-nav" aria-label="Menu administrativo">
            <a class="nav-link active" href="#dashboard" data-section-link="dashboard"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuPesquisas" type="button" aria-expanded="true">
                <i class="bi bi-search"></i><span>Pesquisas</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuPesquisas">
                <a href="#historico" data-section-link="historico">Histórico</a>
                <a href="#tendencias" data-section-link="tendencias">Tendências</a>
            </div>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuProdutos" type="button" aria-expanded="true">
                <i class="bi bi-box-seam"></i><span>Produtos</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuProdutos">
                <a href="#produtos" data-section-link="produtos">Todos os Produtos</a>
                <a href="#categorias" data-section-link="categorias">Categorias</a>
                <a href="#duplicados" data-section-link="duplicados">Duplicados</a>
            </div>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuScrapers" type="button" aria-expanded="true">
                <i class="bi bi-cpu"></i><span>Scrapers</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuScrapers">
                <a href="#scrapers" data-section-link="scrapers">Monitoramento</a>
                <a href="#sites" data-section-link="sites">Sites</a>
                <a href="#logs" data-section-link="logs">Logs</a>
            </div>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuUsuarios" type="button" aria-expanded="true">
                <i class="bi bi-people"></i><span>Usuários</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuUsuarios">
                <a href="#usuarios" data-section-link="usuarios">Lista</a>
                <a href="#permissoes" data-section-link="permissoes">Permissões</a>
            </div>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuAnalytics" type="button" aria-expanded="true">
                <i class="bi bi-bar-chart"></i><span>Analytics</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuAnalytics">
                <a href="#relatorios" data-section-link="relatorios">Relatórios</a>
                <a href="#analytics" data-section-link="analytics">Estatísticas</a>
            </div>

            <button class="nav-link nav-group-toggle" data-bs-toggle="collapse" data-bs-target="#menuConfig" type="button" aria-expanded="true">
                <i class="bi bi-sliders"></i><span>Configurações</span><i class="bi bi-chevron-down nav-chevron"></i>
            </button>
            <div class="collapse show nav-submenu" id="menuConfig">
                <a href="#configuracoes" data-section-link="configuracoes">Sistema</a>
                <a href="#apis" data-section-link="apis">APIs</a>
                <a href="#seguranca" data-section-link="seguranca">Segurança</a>
            </div>

            <a class="nav-link" href="#logs" data-section-link="logs"><i class="bi bi-terminal"></i><span>Logs</span></a>
        </nav>
    </aside>

    <div class="sidebar-backdrop" data-sidebar-close></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="icon-btn" type="button" id="sidebarToggle" aria-label="Alternar sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
                <div class="global-search">
                    <i class="bi bi-search"></i>
                    <input type="search" id="globalSearch" placeholder="Buscar em produtos, usuários, logs..." autocomplete="off">
                    <kbd>/</kbd>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="icon-btn" type="button" id="themeToggle" aria-label="Alternar dark mode"><i class="bi bi-moon-stars"></i></button>
                <button class="icon-btn notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificações">
                    <i class="bi bi-bell"></i><span class="notification-count" id="notificationCount"><?= count($notifications) ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu" id="notificationMenu">
                    <div class="notification-head">
                        <strong>Notificações</strong>
                        <button type="button" data-clear-notifications>Limpar lidas</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <?php foreach ($notifications as $notification): ?>
                            <button class="notification-item" type="button" data-notification-id="<?= htmlspecialchars($notification['id']) ?>">
                                <span class="notification-dot notification-<?= htmlspecialchars($notification['severity']) ?>"></span>
                                <span>
                                    <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                    <small><?= htmlspecialchars($notification['message']) ?></small>
                                    <em><?= htmlspecialchars($notification['time']) ?></em>
                                </span>
                                <i class="bi bi-check2" aria-hidden="true"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="notification-empty" id="notificationEmpty" hidden>
                        <i class="bi bi-check2-circle"></i>
                        <span>Nenhuma notificação pendente.</span>
                    </div>
                </div>
                <div class="admin-profile dropdown">
                    <button class="profile-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="profile-avatar"><?= htmlspecialchars($adminInitials) ?></span>
                        <span class="profile-copy">
                            <strong><?= htmlspecialchars($adminName) ?></strong>
                            <small><?= htmlspecialchars($user['email'] ?? 'admin@precio.local') ?></small>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="../home.php">Voltar ao site</a>
                        <a class="dropdown-item" href="../logout.php">Sair</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <section class="content-section active" id="dashboard" data-section>
                <div class="page-heading">
                    <div>
                        <span class="eyebrow">Visão geral</span>
                        <h1>Dashboard Administrativo</h1>
                        <p>Monitoramento operacional do comparador de preços, scrapers, usuários e catálogo.</p>
                    </div>
                    <div class="heading-actions">
                        <button class="btn btn-light btn-sm" type="button" data-toast="Relatório exportado com sucesso."><i class="bi bi-download"></i> Exportar</button>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#runScraperModal"><i class="bi bi-play-fill"></i> Executar scraper</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <?php foreach ($stats as $stat): ?>
                        <article class="metric-card">
                            <div class="metric-icon"><i class="bi <?= htmlspecialchars($stat['icon']) ?>"></i></div>
                            <div>
                                <span><?= htmlspecialchars($stat['label']) ?></span>
                                <strong><?= htmlspecialchars($stat['value']) ?></strong>
                            </div>
                            <em class="<?= $stat['trend'] === 'down' ? 'trend-down' : 'trend-up' ?>">
                                <i class="bi <?= $stat['trend'] === 'down' ? 'bi-arrow-down-right' : 'bi-arrow-up-right' ?>"></i>
                                <?= htmlspecialchars($stat['growth']) ?>
                            </em>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="dashboard-grid">
                    <article class="panel panel-wide">
                        <div class="panel-header">
                            <div><h2>Pesquisas por Dia</h2><p>Volume dos últimos 7 dias</p></div>
                            <span class="panel-badge">Tempo real</span>
                        </div>
                        <div class="chart-wrap"><canvas id="searchesLineChart"></canvas></div>
                    </article>
                    <article class="panel">
                        <div class="panel-header">
                            <div><h2>Crescimento de Usuários</h2><p>Base acumulada</p></div>
                        </div>
                        <div class="chart-wrap"><canvas id="usersBarChart"></canvas></div>
                    </article>
                    <article class="panel">
                        <div class="panel-header">
                            <div><h2>Produtos Mais Pesquisados</h2><p>Distribuição atual</p></div>
                        </div>
                        <div class="chart-wrap"><canvas id="productsDoughnutChart"></canvas></div>
                    </article>
                </div>
            </section>

            <section class="content-section" id="scrapers" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Operação</span><h1>Monitoramento de Scrapers</h1><p>Status dos coletores e ações operacionais.</p></div>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Loja</th><th>Status</th><th>Última Execução</th><th>Tempo de Resposta</th><th>Produtos Encontrados</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($scrapers as $scraper): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($scraper['store']) ?></strong></td>
                                    <td><span class="status-badge status-<?= badgeClass($scraper['status']) ?>"><i></i><?= htmlspecialchars($scraper['status']) ?></span></td>
                                    <td><?= htmlspecialchars($scraper['last_run']) ?></td>
                                    <td><?= htmlspecialchars($scraper['response']) ?></td>
                                    <td><?= number_format($scraper['products'], 0, ',', '.') ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-soft" data-toast="Execução enviada para a fila."><i class="bi bi-play"></i> Executar</button>
                                        <button class="btn btn-sm btn-soft" data-toast="Worker reiniciado."><i class="bi bi-arrow-clockwise"></i> Reiniciar</button>
                                        <button class="btn btn-sm btn-soft" data-section-link="logs"><i class="bi bi-terminal"></i> Logs</button>
                                        <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#scraperConfigModal"><i class="bi bi-gear"></i> Configurar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="sites" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Scrapers</span><h1>Sites Monitorados</h1><p>Configuração operacional por loja, limites e taxa de sucesso.</p></div>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#scraperConfigModal"><i class="bi bi-plus-lg"></i> Novo site</button>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Loja</th><th>Domínio</th><th>Robots/API</th><th>Rate limit</th><th>Sucesso 7d</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($sites as $site): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($site['store']) ?></strong></td>
                                    <td><?= htmlspecialchars($site['base_url']) ?></td>
                                    <td><?= htmlspecialchars($site['robots']) ?></td>
                                    <td><?= htmlspecialchars($site['rate_limit']) ?></td>
                                    <td><span class="status-badge status-success"><i></i><?= htmlspecialchars($site['success']) ?></span></td>
                                    <td class="table-actions"><button class="btn btn-sm btn-soft">Editar</button><button class="btn btn-sm btn-soft">Testar</button><button class="btn btn-sm btn-soft-danger">Pausar</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="historico" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Pesquisas</span><h1>Histórico de Pesquisas</h1><p>Consulta, ordenação, filtros e paginação client-side com dados simulados.</p></div>
                </div>
                <article class="panel">
                    <div class="toolbar">
                        <input class="form-control" type="search" data-table-search="searchesTable" placeholder="Buscar termo ou usuário">
                        <select class="form-select" data-table-filter="searchesTable" data-column="results">
                            <option value="">Todos os volumes</option>
                            <option value="high">Mais de 100 resultados</option>
                            <option value="low">Até 100 resultados</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table sortable-table" id="searchesTable" data-page-size="5">
                            <thead><tr><th data-sort="term">Termo</th><th data-sort="user">Usuário</th><th data-sort="date">Data</th><th data-sort="results">Quantidade de Resultados</th></tr></thead>
                            <tbody>
                            <?php foreach ($searchHistory as $row): ?>
                                <tr data-results="<?= (int) $row['results'] ?>">
                                    <td><strong><?= htmlspecialchars($row['term']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['user']) ?></td>
                                    <td><?= htmlspecialchars($row['date']) ?></td>
                                    <td><?= (int) $row['results'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer" data-pagination-for="searchesTable"></div>
                </article>
            </section>

            <section class="content-section" id="tendencias" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Pesquisas</span><h1>Tendências</h1><p>Termos com maior aceleração nas últimas 24 horas.</p></div>
                </div>
                <div class="insight-grid">
                    <article class="panel insight-card"><span>Alta forte</span><strong>rtx 4070 super</strong><p>+38% nas buscas após queda média de R$ 260,00.</p></article>
                    <article class="panel insight-card"><span>Oportunidade</span><strong>air fryer 5l</strong><p>22% das pesquisas retornam poucas ofertas válidas.</p></article>
                    <article class="panel insight-card"><span>Sem resultado</span><strong>steam deck oled</strong><p>Termo recorrente sem cobertura confiável em lojas nacionais.</p></article>
                </div>
                <article class="panel mt-3">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Termo</th><th>Buscas 24h</th><th>Variação</th><th>Ticket Médio</th><th>Melhor Loja</th></tr></thead>
                            <tbody>
                                <tr><td><strong>iphone 15 128gb</strong></td><td>4.218</td><td><span class="trend-up">+24,8%</span></td><td>R$ 4.719,00</td><td>Magazine Luiza</td></tr>
                                <tr><td><strong>ssd nvme 1tb</strong></td><td>3.684</td><td><span class="trend-up">+19,1%</span></td><td>R$ 398,40</td><td>KaBuM!</td></tr>
                                <tr><td><strong>monitor 144hz</strong></td><td>2.930</td><td><span class="trend-up">+12,7%</span></td><td>R$ 1.084,20</td><td>Pichau</td></tr>
                                <tr><td><strong>kindle 11 geração</strong></td><td>1.104</td><td><span class="trend-down">-3,4%</span></td><td>R$ 431,90</td><td>Amazon Brasil</td></tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="produtos" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Catálogo</span><h1>Gestão de Produtos</h1><p>Produtos indexados pelos scrapers e prontos para revisão.</p></div>
                    <button class="btn btn-primary btn-sm" type="button" data-toast="Sincronização iniciada."><i class="bi bi-arrow-repeat"></i> Atualizar catálogo</button>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle sortable-table" id="productsTable" data-page-size="6">
                            <thead><tr><th>Imagem</th><th data-sort="name">Nome</th><th data-sort="category">Categoria</th><th data-sort="price">Preço</th><th data-sort="store">Loja</th><th data-sort="updated">Última Atualização</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><img class="product-thumb" src="<?= htmlspecialchars($product['image']) ?>" alt=""></td>
                                    <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($product['category']) ?></td>
                                    <td><?= htmlspecialchars($product['price']) ?></td>
                                    <td><?= htmlspecialchars($product['store']) ?></td>
                                    <td><?= htmlspecialchars($product['updated']) ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-soft"><i class="bi bi-pencil"></i> Editar</button>
                                        <button class="btn btn-sm btn-soft-danger"><i class="bi bi-trash"></i> Excluir</button>
                                        <button class="btn btn-sm btn-soft" data-toast="Produto enviado para atualização."><i class="bi bi-arrow-repeat"></i> Atualizar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer" data-pagination-for="productsTable"></div>
                </article>
            </section>

            <section class="content-section" id="categorias" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Catálogo</span><h1>Categorias</h1><p>Distribuição do catálogo por vertical, preço médio e loja dominante.</p></div>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Categoria</th><th>Produtos</th><th>Preço médio</th><th>Crescimento</th><th>Loja líder</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                    <td><?= number_format($category['products'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($category['avg_price']) ?></td>
                                    <td><span class="trend-up"><?= htmlspecialchars($category['growth']) ?></span></td>
                                    <td><?= htmlspecialchars($category['top_store']) ?></td>
                                    <td class="table-actions"><button class="btn btn-sm btn-soft"><i class="bi bi-pencil"></i> Editar</button><button class="btn btn-sm btn-soft"><i class="bi bi-diagram-3"></i> Regras</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="duplicados" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Catálogo</span><h1>Produtos Duplicados</h1><p>Possíveis duplicidades identificadas por similaridade de nome, SKU e loja.</p></div>
                    <button class="btn btn-primary btn-sm" type="button" data-toast="Mesclagem automática iniciada."><i class="bi bi-intersect"></i> Mesclar confiáveis</button>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Produto canônico</th><th>Correspondências</th><th>Lojas</th><th>Confiança</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($duplicates as $duplicate): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($duplicate['product']) ?></strong></td>
                                    <td><?= (int) $duplicate['matches'] ?></td>
                                    <td><?= htmlspecialchars($duplicate['stores']) ?></td>
                                    <td><span class="status-badge status-success"><i></i><?= htmlspecialchars($duplicate['confidence']) ?></span></td>
                                    <td class="table-actions"><button class="btn btn-sm btn-soft">Revisar</button><button class="btn btn-sm btn-soft-danger">Ignorar</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="usuarios" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Acesso</span><h1>Gestão de Usuários</h1><p>Contas, status e permissões administrativas.</p></div>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle sortable-table" id="usersTable" data-page-size="6">
                            <thead><tr><th data-sort="name">Nome</th><th data-sort="email">Email</th><th data-sort="created">Cadastro</th><th data-sort="status">Status</th><th data-sort="type">Tipo</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($users as $account): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($account['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($account['email']) ?></td>
                                    <td><?= htmlspecialchars($account['created']) ?></td>
                                    <td><span class="status-badge status-<?= badgeClass($account['status']) ?>"><i></i><?= htmlspecialchars($account['status']) ?></span></td>
                                    <td><?= htmlspecialchars($account['type']) ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-soft"><i class="bi bi-pencil"></i> Editar</button>
                                        <button class="btn btn-sm btn-soft"><i class="bi bi-pause-circle"></i> Suspender</button>
                                        <button class="btn btn-sm btn-soft-danger"><i class="bi bi-slash-circle"></i> Banir</button>
                                        <button class="btn btn-sm btn-soft"><i class="bi bi-shield-check"></i> Promover</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="permissoes" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Acesso</span><h1>Permissões</h1><p>Perfis de acesso prontos para integrar com RBAC no MySQL.</p></div>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Perfil</th><th>Usuários</th><th>Escopo</th><th>Risco</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($permissions as $permission): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($permission['role']) ?></strong></td>
                                    <td><?= (int) $permission['users'] ?></td>
                                    <td><?= htmlspecialchars($permission['scope']) ?></td>
                                    <td><span class="status-badge status-<?= $permission['risk'] === 'Alto' ? 'danger' : ($permission['risk'] === 'Medio' ? 'warning' : 'success') ?>"><i></i><?= htmlspecialchars($permission['risk']) ?></span></td>
                                    <td class="table-actions"><button class="btn btn-sm btn-soft">Editar perfil</button><button class="btn btn-sm btn-soft">Auditar</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="logs" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Sistema</span><h1>Visualizador de Logs</h1><p>Leitura operacional no estilo Laravel Log Viewer.</p></div>
                </div>
                <article class="panel log-viewer">
                    <div class="log-filters" role="group" aria-label="Filtros de log">
                        <?php foreach (['ALL' => 'Todos', 'INFO' => 'INFO', 'WARNING' => 'WARNING', 'ERROR' => 'ERROR', 'CRITICAL' => 'CRITICAL'] as $level => $label): ?>
                            <button class="btn btn-sm <?= $level === 'ALL' ? 'btn-primary' : 'btn-soft' ?>" type="button" data-log-filter="<?= $level ?>"><?= $label ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="log-list" id="logList">
                        <?php foreach ($logs as $log): ?>
                            <div class="log-row" data-level="<?= htmlspecialchars($log['level']) ?>">
                                <span class="log-level level-<?= badgeClass($log['level']) ?>"><?= htmlspecialchars($log['level']) ?></span>
                                <span><?= htmlspecialchars($log['date']) ?></span>
                                <span><?= htmlspecialchars($log['time']) ?></span>
                                <strong><?= htmlspecialchars($log['source']) ?></strong>
                                <p><?= htmlspecialchars($log['message']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section class="content-section" id="analytics" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Insights</span><h1>Analytics</h1><p>Leitura estratégica de demanda, lojas e oportunidades de catálogo.</p></div>
                </div>
                <div class="analytics-grid">
                    <article class="panel"><div class="panel-header"><div><h2>Produtos mais pesquisados</h2><p>Participação por termo</p></div></div><div class="chart-wrap"><canvas id="analyticsProductsChart"></canvas></div></article>
                    <article class="panel"><div class="panel-header"><div><h2>Lojas mais acessadas</h2><p>Origem dos cliques</p></div></div><div class="chart-wrap"><canvas id="analyticsStoresChart"></canvas></div></article>
                    <article class="panel"><div class="panel-header"><div><h2>Pesquisas sem resultado</h2><p>Falhas por dia</p></div></div><div class="chart-wrap"><canvas id="noResultChart"></canvas></div></article>
                    <article class="panel"><div class="panel-header"><div><h2>Cliques por loja</h2><p>Conversão de saída</p></div></div><div class="chart-wrap"><canvas id="storeClicksChart"></canvas></div></article>
                </div>
            </section>

            <section class="content-section" id="relatorios" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Analytics</span><h1>Relatórios</h1><p>Relatórios operacionais gerados e agendados para o time.</p></div>
                    <button class="btn btn-primary btn-sm" type="button" data-toast="Novo relatório agendado."><i class="bi bi-calendar-plus"></i> Agendar</button>
                </div>
                <article class="panel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead><tr><th>Relatório</th><th>Período</th><th>Responsável</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($report['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($report['period']) ?></td>
                                    <td><?= htmlspecialchars($report['owner']) ?></td>
                                    <td><span class="status-badge status-<?= $report['status'] === 'Pronto' ? 'success' : ($report['status'] === 'Processando' ? 'warning' : 'neutral') ?>"><i></i><?= htmlspecialchars($report['status']) ?></span></td>
                                    <td class="table-actions"><button class="btn btn-sm btn-soft"><i class="bi bi-eye"></i> Abrir</button><button class="btn btn-sm btn-soft"><i class="bi bi-download"></i> Baixar</button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="content-section" id="configuracoes" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Administração</span><h1>Configurações</h1><p>Parâmetros preparados para persistência em banco.</p></div>
                </div>
                <form class="settings-grid">
                    <article class="panel">
                        <div class="panel-header"><div><h2>Sistema</h2><p>Identidade e parâmetros principais</p></div></div>
                        <div class="form-stack">
                            <label>Nome do Sistema<input class="form-control" type="text" value="Precio"></label>
                            <label>Logo<input class="form-control" type="file" accept="image/*"></label>
                            <label>Favicon<input class="form-control" type="file" accept="image/*"></label>
                            <label>Tempo de Cache (min)<input class="form-control" type="number" value="30" min="1"></label>
                        </div>
                    </article>
                    <article class="panel">
                        <div class="panel-header"><div><h2>Scraping e APIs</h2><p>Limites de execução e integrações</p></div></div>
                        <div class="form-stack">
                            <label>Timeout de Scraping (s)<input class="form-control" type="number" value="45" min="1"></label>
                            <label>Limite de Requisições por Minuto<input class="form-control" type="number" value="120" min="1"></label>
                            <label>Chave de API Primária<input class="form-control" type="password" value="precio-demo-key"></label>
                            <label>Webhook de Alertas<input class="form-control" type="url" value="https://hooks.example.com/precio"></label>
                        </div>
                    </article>
                    <div class="settings-actions">
                        <button class="btn btn-light" type="reset">Restaurar</button>
                        <button class="btn btn-primary" type="button" data-toast="Configurações salvas."><i class="bi bi-check2"></i> Salvar configurações</button>
                    </div>
                </form>
            </section>

            <section class="content-section" id="apis" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Configurações</span><h1>APIs</h1><p>Integrações externas, consumo e chaves usadas pelos scrapers.</p></div>
                </div>
                <div class="settings-grid">
                    <article class="panel">
                        <div class="panel-header"><div><h2>Integrações</h2><p>Status de provedores conectados</p></div></div>
                        <div class="table-responsive">
                            <table class="table admin-table align-middle">
                                <thead><tr><th>Serviço</th><th>Provedor</th><th>Status</th><th>Uso</th></tr></thead>
                                <tbody>
                                <?php foreach ($apiIntegrations as $api): ?>
                                    <tr><td><strong><?= htmlspecialchars($api['name']) ?></strong></td><td><?= htmlspecialchars($api['provider']) ?></td><td><span class="status-badge status-success"><i></i><?= htmlspecialchars($api['status']) ?></span></td><td><?= htmlspecialchars($api['usage']) ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                    <article class="panel">
                        <div class="panel-header"><div><h2>Chaves</h2><p>Valores mascarados para produção</p></div></div>
                        <div class="form-stack">
                            <label>Proxy API Key<input class="form-control" type="password" value="proxy_live_xxxxxxxxx"></label>
                            <label>SerpAPI Key<input class="form-control" type="password" value="serp_xxxxxxxxx"></label>
                            <label>Webhook de alertas<input class="form-control" type="url" value="https://hooks.slack.com/services/precio"></label>
                            <button class="btn btn-primary" type="button" data-toast="Chaves de API salvas.">Salvar chaves</button>
                        </div>
                    </article>
                </div>
            </section>

            <section class="content-section" id="seguranca" data-section>
                <div class="page-heading compact">
                    <div><span class="eyebrow">Proteção</span><h1>Segurança</h1><p>Auditoria de acesso, sessões ativas, bloqueios e autenticação forte.</p></div>
                </div>
                <div class="security-grid">
                    <article class="panel"><div class="panel-header"><div><h2>Histórico de Login</h2><p>Últimas autenticações</p></div></div><ul class="audit-list"><li><strong><?= htmlspecialchars($adminName) ?></strong><span>25/06/2026 14:42 - São Paulo, BR</span></li><li><strong>Bianca Nunes</strong><span>25/06/2026 13:08 - Curitiba, BR</span></li><li><strong>Service Worker</strong><span>25/06/2026 12:00 - Token interno</span></li></ul></article>
                    <article class="panel"><div class="panel-header"><div><h2>Sessões Ativas</h2><p>Dispositivos conectados</p></div></div><ul class="audit-list"><li><strong>Chrome Windows</strong><span>Atual - IP 192.168.0.12</span></li><li><strong>Edge Windows</strong><span>Ativo ha 2 horas - IP 192.168.0.18</span></li></ul></article>
                    <article class="panel"><div class="panel-header"><div><h2>IPs Bloqueados</h2><p>Controle antifraude</p></div></div><ul class="audit-list"><li><strong>203.0.113.91</strong><span>Rate limit excedido</span></li><li><strong>198.51.100.18</strong><span>Tentativas de login</span></li></ul></article>
                    <article class="panel"><div class="panel-header"><div><h2>Autenticação em Dois Fatores</h2><p>Camada adicional para admins</p></div></div><div class="form-check form-switch security-switch"><input class="form-check-input" type="checkbox" role="switch" id="twoFactor" checked><label class="form-check-label" for="twoFactor">Exigir 2FA para administradores</label></div></article>
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade" id="runScraperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Executar scraper</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body">
                <label class="form-label">Loja</label>
                <select class="form-select"><option>Todas as lojas</option><option>Amazon Brasil</option><option>KaBuM!</option><option>Pichau</option></select>
            </div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" data-bs-dismiss="modal" data-toast="Scraper enviado para execução.">Executar agora</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="scraperConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Configurar scraper</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body form-stack">
                <label>Intervalo de execução (min)<input class="form-control" type="number" value="15"></label>
                <label>Timeout (s)<input class="form-control" type="number" value="45"></label>
                <label>User Agent<input class="form-control" type="text" value="PrecioBot/1.0"></label>
            </div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" data-bs-dismiss="modal" data-toast="Configuração do scraper salva.">Salvar</button></div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="adminToast" class="toast align-items-center border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">Ação concluída.</div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    window.PRECIO_ADMIN_DATA = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
