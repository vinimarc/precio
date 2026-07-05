<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/products.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso restrito a administradores.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = getDB();

function paginate(array $items, int $page, int $perPage): array
{
    $page = max(1, $page);
    $total = count($items);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'page' => $page,
        'total_pages' => $totalPages,
    ];
}

function jsonInput(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

// ── DASHBOARD ─────────────────────────────────────────────────────────────────
if ($action === 'stats') {
    $totalUsuarios = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $totalAdmins   = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    $buscasHoje  = (int) $db->query('SELECT COUNT(*) FROM search_log WHERE DATE(created_at) = CURDATE()')->fetchColumn();
    $buscasTotal = (int) $db->query('SELECT COUNT(*) FROM search_log')->fetchColumn();

    $stmt = $db->query("SELECT DATE(created_at) AS dia, COUNT(*) AS total FROM search_log WHERE created_at >= (CURDATE() - INTERVAL 6 DAY) GROUP BY dia ORDER BY dia ASC");
    $porDia = $stmt->fetchAll();
    $labelsDia = [];
    $valoresDia = [];
    for ($i = 6; $i >= 0; $i--) {
        $dia = date('Y-m-d', strtotime("-{$i} day"));
        $labelsDia[] = date('d/m', strtotime($dia));
        $encontrado = array_values(array_filter($porDia, fn($r) => $r['dia'] === $dia));
        $valoresDia[] = $encontrado ? (int) $encontrado[0]['total'] : 0;
    }

    $stmt = $db->query('SELECT term, COUNT(*) AS total FROM search_log GROUP BY term ORDER BY total DESC LIMIT 5');
    $topTermos = $stmt->fetchAll();

    $produtos = loadAllCachedProducts();
    $lojas = aggregateStores($produtos);
    $cache = cacheDiskStats();

    echo json_encode([
        'success' => true,
        'data' => [
            'total_usuarios'  => $totalUsuarios,
            'total_admins'    => $totalAdmins,
            'buscas_hoje'     => $buscasHoje,
            'buscas_total'    => $buscasTotal,
            'produtos_cache'  => count($produtos),
            'lojas_ativas'    => count($lojas),
            'cache_arquivos'  => $cache['arquivos'],
            'cache_tamanho_kb'=> round($cache['tamanho_bytes'] / 1024, 1),
            'chart_buscas' => ['labels' => $labelsDia, 'values' => $valoresDia],
            'chart_lojas'  => [
                'labels' => array_map(fn($l) => $l['loja'], $lojas),
                'values' => array_map(fn($l) => $l['produtos'], $lojas),
            ],
            'top_termos' => $topTermos,
        ],
    ]);
    exit;
}

// ── USUÁRIOS ──────────────────────────────────────────────────────────────────
if ($action === 'users_list') {
    $q = trim((string) jsonInput('q', ''));
    $page = (int) jsonInput('page', 1);

    if ($q !== '') {
        $stmt = $db->prepare('SELECT id, name, email, role, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC');
        $like = '%' . $q . '%';
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
    }

    $rows = $stmt->fetchAll();
    $result = paginate($rows, $page, 8);
    echo json_encode(['success' => true] + $result);
    exit;
}

if ($action === 'users_create') {
    $name = trim((string) jsonInput('name', ''));
    $email = trim((string) jsonInput('email', ''));
    $password = (string) jsonInput('password', '');
    $role = jsonInput('role', 'user') === 'admin' ? 'admin' : 'user';

    if (!$name || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Preencha nome, e-mail e senha.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter ao menos 6 caracteres.']);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um usuário com este e-mail.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $role]);

    logMsg('INFO', 'admin.users', "Usuário criado pelo painel: {$email} ({$role}).");
    echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    exit;
}

if ($action === 'users_update') {
    $id = (int) jsonInput('id', 0);
    $name = trim((string) jsonInput('name', ''));
    $email = trim((string) jsonInput('email', ''));
    $role = jsonInput('role', 'user') === 'admin' ? 'admin' : 'user';
    $password = (string) jsonInput('password', '');

    if (!$id || !$name || !$email) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este e-mail já pertence a outro usuário.']);
        exit;
    }

    // Impede remover o último administrador do sistema
    if ($role !== 'admin') {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $atual = $stmt->fetch();
        if ($atual && $atual['role'] === 'admin') {
            $totalAdmins = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            if ($totalAdmins <= 1) {
                echo json_encode(['success' => false, 'message' => 'Não é possível remover o último administrador.']);
                exit;
            }
        }
    }

    if ($password !== '') {
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'A senha deve ter ao menos 6 caracteres.']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $email, $role, $hash, $id]);
    } else {
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $stmt->execute([$name, $email, $role, $id]);
    }

    // Se o admin editou a própria conta, atualiza a sessão também
    if ($id === (int) $_SESSION['user_id']) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
    }

    logMsg('INFO', 'admin.users', "Usuário #{$id} atualizado pelo painel.");
    echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso.']);
    exit;
}

