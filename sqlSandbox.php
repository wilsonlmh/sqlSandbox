<?php
/*
 * by Wilson Luniz @ Project E
 */

require_once __DIR__ . "/vendor/autoload.php";

class sqlSandbox
{
    public $config = [
        "statementsWhiteList" => ["SELECT", "FROM", "DESCRIBE"],
        "tablesWhiteList" => ["test"],
        "statementsBlackList" => ["DROP", "ROLLBACK", "USE", "ALTER", "INDEX", "INSERT", "UPDATE",],
        "tablesBlackList" => [],
        "filterMode" => "whitelist",
    ];

    //Support MySQL 5.7
    protected $allStatements = ["ALTER", "ANALYZE", "BEGIN", "BINARY", "BINLOG", "CACHE", "CALL", "CASE", "CHANGE", "CHECK", "CHECKSUM", "CLOSE", "COMMIT", "CREATE", "DATA", "DATABASE", "DEALLOCATE", "DECLARE", "DELETE", "DESCRIBE", "DIAGNOSTICS", "DO", "DROP", "END", "EVENT", "EXECUTE", "EXPLAIN", "FETCH", "FILTER", "FLUSH", "FUNCTION", "GET", "GLOBAL", "GRANT", "GROUP", "GROUP_REPLICATION", "HANDLER", "HELP", "IF", "INDEX", "INFILE", "INSERT", "INSTALL", "INSTANCE", "INTO", "ITERATE", "KILL", "LEAVE", "LOAD", "LOCK", "LOGFILE", "LOGS", "LOOP", "MASTER", "OPEN", "OPTIMIZE", "PASSWORD", "PLUGIN", "PREPARE", "PROCEDURE", "PURGE", "RELEASE", "RENAME", "REPAIR", "REPEAT", "REPLACE", "REPLICATION", "RESET", "RESIGNAL", "RETURN", "REVOKE", "ROLLBACK", "SAVEPOINT", "SELECT", "SERVER", "SET", "SHOW", "SHUTDOWN", "SIGNAL", "SLAVE", "START", "STOP", "TABLE", "TABLES", "TABLESPACE", "TO", "TRANSACTION", "TRUNCATE", "UNINSTALL", "UNLOCK", "UPDATE", "USE", "USER", "VIEW", "WHILE", "XML"];
    protected $inputSQL = "";
    protected $outputSQL = "";

    public function __construct($sql = "", $config = [])
    {
        $this->inputSQL = $sql;
        $this->config = array_merge($config, $this->config);
        return $this->parseAndCheck();
    }

    public function parseAndCheck($sql = NULL)
    {
        if (!empty($sql)) {
            $this->inputSQL = $sql;
        }

        $this->outputSQL = $this->inputSQL;
        $parsed = (new \PHPSQLParser\PHPSQLParser($this->outputSQL))->parsed;

        switch ($this->config["filterMode"]) {
            case "whitelist":
                array_walk_recursive($parsed, function ($val, $key) {
                    if ($key == "expr_type" && (in_array($val, $this->allStatements) && !in_array($val, $this->config["statementsWhiteList"]))) {
                        $this->outputSQL = "";
                    }
                    if ($key == "table" && !in_array($val, $this->config["tablesWhiteList"])) {
                        $this->outputSQL = "";
                    }
                });
                array_walk($parsed, function ($val, $key) {
                    if (!in_array($key, $this->config["statementsWhiteList"])) {
                        $this->outputSQL = "";
                    }
                });

                break;
            case "blacklist":
                array_walk_recursive($parsed, function ($val, $key) {
                    if ($key == "expr_type" && !in_array($val, $this->config["statementsBlackList"])) {
                        $this->outputSQL = "";
                    }
                    if ($key == "table" && in_array($val, $this->config["tablesWhileList"])) {
                        $this->outputSQL = "";
                    }
                });
                array_walk($parsed, function ($val, $key) {
                    if (in_array($key, $this->config["statementsBlackList"])) {
                        $this->outputSQL = "";
                    }
                });
                break;
            default:
                $this->outputSQL = "";
        }
        return $this->outputSQL;

    }
}

//Example:
print_r((new sqlSandbox("SELECT * FROM test;"))->parseAndCheck());

/*
 * For secure implementation:
 *  - Create a limited user who only able to read certian DB
 *  - Run SQL like 'CREATE TEMPORARY TABLE IF NOT EXISTS table2 AS (SELECT * FROM table1);' to create temporary table(s)
 *  - Use sqlSandbox::config["tablesWhiteList"] to limit user can only access templorary table(s) created above
 *  - run sqlSandbox::parseAndCheck() to filter out statements
 *  - execute output of parseAndCheck()
 */

