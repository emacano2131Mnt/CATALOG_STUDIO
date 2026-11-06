<?php

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_is_valid($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function form_value(array $source, string $key, $fallback = ''): string
{
    $value = $source[$key] ?? $fallback;
    return is_string($value) || is_numeric($value) ? (string)$value : (string)$fallback;
}

function request_text(array $source, string $key, string $label, int $maxLength, bool $required, array &$errors): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        $errors[] = $label . ' tiene un formato inválido.';
        return '';
    }

    $value = trim($value);
    if (!preg_match('//u', $value)) {
        $errors[] = $label . ' debe usar texto UTF-8 válido.';
        return '';
    }

    preg_match_all('/./us', $value, $characters);
    $length = count($characters[0]);

    if ($required && $value === '') {
        $errors[] = 'El campo ' . $label . ' es obligatorio.';
    } elseif ($length > $maxLength) {
        $errors[] = $label . ' no puede superar ' . $maxLength . ' caracteres.';
    }

    return $value;
}

function positive_id($value): ?int
{
    if (!is_string($value) && !is_int($value)) {
        return null;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function valid_price($value, array &$errors): ?string
{
    if (!is_string($value) || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $value)) {
        $errors[] = 'El precio debe ser un número no negativo, con hasta 10 dígitos enteros y 2 decimales.';
        return null;
    }

    return $value;
}

function render_form_errors(array $errors): void
{
    if (!$errors) {
        return;
    }

    echo '<div class="empty-state" role="alert"><h3>Revisa estos datos</h3><ul style="display:inline-block;text-align:left">';
    foreach (array_unique($errors) as $error) {
        echo '<li>' . h($error) . '</li>';
    }
    echo '</ul></div>';
}

function reject_request(string $message, int $status = 400): void
{
    http_response_code($status);
    $safeMessage = h($message);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Solicitud no válida</title><body><main><h1>Solicitud no válida</h1><p>' . $safeMessage . '</p><p><a href="index.php">Volver al panel</a></p></main></body></html>';
    exit;
}
