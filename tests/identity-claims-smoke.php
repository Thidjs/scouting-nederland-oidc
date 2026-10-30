<?php
/**
 * Dependency-free smoke test for ScoutingIdentityService::validateClaims().
 *
 * Run from repository root: php tests/identity-claims-smoke.php
 * Does NOT test token signatures, persistence or the Joomla login flow.
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once __DIR__ . '/../plugins/system/scoutingoidc/src/Service/ScoutingIdentityService.php';

use Joomla\Plugin\System\Scoutingoidc\Service\ScoutingIdentityService;

$service = (new ReflectionClass(ScoutingIdentityService::class))
    ->newInstanceWithoutConstructor();

$base = [
    'iss' => 'https://login.scouting.nl',
    'sub' => 'scouting-subject-123',
    'member_id' => 12345,
    'email' => 'example@example.invalid',
];

$cases = [
    ['verified true', $base + ['email_verified' => true], true],
    ['verified false', $base + ['email_verified' => false], true],
    ['verification missing', $base, true],
    ['wrong issuer', array_replace($base, ['iss' => 'https://other.example']), false],
    ['missing subject', array_replace($base, ['sub' => '']), false],
    ['invalid member id', array_replace($base, ['member_id' => 'abc']), false],
    ['zero member id', array_replace($base, ['member_id' => 0]), false],
];

$failures = 0;
foreach ($cases as [$label, $claims, $shouldPass]) {
    try {
        $identity = $service->validateClaims($claims);
        $passed = $shouldPass
            && $identity['issuer'] === 'https://login.scouting.nl'
            && $identity['subject'] === 'scouting-subject-123'
            && $identity['member_id'] === '12345';
    } catch (InvalidArgumentException $e) {
        $passed = !$shouldPass;
    }

    echo ($passed ? 'PASS' : 'FAIL') . ': ' . $label . PHP_EOL;
    if (!$passed) {
        ++$failures;
    }
}

exit($failures === 0 ? 0 : 1);
