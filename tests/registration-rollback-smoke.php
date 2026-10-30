<?php
/**
 * Dependency-free transaction rollback smoke tests for ScoutingRegistrationService.
 * Run: php tests/registration-rollback-smoke.php
 * Uses no real database and deliberately fails before Joomla user creation.
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace ScoutingTest {
    final class TransactionDatabase implements \Joomla\Database\DatabaseInterface {
        public int $starts = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public function transactionStart(): void { ++$this->starts; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once __DIR__ . '/../plugins/system/scoutingoidc/src/Service/ScoutingIdentityService.php';
    require_once __DIR__ . '/../plugins/system/scoutingoidc/src/Service/ScoutingRegistrationService.php';

    $base = [
        'iss' => 'https://login.scouting.nl',
        'sub' => 'test-sub',
        'member_id' => 12345,
        'email' => 'tester@example.invalid',
    ];

    $cases = [
        ['invalid issuer', array_replace($base, ['iss' => 'https://invalid.example']), 'tester@example.invalid', 'Test User', 7, true],
        ['invalid email syntax', $base, 'invalid-email', 'Test User', 7, true],
        ['email mismatch', $base, 'other@example.invalid', 'Test User', 7, true],
        ['empty full name', $base, 'tester@example.invalid', '', 7, true],
        ['invalid server ID before transaction', $base, 'tester@example.invalid', 'Test User', 0, false],
    ];

    $failed = 0;
    foreach ($cases as [$label, $claims, $email, $name, $serverId, $shouldStart]) {
        $db = new \ScoutingTest\TransactionDatabase();
        $identity = new \Joomla\Plugin\System\Scoutingoidc\Service\ScoutingIdentityService($db);
        $service = new \Joomla\Plugin\System\Scoutingoidc\Service\ScoutingRegistrationService($db, $identity);

        $thrown = false;
        try {
            $service->register($claims, $email, $name, $serverId);
        } catch (\Throwable $e) {
            $thrown = true;
        }

        $expected = $shouldStart ? 1 : 0;
        $ok = $thrown && $db->starts === $expected
            && $db->rollbacks === $expected && $db->commits === 0;
        echo ($ok ? 'PASS' : 'FAIL') . ": $label (start={$db->starts}, rollback={$db->rollbacks}, commit={$db->commits})\n";
        if (!$ok) ++$failed;
    }
    exit($failed === 0 ? 0 : 1);
}