if ($action === 'users_delete') {
    $id = (int) jsonInput('id', 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Usuário inválido.']);
        exit;
    }
    if ($id === (int) $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta.']);
        exit;
    }

    $stmt = $db->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $alvo = $stmt->fetch();
    if (!$alvo) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
        exit;
    }
    if ($alvo['role'] === 'admin') {
        $totalAdmins = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($totalAdmins <= 1) {
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir o último administrador.']);
            exit;
        }
    }

    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);

    logMsg('WARNING', 'admin.users', "Usuário #{$id} excluído pelo painel.");
    echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso.']);
    exit;
}

// ── PRODUTOS (CATÁLOGO EM CACHE) ─────────────────────────────────────────────
if ($action === 'products_list') {
    $q = mb_strtolower(trim((string) jsonInput('q', '')));
    $loja = trim((string) jsonInput('loja', ''));
    $page = (int) jsonInput('page', 1);

    $produtos = loadAllCachedProducts();

    if ($q !== '') {
        $produtos = array_values(array_filter($produtos, fn($p) => str_contains(mb_strtolower($p['nome']), $q)));
    }
    if ($loja !== '') {
        $produtos = array_values(array_filter($produtos, fn($p) => $p['loja'] === $loja));
    }

    usort($produtos, fn($a, $b) => $b['updated_at'] <=> $a['updated_at']);

    $result = paginate($produtos, $page, 10);
    echo json_encode(['success' => true] + $result);
    exit;
}

if ($action === 'products_update') {
    $id = (string) jsonInput('id', '');
    $nome = trim((string) jsonInput('nome', ''));
    $preco = jsonInput('preco', null);
    $emEstoque = jsonInput('em_estoque', '1') === '1';

    if (!$id || !$nome || $preco === null || !is_numeric($preco)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    $ok = updateCachedProduct($id, ['nome' => $nome, 'preco' => (float) $preco, 'em_estoque' => $emEstoque]);
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado no cache.']);
        exit;
    }

    logMsg('INFO', 'admin.products', "Produto atualizado no cache (id {$id}).");
    echo json_encode(['success' => true, 'message' => 'Produto atualizado com sucesso.']);
    exit;
}

if ($action === 'products_delete') {
    $id = (string) jsonInput('id', '');
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Produto inválido.']);
        exit;
    }

    $ok = deleteCachedProduct($id);
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado no cache.']);
        exit;
    }

    logMsg('WARNING', 'admin.products', "Produto removido do cache (id {$id}).");
    echo json_encode(['success' => true, 'message' => 'Produto removido do cache.']);
    exit;
}

// ── SCRAPERS / CACHE ──────────────────────────────────────────────────────────
if ($action === 'stores_list') {
    $produtos = loadAllCachedProducts();
    $lojas = aggregateStores($produtos);
    echo json_encode(['success' => true, 'data' => $lojas]);
    exit;
}

if ($action === 'cache_clear') {
    $removidos = clearAllCache();
    logMsg('WARNING', 'admin.cache', "Cache de buscas limpo manualmente pelo painel ({$removidos} arquivos removidos).");
    echo json_encode(['success' => true, 'message' => "Cache limpo: {$removidos} arquivo(s) removido(s)."]);
    exit;
}

