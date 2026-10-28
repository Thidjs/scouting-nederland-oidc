<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Database\DatabaseInterface;

class pkg_scoutingoidcInstallerScript
{
    public function preflight(
        $route,
        InstallerAdapter $adapter
    ): bool {
        if (!in_array($route, ['install', 'update'], true)) {
            return true;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select(['element', 'manifest_cache'])
            ->from($db->quoteName('#__extensions'))
            ->where(
                $db->quoteName('type') . ' = '
                . $db->quote('component')
            )
            ->where(
                $db->quoteName('element') . ' = '
                . $db->quote('com_externallogin')
            );

        $db->setQuery($query);
        $component = $db->loadAssoc();

        $metadata = $component
            ? json_decode((string) $component['manifest_cache'], true)
            : null;

        $version = is_array($metadata)
            ? ($metadata['version'] ?? null)
            : null;

        if ($version !== '5.2.0') {
            return $this->deny(
                'Scouting Nederland OIDC 1.0.0 vereist '
                . 'External Login 5.2.0. '
                . 'Aangetroffen versie: '
                . (is_string($version) ? $version : 'niet geïnstalleerd')
            );
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__extensions'))
            ->where(
                $db->quoteName('type') . ' = '
                . $db->quote('plugin')
            )
            ->where(
                $db->quoteName('folder') . ' = '
                . $db->quote('authentication')
            )
            ->where(
                $db->quoteName('element') . ' = '
                . $db->quote('externallogin')
            );

        $db->setQuery($query);

        if ((int) $db->loadResult() !== 1) {
            return $this->deny(
                'De External Login-authenticatieplugin ontbreekt.'
            );
        }

        return true;
    }

    private function deny(string $message): bool
    {
        Factory::getApplication()->enqueueMessage(
            $message,
            'error'
        );

        return false;
    }
}
