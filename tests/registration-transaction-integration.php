<?php
/**
 * Isolated InnoDB transaction/rollback smoke test.
 *
 * Run from Joomla root:
 *   php /tmp/scouting-oidc-issue3/tests/registration-transaction-integration.php
 *
 * Uses connection-local TEMPORARY tables ONLY; never writes Joomla tables.
 * This proves InnoDB rollback semantics, not Joomla User::save() transaction sharing.
 */
declare(strict_types=1);

$joomlaRoot = getcwd();
$configFile = $joomlaRoot . '/configuration.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "FAIL: Run from Joomla root (configuration.php missing).\n");
    exit(1);
}
require_once $configFile;
$config = new JConfig();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = null;
$tables = [];
$started = false;
$passed = false;

try {
    $db = new mysqli($config->host, $config->user, $config->password, $config->db);
    $db->set_charset('utf8mb4');

    // Names are random and connection-local, and cannot shadow existing Joomla tables.
    $suffix = bin2hex(random_bytes(6));
    foreach (['users', 'groups', 'external', 'oidc', 'profiles'] as $key) {
        $tables[$key] = 'tmp_scouting_rollback_' . $key . '_' . $suffix;
    }

    foreach ($tables as $name) {
        $db->query("CREATE TEMPORARY TABLE `$name` (
            id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
            marker VARCHAR(64) NOT NULL
        ) ENGINE=InnoDB");
    }

    // Ensure the temporary tables really support transactions.
    $db->begin_transaction();
    $started = true;
    foreach ($tables as $name) {
        $db->query("INSERT INTO `$name` (id, marker) VALUES (1, 'rollback-test')");
    }

    // Simulate a failure while writing the identity binding, after other writes.
    $oidc = $tables['oidc'];
    $db->query("INSERT INTO `$oidc` (id, marker) VALUES (1, 'forced-duplicate')");
    throw new RuntimeException('FAIL: Duplicate-key injection unexpectedly succeeded');
} catch (mysqli_sql_exception $e) {
    if (!$started || (int) $e->getCode() !== 1062) {
        fwrite(STDERR, "FAIL: Unexpected database error (code {$e->getCode()}).\n");
    } else {
        echo "PASS: Simulated OIDC identity write failed with duplicate key (1062).\n";
        $passed = true;
    }
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: " . $e->getMessage() . "\n");
} finally {
    if ($db !== null) {
        if ($started) {
            try {
                $db->rollback();
                echo "INFO: Transaction rollback executed.\n";
            } catch (Throwable $e) {
                $passed = false;
                fwrite(STDERR, "FAIL: Rollback failed.\n");
            }
        }

        // Verify rollback on the same connection while temp tables still exist.
        if ($passed) {
            try {
                foreach ($tables as $key => $name) {
                    $result = $db->query("SELECT COUNT(*) AS total FROM `$name`");
                    $count = (int) $result->fetch_assoc()['total'];
                    if ($count !== 0) {
                        $passed = false;
                        echo "FAIL: $key still contains $count record(s).\n";
                    } else {
                        echo "PASS: $key has 0 records after rollback.\n";
                    }
                }
            } catch (Throwable $e) {
                $passed = false;
                fwrite(STDERR, "FAIL: Post-rollback verification failed.\n");
            }
        }

        foreach ($tables as $name) {
            try {
                $db->query("DROP TEMPORARY TABLE IF EXISTS `$name`");
            } catch (Throwable $ignored) {
                // Connection close will also remove temporary tables.
            }
        }
        $db->close();
    }
}
echo $passed ? "PASS: Isolated InnoDB rollback test completed.\n" : "FAIL: Isolated InnoDB rollback test did not pass.\n";
exit($passed ? 0 : 1);
