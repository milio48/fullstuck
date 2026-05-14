<?php
function _fst_connect_db() {
    $fst_config = fst_app('config');
    if ($fst_config) {
        $db_all_config = $fst_config['database'] ?? null;
        $driver = $db_all_config['driver'] ?? 'none';

        if ($driver !== 'none' && $db_all_config) {
            try {
                $db_config = $db_all_config[$driver];
                $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];

                switch ($driver) {
                    case 'mysql':
                        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
                        $fst_pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
                        break;
                    case 'sqlite':
                        $path = FST_ROOT_DIR . '/' . $db_config['database_path'];
                        $dsn = "sqlite:" . $path;
                        $fst_pdo = new PDO($dsn, null, null, $options);
                        break;
                    case 'pgsql':
                        $port = $db_config['port'] ?? '5432';
                        $dsn = "pgsql:host={$db_config['host']};port={$port};dbname={$db_config['dbname']}";
                        $fst_pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
                        break;
                    default:
                        throw new Exception("Unsupported database driver '{$driver}' in fullstuck.json.");
                }
                fst_app('pdo', $fst_pdo);
            } catch (Exception $e) {
                if (function_exists('fst_abort')) fst_abort(500, "Database Connection Failed: " . $e->getMessage());
                else die("FATAL ERROR: Database Connection Failed: " . $e->getMessage());
            }
        }
    }
}

function fst_db_quote_ident($name) {
    $fst_config = fst_app('config');
    $driver = $fst_config['database']['driver'] ?? 'sqlite';
    $q = ($driver === 'pgsql') ? '"' : '`';
    
    // [PATCH] Dukungan table.column
    if (str_contains($name, '.')) {
        $parts = explode('.', $name);
        $quoted_parts = array_map(function($p) use ($q) {
            return $q . str_replace($q, $q . $q, $p) . $q;
        }, $parts);
        return implode('.', $quoted_parts);
    }
    return $q . str_replace($q, $q . $q, $name) . $q;
}

// Sanitasi order_by agar aman dari SQL Injection
function _fst_sanitize_order_by($order_by) {
    $parts = array_map('trim', explode(',', $order_by));
    $safe_parts = [];
    foreach ($parts as $part) {
        // Format yang diizinkan: "column_name" atau "column_name ASC/DESC"
        if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_.]*)(\s+(ASC|DESC))?$/i', $part, $m)) {
            $safe_parts[] = fst_db_quote_ident($m[1]) . (isset($m[3]) ? ' ' . strtoupper($m[3]) : '');
        }
    }
    return !empty($safe_parts) ? implode(', ', $safe_parts) : null;
}

function fst_db($mode, $sql, $params = []) {
    if (fst_app('pdo') === null) {
        _fst_connect_db();
    }
    
    $fst_pdo = fst_app('pdo');

    if ($fst_pdo === null) {
        fst_abort(500, "Database function fst_db() called, but no database is configured or connected. Check 'fullstuck.json'.");
    }

    $stmt = $fst_pdo->prepare($sql);
    $stmt->execute($params);
    $normalizedSql = strtoupper(trim($sql));
    $isInsert = strpos($normalizedSql, 'INSERT') === 0;
    if (strtoupper($mode) === 'EXEC') {
        return ['affected_rows' => $stmt->rowCount(),'last_id' => $isInsert ? $fst_pdo->lastInsertId() : null,'query_type' => strtok($normalizedSql, ' '),'success' => true];
    }
    return match(strtoupper($mode)) { 
        'ROW' => $stmt->fetch(), 
        'SCALAR', 'ONE' => $stmt->fetchColumn(), 
        'ALL' => $stmt->fetchAll(), 
        default => $stmt->fetchAll() 
    };
}

function fst_db_select($table, $conditions = [], $options = []) {
    $columns = $options['select'] ?? '*';
    $t = fst_db_quote_ident($table);
    $sql = "SELECT {$columns} FROM {$t}";
    $params = [];
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $k => $v) {
            $where[] = fst_db_quote_ident($k) . " = ?";
            $params[] = $v;
        }
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    if (isset($options['order_by'])) {
        $safe_order = _fst_sanitize_order_by($options['order_by']);
        if ($safe_order) $sql .= " ORDER BY " . $safe_order;
    }
    if (isset($options['limit'])) $sql .= " LIMIT " . (int)$options['limit'];
    if (isset($options['offset'])) $sql .= " OFFSET " . (int)$options['offset'];
    
    $mode = $options['mode'] ?? 'ALL';
    return fst_db($mode, $sql, $params);
}

function fst_db_insert($table, $data) {
    if (empty($data)) return false;
    $t = fst_db_quote_ident($table);
    $columns = array_map('fst_db_quote_ident', array_keys($data));
    $placeholders = array_fill(0, count($data), '?');
    $sql = "INSERT INTO {$t} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
    return fst_db('EXEC', $sql, array_values($data));
}

function fst_db_update($table, $data, $conditions = []) {
    if (empty($conditions)) return false; // [PATCH] Mencegah mass-update
    if (empty($data)) return false;
    $t = fst_db_quote_ident($table);
    $set = [];
    $params = [];
    foreach ($data as $k => $v) {
        $set[] = fst_db_quote_ident($k) . " = ?";
        $params[] = $v;
    }
    $sql = "UPDATE {$t} SET " . implode(", ", $set);
    
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $k => $v) {
            $where[] = fst_db_quote_ident($k) . " = ?";
            $params[] = $v;
        }
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    return fst_db('EXEC', $sql, $params);
}

function fst_db_delete($table, $conditions) {
    if (empty($conditions)) return false; 
    $t = fst_db_quote_ident($table);
    $where = [];
    $params = [];
    foreach ($conditions as $k => $v) {
        $where[] = fst_db_quote_ident($k) . " = ?";
        $params[] = $v;
    }
    $sql = "DELETE FROM {$t} WHERE " . implode(" AND ", $where);
    return fst_db('EXEC', $sql, $params);
}
function fst_db_row($table, $conditions = [], $options = []) {
    $options['limit'] = 1;
    $options['mode'] = 'ROW';
    return fst_db_select($table, $conditions, $options);
}

function fst_db_exists($table, $conditions = []) {
    $row = fst_db_row($table, $conditions, ['select' => '1']);
    return !empty($row);
}
?>
