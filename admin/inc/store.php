<?php
/**
 * Store — atomic PHP-array file persistence.
 *
 * Every content domain lives as one file under data/content/<key>.php (or an
 * arbitrary path for per-slug records like landing pages), each returning a
 * plain array. save() rewrites it via var_export() inside a temp-file+rename
 * (atomic on the same filesystem — a save is never left half-written), after
 * copying the previous version into data/backups/ so any save can be undone.
 */
final class Store
{
    private const DIR     = __DIR__ . '/../../data/content/';
    private const BACKUPS = __DIR__ . '/../../data/backups/';
    private const KEEP    = 20; // backup versions retained per key

    public static function load(string $key): array
    {
        return self::loadPath(self::DIR . $key . '.php');
    }

    public static function save(string $key, array $data): bool
    {
        return self::savePath(self::DIR . $key . '.php', $data, $key);
    }

    public static function loadPath(string $file): array
    {
        if (!is_file($file)) return [];
        $data = require $file;
        return is_array($data) ? $data : [];
    }

    public static function savePath(string $file, array $data, ?string $backupKey = null): bool
    {
        self::backup($file, $backupKey ?? basename($file, '.php'));
        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) return false;
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $php = "<?php\n/* Managed by admin console — last saved " . date('c') . " */\nreturn "
             . var_export($data, true) . ";\n";
        if (file_put_contents($tmp, $php, LOCK_EX) === false) return false;
        if (!rename($tmp, $file)) { @unlink($tmp); return false; }
        return true;
    }

    /** Recent backups for a key, newest first. */
    public static function backups(string $key): array
    {
        $files = glob(self::BACKUPS . $key . '-*.php') ?: [];
        rsort($files);
        return array_map(fn($f) => ['file' => $f, 'name' => basename($f), 'time' => filemtime($f)], $files);
    }

    /** Roll a key back to one of its own backups (itself preceded by a fresh backup). */
    public static function restore(string $key, string $backupFile): bool
    {
        return self::restorePath(self::DIR . $key . '.php', $backupFile, $key);
    }

    /**
     * Same as restore(), but for keys whose live file isn't under
     * data/content/ (landing pages, admin-users) — the caller supplies
     * the real target path to overwrite.
     */
    public static function restorePath(string $targetFile, string $backupFile, string $key): bool
    {
        $real       = realpath($backupFile);
        $backupsDir = realpath(self::BACKUPS);
        if (!$real || !$backupsDir || strncmp($real, $backupsDir, strlen($backupsDir)) !== 0) return false;
        if (basename($real) === '.' || strpos(basename($real), $key . '-') !== 0) return false;
        return self::savePath($targetFile, self::loadPath($real), $key);
    }

    /** Every distinct backup key currently on disk, i.e. every domain that has at least one backup. */
    public static function allBackupKeys(): array
    {
        $files = glob(self::BACKUPS . '*.php') ?: [];
        $keys = [];
        foreach ($files as $f) {
            // filename shape: <key>-YYYYMMDD-HHMMSS-xxxx.php ; key itself may contain hyphens.
            if (preg_match('/^(.+)-\d{8}-\d{6}-[0-9a-f]+\.php$/', basename($f), $m)) {
                $keys[$m[1]] = true;
            }
        }
        $keys = array_keys($keys);
        sort($keys);
        return $keys;
    }

    private static function backup(string $file, string $key): void
    {
        if (!is_file($file)) return;
        if (!is_dir(self::BACKUPS)) mkdir(self::BACKUPS, 0775, true);
        $suffix = date('Ymd-His') . '-' . bin2hex(random_bytes(2));
        copy($file, self::BACKUPS . $key . '-' . $suffix . '.php');
        self::prune($key);
    }

    private static function prune(string $key): void
    {
        $files = glob(self::BACKUPS . $key . '-*.php') ?: [];
        rsort($files);
        foreach (array_slice($files, self::KEEP) as $old) @unlink($old);
    }
}
