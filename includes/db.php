<?php
/**
 * Taska - Text-file database helpers
 * Each .txt file stores one JSON object per line.
 */

define('DATA_DIR', __DIR__ . '/../data/');

/**
 * Read all records from a txt file.
 */
function db_read(string $file): array {
    $path = DATA_DIR . $file;
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $records = [];
    foreach ($lines as $line) {
        $obj = json_decode($line, true);
        if ($obj !== null) {
            $records[] = $obj;
        }
    }
    return $records;
}

/**
 * Write all records to a txt file (overwrites).
 */
function db_write(string $file, array $records): void {
    $path = DATA_DIR . $file;
    $lines = array_map('json_encode', $records);
    file_put_contents($path, implode("\n", $lines) . (count($lines) ? "\n" : ''), LOCK_EX);
}

/**
 * Insert a new record. Auto-generates an id if not present.
 */
function db_insert(string $file, array $record): array {
    $records = db_read($file);
    if (!isset($record['id'])) {
        $maxId = 0;
        foreach ($records as $r) {
            if (isset($r['id']) && (int)$r['id'] > $maxId) {
                $maxId = (int)$r['id'];
            }
        }
        $record['id'] = (string)($maxId + 1);
    }
    if (!isset($record['created_at'])) {
        $record['created_at'] = date('Y-m-d H:i:s');
    }
    $records[] = $record;
    db_write($file, $records);
    return $record;
}

/**
 * Update a record by id.
 */
function db_update(string $file, string $id, array $updates): bool {
    $records = db_read($file);
    $found = false;
    foreach ($records as &$record) {
        if (isset($record['id']) && $record['id'] === $id) {
            foreach ($updates as $k => $v) {
                $record[$k] = $v;
            }
            $found = true;
            break;
        }
    }
    unset($record);
    if ($found) {
        db_write($file, $records);
    }
    return $found;
}

/**
 * Delete a record by id.
 */
function db_delete(string $file, string $id): bool {
    $records = db_read($file);
    $filtered = array_filter($records, fn($r) => !isset($r['id']) || $r['id'] !== $id);
    if (count($filtered) === count($records)) return false;
    db_write($file, array_values($filtered));
    return true;
}

/**
 * Find a single record by field value.
 */
function db_find(string $file, string $field, $value): ?array {
    foreach (db_read($file) as $record) {
        if (isset($record[$field]) && $record[$field] === $value) {
            return $record;
        }
    }
    return null;
}

/**
 * Find all records matching a field value.
 */
function db_find_all(string $file, string $field, $value): array {
    return array_values(array_filter(db_read($file), fn($r) => isset($r[$field]) && $r[$field] === $value));
}

/**
 * Count records in a file.
 */
function db_count(string $file): int {
    return count(db_read($file));
}
