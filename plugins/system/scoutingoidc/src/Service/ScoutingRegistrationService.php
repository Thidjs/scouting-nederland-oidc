<?php

namespace Joomla\Plugin\System\Scoutingoidc\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use RuntimeException;
use Throwable;

final class ScoutingRegistrationService
{
    private const REGISTERED_GROUP_ID = 2;

    public function __construct(
        private DatabaseInterface $db,
        private ScoutingIdentityService $identityService
    ) {
    }

    /**
     * Production entry point.
     *
     * Must only receive claims from a verified Scouting OIDC flow.
     * Must not be called inside an existing transaction.
     */
    public function register(
        array $verifiedClaims,
        string $email,
        string $fullName,
        int $serverId
    ): int {
        if ($serverId <= 0) {
            throw new RuntimeException('Invalid Scouting server ID');
        }
        $transactionStarted = false;

        try {
            $this->db->transactionStart();
            $transactionStarted = true;

            $userId = $this->persistRegistration(
                $verifiedClaims,
                $email,
                $fullName,
                $serverId
            );

            $this->db->transactionCommit();
            $transactionStarted = false;

            return $userId;

        } catch (Throwable $e) {
            if ($transactionStarted) {
                try {
                    $this->db->transactionRollback();
                } catch (Throwable $rollbackError) {
                    throw new RuntimeException(
                        'Scouting registration rollback failed',
                        0,
                        $rollbackError
                    );
                }
            }

            throw $e;
        }
    }

    /**
     * Persist a new identity within an ALREADY active transaction.
     *
     * No transactionStart, transactionCommit or transactionRollback.
     * Private by design; used internally by register().
     *
     * Claims must have been cryptographically verified upstream.
     */
    private function persistRegistration(
        array $verifiedClaims,
        string $email,
        string $fullName,
        int $serverId
    ): int {
        $identity = $this->identityService->validateClaims(
            $verifiedClaims
        );

        $username = $identity['member_id'];
        $email = trim($email);
        $fullName = trim($fullName);

        if (
            strlen($email) > 100
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || $fullName === ''
            || strlen($fullName) > 400
        ) {
            throw new RuntimeException(
                'Invalid Scouting registration data'
            );
        }

        // The email must come from the same verified claims.
        if (
            !isset($verifiedClaims['email'])
            || !is_string($verifiedClaims['email'])
            || $verifiedClaims['email'] !== $email
        ) {
            throw new RuntimeException(
                'Scouting registration email mismatch'
            );
        }

        $db = $this->db;

        $inspection = $this->identityService->inspect(
            $identity,
            $serverId
        );

        if ($inspection['status'] !== 'NEW') {
            throw new RuntimeException(
                'Scouting identity is not available for registration'
            );
        }

        // Never attach a new identity to an existing email account.
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__users'))
            ->where(
                $db->quoteName('email') . ' = ' .
                $db->quote($email)
            );

        $db->setQuery($query);

        if ((int) $db->loadResult() !== 0) {
            throw new RuntimeException(
                'Email address is already registered'
            );
        }

        $userFactory = Factory::getContainer()->get(
            UserFactoryInterface::class
        );

        $user = $userFactory->loadUserById(0);

        $user->set('id', 0);
        $user->set('name', $fullName);
        $user->set('username', $username);
        $user->set('email', $email);

        // Random password; OIDC login does not use this password.
        $user->set('password', bin2hex(random_bytes(32)));
        $user->set('block', 0);
        $user->set('activation', '');
        $user->set('groups', [self::REGISTERED_GROUP_ID]);

        if (!$user->save()) {
            throw new RuntimeException(
                'Joomla user save failed'
            );
        }

        $userId = (int) $user->id;

        if ($userId <= 0) {
            throw new RuntimeException(
                'Invalid Joomla user ID'
            );
        }

        $externalBinding = (object) [
            'server_id' => $serverId,
            'user_id' => $userId,
        ];

        $db->insertObject(
            '#__externallogin_users',
            $externalBinding
        );

        $oidcBinding = (object) [
            'user_id' => $userId,
            'server_id' => $serverId,
            'issuer' => $identity['issuer'],
            'subject' => $identity['subject'],
            'member_id' => $identity['member_id'],
        ];

        $db->insertObject(
            '#__oidc_identities',
            $oidcBinding
        );

        $inspection = $this->identityService->inspect(
            $identity,
            $serverId
        );

        if (
            $inspection['status'] !== 'KNOWN'
            || (int) $inspection['user_id'] !== $userId
        ) {
            throw new RuntimeException(
                'Scouting identity binding verification failed'
            );
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__user_usergroup_map'))
            ->where(
                $db->quoteName('user_id') . ' = ' . $userId
            )
            ->where(
                $db->quoteName('group_id') . ' = ' .
                self::REGISTERED_GROUP_ID
            );

        $db->setQuery($query);

        if ((int) $db->loadResult() !== 1) {
            throw new RuntimeException(
                'Joomla user group verification failed'
            );
        }

        return $userId;
    }
}
