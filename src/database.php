<?php
try {
    if (!$fst_config) throw new Exception("Configuration not loaded.");

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
                default:
                    throw new Exception("Unsupported database driver '{$driver}' in fullstuck.json.");
            }
        } catch (Exception $e) {
            if (function_exists('fst_abort')) fst_abort(500, "Database Connection Failed: " . $e->getMessage());
            else die("FATAL ERROR: Database Connection Failed: " . $e->getMessage());
        }
    }
} catch (Exception $e) {
    if (function_exists('fst_abort')) fst_abort(500, "Database Connection Failed: " . $e->getMessage());
    else die("FATAL ERROR: Database Connection Failed: " . $e->getMessage());
}

function fst_db($mode, $sql, $params = []) {
    global $fst_pdo;

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
    return match(strtoupper($mode)) { 'ROW' => $stmt->fetch(), 'SCALAR' => $stmt->fetchColumn(), 'ALL' => $stmt->fetchAll(), default => $stmt->fetchAll() };
}
?>
