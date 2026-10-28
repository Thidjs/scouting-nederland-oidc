<?php

namespace Joomla\Plugin\System\Scoutingoidc\Service;

defined('_JEXEC') or die;

use InvalidArgumentException;
use Joomla\Database\DatabaseInterface;

final class ScoutingIdentityService
{
    public function __construct(
        private DatabaseInterface $db
    ) {
    }

    /**
     * Validate a previously verified OIDC identity.
     * This method does not verify JWT signatures itself.
     */
    public function validateClaims(array $claims): array
    {
        $issuer = $claims['iss'] ?? null;
        $subject = $claims['sub'] ?? null;
        $memberId = $claims['member_id'] ?? null;

        if ($issuer !== 'https://login.scouting.nl') {
            throw new InvalidArgumentException('Unexpected OIDC issuer');
        }

        if (
            !is_string($subject)
            || $subject === ''
            || strlen($subject) > 191
        ) {
            throw new InvalidArgumentException('Invalid OIDC subject');
        }

        // SOL returns member_id as an integer.
        if (is_int($memberId)) {
            $memberId = (string) $memberId;
        }

        if (
            !is_string($memberId)
            || !preg_match('/^[1-9][0-9]*$/D', $memberId)
            || strlen($memberId) > 150
        ) {
            throw new InvalidArgumentException('Invalid SOL member_id');
        }

        return [
            'issuer' => $issuer,
            'subject' => $subject,
            'member_id' => $memberId,
        ];
    }

    /**
     * Inspect identity bindings without creating or changing data.
     *
     * Possible outcomes:
     * NEW      No existing OIDC binding or Joomla username.
     * KNOWN    Existing matching binding and Joomla account.
     * CONFLICT Existing data needs manual investigation.
     */
    public function inspect(array $identity, int $serverId): array
    {
        if ($serverId <= 0) {
            throw new InvalidArgumentException('Invalid server ID');
        }

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select(['user_id', 'member_id', 'server_id'])
            ->from($db->quoteName('#__oidc_identities'))
            ->where(
                $db->quoteName('issuer') . ' = ' .
                $db->quote($identity['issuer'])
            )
            ->where(
                $db->quoteName('subject') . ' = ' .
                $db->quote($identity['subject'])
            );

        $db->setQuery($query);
        $existingIdentity = $db->loadAssoc();

        // Check whether the member ID is already bound to another identity.
        $query = $db->getQuery(true)
            ->select('user_id')
            ->from($db->quoteName('#__oidc_identities'))
            ->where(
                $db->quoteName('server_id') . ' = ' . $serverId
            )
            ->where(
                $db->quoteName('member_id') . ' = ' .
                $db->quote($identity['member_id'])
            );

        $db->setQuery($query);
        $memberBinding = $db->loadResult();

        // Also protect pre-existing Joomla usernames.
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__users'))
            ->where(
                $db->quoteName('username') . ' = ' .
                $db->quote($identity['member_id'])
            );

        $db->setQuery($query);
        $existingUserId = $db->loadResult();

        if ($existingIdentity !== null && $existingIdentity !== false) {
            $userId = (int) $existingIdentity['user_id'];

            if (
                (int) $existingIdentity['server_id'] !== $serverId
                || $existingIdentity['member_id'] !== $identity['member_id']
                || (int) $existingUserId !== $userId
                || (int) $memberBinding !== $userId
            ) {
                return ['status' => 'CONFLICT', 'user_id' => null];
            }

            // Require an existing External Login server binding too.
            $query = $db->getQuery(true)
                ->select('server_id')
                ->from($db->quoteName('#__externallogin_users'))
                ->where($db->quoteName('user_id') . ' = ' . $userId);

            $db->setQuery($query);
            $linkedServer = $db->loadResult();

            if ((int) $linkedServer !== $serverId) {
                return ['status' => 'CONFLICT', 'user_id' => null];
            }

            return ['status' => 'KNOWN', 'user_id' => $userId];
        }

        if ($memberBinding !== null || $existingUserId !== null) {
            return ['status' => 'CONFLICT', 'user_id' => null];
        }

        return ['status' => 'NEW', 'user_id' => null];
    }
}
