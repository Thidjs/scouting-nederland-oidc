<?php

namespace Joomla\Plugin\System\Scoutingoidc\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\AuthenticationResponse;
use RuntimeException;
use WeakMap;

/**
 * One-time, request-local proof of a verified Scouting OIDC login.
 *
 * Never store this proof in sessions, cookies or persistent storage.
 */
final class ScoutingAuthenticationProof
{
    private static ?WeakMap $proofs = null;

    public static function issue(
        AuthenticationResponse $response,
        string $username,
        int $serverId,
        string $issuer,
        string $subject,
        string $memberId
    ): void {
        if (
            $username === ''
            || $serverId <= 0
            || $issuer !== 'https://login.scouting.nl'
            || $subject === ''
            || $memberId === ''
            || $username !== $memberId
        ) {
            throw new RuntimeException(
                'Invalid Scouting authentication proof'
            );
        }

        self::$proofs ??= new WeakMap();

        self::$proofs[$response] = [
            'username' => $username,
            'server_id' => $serverId,
            'issuer' => $issuer,
            'subject' => $subject,
            'member_id' => $memberId,
        ];
    }

    /**
     * Consume the proof exactly once for the same response object.
     */
    public static function consume(
        AuthenticationResponse $response
    ): ?array {
        if (
            self::$proofs === null
            || !isset(self::$proofs[$response])
        ) {
            return null;
        }

        $proof = self::$proofs[$response];

        unset(self::$proofs[$response]);

        return $proof;
    }
}
