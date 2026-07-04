<?php
// Configurações persistidas do painel administrativo.
// Guardadas em arquivo JSON simples (sem tabela dedicada no banco).

define('SETTINGS_FILE', __DIR__ . '/../config/settings.json');

function settingsDefaults(): array {
    return [
        'cache_ttl_minutes' => 10,
    ];
}

function getSettings(): array {
    $defaults = settingsDefaults();
    if (!file_exists(SETTINGS_FILE)) {
        return $defaults;
    }
    $raw = @file_get_contents(SETTINGS_FILE);
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        return $defaults;
    }
    return array_merge($defaults, $data);
}

function getSetting(string $key)
{
    $settings = getSettings();
    return $settings[$key] ?? (settingsDefaults()[$key] ?? null);
}

function saveSettings(array $partial): array {
    $current = getSettings();
    $updated = array_merge($current, $partial);

    if (!is_dir(dirname(SETTINGS_FILE))) {
        @mkdir(dirname(SETTINGS_FILE), 0755, true);
    }
    @file_put_contents(SETTINGS_FILE, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $updated;
}