// ── HISTÓRICO DE PESQUISAS ────────────────────────────────────────────────────
if ($action === 'searches_list') {
    $q = trim((string) jsonInput('q', ''));
    $page = (int) jsonInput('page', 1);

    $sql = 'SELECT sl.id, sl.term, sl.results_count, sl.source, sl.created_at, u.name AS user_name
            FROM search_log sl LEFT JOIN users u ON u.id = sl.user_id';
    $params = [];
    if ($q !== '') {
        $sql .= ' WHERE sl.term LIKE ?';
        $params[] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY sl.created_at DESC LIMIT 500';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $result = paginate($rows, $page, 10);
    echo json_encode(['success' => true] + $result);
    exit;
}

// ── LOGS ──────────────────────────────────────────────────────────────────────
if ($action === 'logs_list') {
    $level = jsonInput('level', 'ALL');
    $entries = readLogs(300, $level === 'ALL' ? null : $level);
    echo json_encode(['success' => true, 'data' => $entries]);
    exit;
}

if ($action === 'logs_clear') {
    clearLogs();
    logMsg('INFO', 'admin.logs', 'Log do sistema foi limpo pelo painel administrativo.');
    echo json_encode(['success' => true, 'message' => 'Log limpo com sucesso.']);
    exit;
}

// ── LOJAS VTEX ────────────────────────────────────────────────────────────────
if ($action === 'vtex_stores_list') {
    echo json_encode(['success' => true, 'data' => getVtexLojas()]);
    exit;
}

if ($action === 'vtex_store_add') {
    $nome    = trim((string) jsonInput('nome', ''));
    $dominio = trim((string) jsonInput('dominio', ''));
    $dominio = preg_replace('#^https?://#', '', $dominio);
    $dominio = rtrim((string) $dominio, '/');

    if (!$nome || !$dominio) {
        echo json_encode(['success' => false, 'message' => 'Informe o nome da loja e o domínio da API.']);
        exit;
    }

    $lojas = getVtexLojas();
    $novoId = 1;
    foreach ($lojas as $l) $novoId = max($novoId, ((int) $l['id']) + 1);

    $lojas[] = ['id' => $novoId, 'nome' => $nome, 'dominio' => $dominio, 'ativo' => true];
    saveSettings(['vtex_lojas' => $lojas]);

    logMsg('INFO', 'admin.vtex', "Loja VTEX adicionada: {$nome} ({$dominio}).");
    echo json_encode(['success' => true, 'message' => 'Loja adicionada com sucesso.', 'data' => $lojas]);
    exit;
}

if ($action === 'vtex_store_toggle') {
    $id = (int) jsonInput('id', 0);
    $lojas = getVtexLojas();
    $achou = false;
    foreach ($lojas as &$l) {
        if ((int) $l['id'] === $id) {
            $l['ativo'] = empty($l['ativo']);
            $achou = true;
        }
    }
    unset($l);

    if (!$achou) {
        echo json_encode(['success' => false, 'message' => 'Loja não encontrada.']);
        exit;
    }

    saveSettings(['vtex_lojas' => $lojas]);
    logMsg('INFO', 'admin.vtex', "Loja VTEX #{$id} teve o status alternado.");
    echo json_encode(['success' => true, 'message' => 'Status atualizado.', 'data' => $lojas]);
    exit;
}

if ($action === 'vtex_store_remove') {
    $id = (int) jsonInput('id', 0);
    $lojas = getVtexLojas();
    $restantes = array_values(array_filter($lojas, fn($l) => (int) $l['id'] !== $id));

    if (count($restantes) === count($lojas)) {
        echo json_encode(['success' => false, 'message' => 'Loja não encontrada.']);
        exit;
    }

    saveSettings(['vtex_lojas' => $restantes]);
    logMsg('WARNING', 'admin.vtex', "Loja VTEX #{$id} removida.");
    echo json_encode(['success' => true, 'message' => 'Loja removida com sucesso.', 'data' => $restantes]);
    exit;
}

// ── CONFIGURAÇÕES ─────────────────────────────────────────────────────────────
if ($action === 'settings_get') {
    echo json_encode(['success' => true, 'data' => getSettings()]);
    exit;
}

if ($action === 'settings_update') {
    $ttl = (int) jsonInput('cache_ttl_minutes', 10);
    if ($ttl < 1 || $ttl > 1440) {
        echo json_encode(['success' => false, 'message' => 'O tempo de cache deve ser entre 1 e 1440 minutos.']);
        exit;
    }

    $meliClientId     = trim((string) jsonInput('meli_client_id', ''));
    $meliClientSecret = trim((string) jsonInput('meli_client_secret', ''));

    $updated = saveSettings([
        'cache_ttl_minutes'  => $ttl,
        'meli_client_id'     => $meliClientId,
        'meli_client_secret' => $meliClientSecret,
    ]);
    logMsg('INFO', 'admin.settings', "Configurações atualizadas (cache: {$ttl} min, Mercado Livre: " . ($meliClientId !== '' ? 'credenciais definidas' : 'sem credenciais') . ').');
    echo json_encode(['success' => true, 'message' => 'Configurações salvas.', 'data' => $updated]);
    exit;
}

// ── PERFIL DO ADMINISTRADOR LOGADO ───────────────────────────────────────────
if ($action === 'profile_update') {
    $name = trim((string) jsonInput('name', ''));
    $email = trim((string) jsonInput('email', ''));
    $password = (string) jsonInput('password', '');
    $id = (int) $_SESSION['user_id'];

    if (!$name || !$email) {
        echo json_encode(['success' => false, 'message' => 'Preencha nome e e-mail.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
        exit;
    }

    if ($password !== '') {
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'A senha deve ter ao menos 6 caracteres.']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $email, $hash, $id]);
    } else {
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $id]);
    }

    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    logMsg('INFO', 'admin.profile', "Perfil do administrador #{$id} atualizado.");
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
