<?php
function fst_dump(...$vars) {
    $fst_config = fst_app('config');
    if (!fst_is_dev()) {
        return;
    }
    echo '<pre style="background-color: #1a1a1a; color: #f0f0f0; padding: 15px; border: 1px solid #444; margin: 10px; border-radius: 5px; text-align: left; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5;">';
    foreach ($vars as $var) { var_dump($var); }
    echo '</pre>';
}
function fst_dd(...$vars) { fst_dump(...$vars); die(); }

// Helper internal: fallback strlen jika mbstring tidak tersedia
function _fst_strlen($str) {
    return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
}

function fst_validate($data, $rules) {
    $errors = [];
    $sanitized = [];

    foreach ($rules as $field => $rule_string) {
        $value = $data[$field] ?? null;
        $rules_array = is_array($rule_string) ? $rule_string : explode('|', $rule_string);
        
        $field_valid = true;

        foreach ($rules_array as $rule) {
            $params = [];
            if (str_contains($rule, ':')) {
                list($rule_name, $param_str) = explode(':', $rule, 2);
                $params = explode(',', $param_str);
            } else {
                $rule_name = $rule;
            }

            // Abaikan validasi lanjutan jika kosong dan tidak di-set required
            if ($rule_name !== 'required' && ($value === null || trim((string)$value) === '')) {
                continue;
            }

            if ($rule_name === 'required') {
                if ($value === null || trim((string)$value) === '') {
                    $errors[$field][] = "Bidang '{$field}' wajib diisi.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "Bidang '{$field}' harus berupa email yang valid.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'min') {
                $min = (int)($params[0] ?? 0);
                if (_fst_strlen((string)$value) < $min) {
                    $errors[$field][] = "Bidang '{$field}' minimal {$min} karakter.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'max') {
                $max = (int)($params[0] ?? 0);
                if (_fst_strlen((string)$value) > $max) {
                    $errors[$field][] = "Bidang '{$field}' maksimal {$max} karakter.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'numeric') {
                if (!is_numeric($value)) {
                    $errors[$field][] = "Bidang '{$field}' harus berupa angka.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'in') {
                if (!in_array($value, $params)) {
                    $errors[$field][] = "Bidang '{$field}' harus salah satu dari: " . implode(', ', $params) . ".";
                    $field_valid = false;
                }
            }
        }
        
        if ($value !== null) {
            $sanitized[$field] = is_string($value) ? trim($value) : $value;
        }
    }

    return [
        'valid' => count($errors) === 0,
        'errors' => $errors,
        'data' => $sanitized
    ];
}
?>
