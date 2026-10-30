<?php
/**
 * Dependency-free, in-memory tests of the real ScoutingIdentityService::inspect().
 * Run: php tests/identity-inspection-smoke.php
 * No Joomla bootstrap or database connection is required.
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace ScoutingTest {
    final class Query {
        public string $table = '';
        public array $where = [];
        public function select($columns): self { return $this; }
        public function from($table): self { $this->table = trim($table, '`'); return $this; }
        public function where($condition): self { $this->where[] = $condition; return $this; }
    }

    final class FakeDatabase implements \Joomla\Database\DatabaseInterface {
        private Query $query;
        public function __construct(
            private array $identities,
            private array $users,
            private array $externalBindings
        ) {}
        public function getQuery(bool $new = true): Query { return new Query(); }
        public function quoteName(string $name): string { return '`' . $name . '`'; }
        public function quote(string $value): string { return "'" . str_replace("'", "''", $value) . "'"; }
        public function setQuery(Query $query): void { $this->query = $query; }
        private function matches(array $row): bool {
            foreach ($this->query->where as $condition) {
                if (!preg_match('/^`(\\w+)`\\s*=\\s*(?:\'((?:\'\'|[^\'])*)\'|(\\d+))$/', $condition, $m)) {
                    throw new \RuntimeException('Unexpected query condition: ' . $condition);
                }
                $expected = isset($m[3]) && $m[3] !== '' ? $m[3] : str_replace("''", "'", $m[2]);
                if (!array_key_exists($m[1], $row) || (string) $row[$m[1]] !== (string) $expected) {
                    return false;
                }
            }
            return true;
        }
        private function rows(): array {
            $table = $this->query->table;
            $rows = match ($table) {
                '#__oidc_identities' => $this->identities,
                '#__users' => $this->users,
                '#__externallogin_users' => $this->externalBindings,
                default => throw new \RuntimeException('Unexpected table: ' . $table),
            };
            return array_values(array_filter($rows, fn(array $row) => $this->matches($row)));
        }
        public function loadAssoc(): ?array { return $this->rows()[0] ?? null; }
        public function loadResult(): mixed {
            $row = $this->rows()[0] ?? null;
            if ($row === null) return null;
            // inspect() selects user_id, id, or server_id depending on table.
            return match ($this->query->table) {
                '#__users' => $row['id'],
                '#__externallogin_users' => $row['server_id'],
                default => $row['user_id'],
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once __DIR__ . '/../plugins/system/scoutingoidc/src/Service/ScoutingIdentityService.php';

    $identity = ['issuer' => 'https://login.scouting.nl', 'subject' => 'sub-1', 'member_id' => '12345'];
    $binding = ['issuer' => $identity['issuer'], 'subject' => $identity['subject'],
        'member_id' => $identity['member_id'], 'user_id' => 10, 'server_id' => 7];
    $user = ['id' => 10, 'username' => '12345'];
    $external = ['user_id' => 10, 'server_id' => 7];

    $cases = [
        ['NEW without collisions', [], [], [], 'NEW'],
        ['KNOWN with consistent bindings', [$binding], [$user], [$external], 'KNOWN'],
        ['CONFLICT existing username', [], [$user], [], 'CONFLICT'],
        ['CONFLICT existing member binding', [array_replace($binding, ['subject' => 'other-sub'])], [$user], [$external], 'CONFLICT'],
        ['CONFLICT identity wrong server', [array_replace($binding, ['server_id' => 8])], [$user], [$external], 'CONFLICT'],
        ['CONFLICT identity wrong member', [array_replace($binding, ['member_id' => '98765'])], [$user], [$external], 'CONFLICT'],
        ['CONFLICT missing Joomla user', [$binding], [], [$external], 'CONFLICT'],
        ['CONFLICT missing external binding', [$binding], [$user], [], 'CONFLICT'],
        ['CONFLICT external binding wrong server', [$binding], [$user], [['user_id' => 10, 'server_id' => 8]], 'CONFLICT'],
    ];

    $failed = 0;
    foreach ($cases as [$label, $identities, $users, $externals, $expected]) {
        $service = new \Joomla\Plugin\System\Scoutingoidc\Service\ScoutingIdentityService(
            new \ScoutingTest\FakeDatabase($identities, $users, $externals)
        );
        try {
            $actual = $service->inspect($identity, 7)['status'];
            $ok = $actual === $expected;
            echo ($ok ? 'PASS' : 'FAIL') . ": $label (expected=$expected, actual=$actual)\n";
        } catch (\Throwable $e) {
            $ok = false;
            echo "FAIL: $label (" . $e->getMessage() . ")\n";
        }
        if (!$ok) ++$failed;
    }
    exit($failed === 0 ? 0 : 1);
}
