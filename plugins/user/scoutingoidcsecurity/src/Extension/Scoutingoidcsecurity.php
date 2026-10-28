<?php

namespace Joomla\Plugin\User\Scoutingoidcsecurity\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\User\AuthorisationEvent;
use Joomla\CMS\Event\User\BeforeResetCompleteEvent;
use Joomla\CMS\Event\User\BeforeResetRequestEvent;
use Joomla\CMS\Event\User\BeforeSaveEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use RuntimeException;

final class Scoutingoidcsecurity extends CMSPlugin implements
    SubscriberInterface,
    DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAuthorisation' =>
                'onUserAuthorisation',
            'onUserBeforeResetRequest' =>
                'onUserBeforeResetRequest',
            'onUserBeforeResetComplete' =>
                'onUserBeforeResetComplete',
            'onUserBeforeSave' =>
                'onUserBeforeSave',
        ];
    }

    private function isScoutingUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__oidc_identities'))
            ->where(
                $db->quoteName('user_id') . ' = ' . $userId
            )
            ->where(
                $db->quoteName('issuer') . ' = ' .
                $db->quote('https://login.scouting.nl')
            );

        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    private function getUserIdByUsername(string $username): int
    {
        if ($username === '') {
            return 0;
        }

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where(
                $db->quoteName('username') . ' = ' .
                $db->quote($username)
            );

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Reject Joomla password authentication for Scouting accounts.
     */
    public function onUserAuthorisation(
        AuthorisationEvent $event
    ): void {
        $response = $event->getAuthenticationResponse();
        $username = (string) $response->username;

        $userId = $this->getUserIdByUsername($username);

        // Ordinary Joomla users remain unaffected.
        if (!$this->isScoutingUser($userId)) {
            return;
        }

        $allowed = false;

        // Only the External Login bridge may complete a Scouting login.
        if (
            $response->status === Authentication::STATUS_SUCCESS
            && $response->type === 'externallogin'
        ) {
            $proofFile = JPATH_PLUGINS
                . '/system/scoutingoidc/src/Service/'
                . 'ScoutingAuthenticationProof.php';

            if (is_file($proofFile)) {
                require_once $proofFile;

                $proof = \Joomla\Plugin\System\Scoutingoidc\Service\ScoutingAuthenticationProof::consume(
                    $response
                );

                if (
                    is_array($proof)
                    && ($proof['username'] ?? null) === $username
                    && ($proof['member_id'] ?? null) === $username
                    && ($proof['issuer'] ?? null) === 'https://login.scouting.nl'
                    && is_string($proof['subject'] ?? null)
                    && $proof['subject'] !== ''
                    && is_int($proof['server_id'] ?? null)
                    && $proof['server_id'] > 0
                ) {
                    // Require BOTH identity bindings to match the proof.
                    $db = $this->getDatabase();

                    $query = $db->getQuery(true)
                        ->select('COUNT(*)')
                        ->from($db->quoteName('#__oidc_identities', 'i'))
                        ->innerJoin(
                            $db->quoteName('#__externallogin_users', 'e')
                            . ' ON ' . $db->quoteName('e.user_id')
                            . ' = ' . $db->quoteName('i.user_id')
                            . ' AND ' . $db->quoteName('e.server_id')
                            . ' = ' . $db->quoteName('i.server_id')
                        )
                        ->where(
                            $db->quoteName('i.user_id') . ' = ' . $userId
                        )
                        ->where(
                            $db->quoteName('i.server_id') . ' = '
                            . (int) $proof['server_id']
                        )
                        ->where(
                            $db->quoteName('i.issuer') . ' = '
                            . $db->quote($proof['issuer'])
                        )
                        ->where(
                            $db->quoteName('i.subject') . ' = '
                            . $db->quote($proof['subject'])
                        )
                        ->where(
                            $db->quoteName('i.member_id') . ' = '
                            . $db->quote($proof['member_id'])
                        );

                    $db->setQuery($query);
                    $allowed = (int) $db->loadResult() === 1;
                }
            }
        }

        if ($allowed) {
            return;
        }

        // Fail closed for any other authentication route.
        $response->status = Authentication::STATUS_DENIED;
        $response->error_message =
            'Gebruik Scouting Nederland om in te loggen.';

        $event->addResult($response);
    }

    public function onUserBeforeResetRequest(
        BeforeResetRequestEvent $event
    ): void {
        $user = $event->getUser();

        if ($this->isScoutingUser((int) $user->id)) {
            throw new RuntimeException(
                'Wachtwoordherstel is niet beschikbaar voor Scouting-accounts.'
            );
        }
    }

    public function onUserBeforeResetComplete(
        BeforeResetCompleteEvent $event
    ): void {
        $user = $event->getUser();

        if ($this->isScoutingUser((int) $user->id)) {
            throw new RuntimeException(
                'Wachtwoordherstel is niet beschikbaar voor Scouting-accounts.'
            );
        }
    }

    public function onUserBeforeSave(
        BeforeSaveEvent $event
    ): void {
        if ($event->getIsNew()) {
            return;
        }

        $old = $event->getUser();
        $new = $event->getData();

        $userId = (int) ($old['id'] ?? 0);

        if (!$this->isScoutingUser($userId)) {
            return;
        }

        // Only deny a change to the stored password hash.
        // Ordinary profile updates remain possible.
        $oldPassword = (string) ($old['password'] ?? '');
        $newPassword = (string) (
            $new['password'] ?? $oldPassword
        );

        if ($newPassword !== $oldPassword) {
            $event->addResult(false);
        }
    }
}
