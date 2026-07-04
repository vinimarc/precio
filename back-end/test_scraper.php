<?php
/**
 * test_scraper.php — ferramenta de diagnóstico dos scrapers.
 *
 * Por quê isso existe: os parsers de Magazine Luiza e Terabyte Shop foram
 * escritos sem acesso à internet (o ambiente que gerou este código não
 * consegue alcançar r.jina.ai, magazineluiza.com.br nem terabyteshop.com.br).
 * Então, embora a LÓGICA tenha sido validada com markdown sintético, o
 * REGEX pode não bater 100% com o markdown real que o Jina Reader devolve
 * pra essas duas lojas. Rode este script no seu ambiente (XAMPP/Laragon,
 * que tem internet de verdade) pra confirmar e, se precisar, ajustar.
 *
 * Uso (via terminal, na pasta do projeto):
 *   php test_scraper.php magalu "rtx 4060"
 *   php test_scraper.php terabyte "ssd 1tb"
 *   php test_scraper.php kabum "rtx 4060"
 *   php test_scraper.php amazon "rtx 4060"
 *   php test_scraper.php pichau "rtx 4060"
 *
 * O que ele faz:
 *   1. Busca o markdown real via Jina Reader (mesma função usada pela API).
 *   2. Salva o markdown bruto em debug_<loja>.md (pra você inspecionar).
 *   3. Roda o parser daquela loja sobre o markdown e mostra quantos
 *      produtos ele conseguiu extrair (e os 3 primeiros, formatados).
 *
 * Se "produtos extraídos" vier 0 mas o debug_<loja>.md claramente tem
 * produtos lá dentro, é sinal de que o regex em parseGenericLojaMarkdown()
 * (ou no parser específico) precisa de ajuste — me mande o conteúdo do
 * debug_<loja>.md que eu ajusto o regex pra bater certinho.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Este script é só pra linha de comando: php test_scraper.php <loja> \"<busca>\"\n");
}

// Suprime qualquer saída/efeito colateral do api.php ao ser incluído
// (ele tenta despachar uma "action" de $_POST, que estará vazio aqui).
ob_start();
require_once __DIR__ . '/api.php';
ob_end_clean();

$loja  = $argv[1] ?? null;
$busca = $argv[2] ?? null;

$lojasValidas = ['pichau', 'kabum', 'amazon', 'magalu', 'terabyte'];

if (!$loja || !$busca || !in_array($loja, $lojasValidas, true)) {
    echo "Uso: php test_scraper.php <loja> \"<termo de busca>\"\n";
    echo "Lojas válidas: " . implode(', ', $lojasValidas) . "\n";
    exit(1);
}

$urls = [
    'pichau'   => 'https://www.pichau.com.br/search?q=' . rawurlencode($busca),
    'kabum'    => 'https://www.kabum.com.br/busca/' . rawurlencode($busca),
    'amazon'   => 'https://www.amazon.com.br/s?k=' . rawurlencode($busca),
    'magalu'   => 'https://www.magazineluiza.com.br/busca/' . rawurlencode($busca) . '/',
    'terabyte' => 'https://www.terabyteshop.com.br/busca?str=' . rawurlencode($busca),
];

$readerUrl = 'https://r.jina.ai/' . $urls[$loja];
echo "Buscando: $readerUrl\n";

$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
];

$ch  = buildCurlHandle($readerUrl, $headers);
$res = curlMultiExec([$loja => $ch]);
$r   = $res[$loja];

echo "HTTP status: {$r['status']}\n";
if ($r['error']) echo "Erro cURL: {$r['error']}\n";

$markdown = (string) ($r['body'] ?? '');
if ($markdown === '') {
    echo "Resposta vazia — não dá pra testar o parser. Verifique conexão / se o Jina Reader está acessível.\n";
    exit(1);
}

$debugFile = __DIR__ . "/debug_{$loja}.md";
file_put_contents($debugFile, $markdown);
echo "Markdown bruto salvo em: $debugFile (" . strlen($markdown) . " bytes)\n\n";

$parser = match ($loja) {
    'pichau'   => 'parsePichauMarkdown',
    'kabum'    => 'parseKabumMarkdown',
    'amazon'   => 'parseAmazonMarkdown',
    'magalu'   => 'parseMagaluMarkdown',
    'terabyte' => 'parseTerabyteMarkdown',
};

$resultado = $parser($markdown, 30);
$produtos  = $resultado['produtos'] ?? [];

echo "Produtos extraídos pelo parser: " . count($produtos) . "\n";
echo str_repeat('-', 50) . "\n";

foreach (array_slice($produtos, 0, 3) as $i => $p) {
    printf(
        "%d. %s\n   Preço: R$ %s%s\n   URL: %s\n   Imagem: %s\n\n",
        $i + 1,
        $p['nome'],
        number_format($p['preco'], 2, ',', '.'),
        $p['preco_orig'] ? ' (de R$ ' . number_format($p['preco_orig'], 2, ',', '.') . ')' : '',
        $p['url'],
        $p['imagem'] ?: '(sem imagem)'
    );
}

if (count($produtos) === 0) {
    echo "\nNenhum produto extraído. Abra $debugFile e veja como o markdown real está\n";
    echo "estruturado — se o padrão (imagem -> nome -> preço) for diferente do esperado,\n";
    echo "me envie um trecho do arquivo que eu ajusto o regex em api.php.\n";
}
