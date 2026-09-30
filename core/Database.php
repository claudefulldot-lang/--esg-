<?php
namespace App;

use PDO;
use PDOException;

class PrefixedPDO extends PDO {
    private string $prefix = '';
    private static array $tables = [
        'sys_organizations',
        'sys_roles',
        'sys_users',
        'sys_audit_logs',
        'sys_settings',
        'esg_emission_factors',
        'esg_ghg_records',
        'esg_energy_water_records',
        'esg_waste_records',
        'esg_social_records',
        'esg_governance_records',
        'esg_workflow_tasks',
        'esg_workflow_logs',
        'esg_attachments',
    ];

    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null, string $prefix = '') {
        parent::__construct($dsn, $username, $password, $options);
        $this->prefix = $prefix;
    }

    public function prefixSql(string $statement): string {
        if (empty($this->prefix)) {
            return $statement;
        }
        $pattern = '/(?<!' . preg_quote($this->prefix, '/') . ')\b(' . implode('|', self::$tables) . ')\b/';
        return preg_replace($pattern, $this->prefix . '$1', $statement);
    }

    public function prepare(string $query, array $options = []): \PDOStatement|false {
        return parent::prepare($this->prefixSql($query), $options);
    }

    public function query(string $query, ?int $fetchMode = null, ...$fetchModeArgs): \PDOStatement|false {
        $prefixed = $this->prefixSql($query);
        if ($fetchMode !== null) {
            return parent::query($prefixed, $fetchMode, ...$fetchModeArgs);
        }
        return parent::query($prefixed);
    }

    public function exec(string $statement): int|false {
        return parent::exec($this->prefixSql($statement));
    }
}

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                $prefix = $config['prefix'] ?? '';
                if (!empty($prefix)) {
                    self::$instance = new PrefixedPDO($dsn, $config['username'], $config['password'], $config['options'], $prefix);
                } else {
                    self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                }
            } catch (PDOException $e) {
                die("資料庫連線失敗: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function resetConnection(): void {
        self::$instance = null;
    }
}
