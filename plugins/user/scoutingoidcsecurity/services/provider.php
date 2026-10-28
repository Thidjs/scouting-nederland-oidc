<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\User\Scoutingoidcsecurity\Extension\Scoutingoidcsecurity;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Scoutingoidcsecurity(
                    (array) PluginHelper::getPlugin(
                        'user',
                        'scoutingoidcsecurity'
                    )
                );

                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase(
                    $container->get(DatabaseInterface::class)
                );

                return $plugin;
            }
        );
    }
};
