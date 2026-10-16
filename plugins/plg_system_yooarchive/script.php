<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Installation script: checks PHP and Joomla versions, enables the plugin on the
 * first install and warns when YOOtheme Pro is not installed.
 *
 * @since  1.0.0
 */
class PlgSystemYooarchiveInstallerScript extends InstallerScript
{
    /**
     * @var    string
     * @since  1.0.0
     */
    protected $minimumPhp = '8.1.0';

    /**
     * @var    string
     * @since  1.0.0
     */
    protected $minimumJoomla = '5.0.0';

    /**
     * @param   string            $type    install, update or discover_install.
     * @param   InstallerAdapter  $parent  The installer adapter.
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function postflight($type, $parent)
    {
        $app = Factory::getApplication();
        $app->getLanguage()->load('plg_system_yooarchive.sys', JPATH_PLUGINS . '/system/yooarchive');

        if ($type === 'install' || $type === 'discover_install') {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('yooarchive'));
            $db->setQuery($query)->execute();
        }

        if (!is_file(JPATH_ROOT . '/templates/yootheme/template_bootstrap.php')) {
            $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_INSTALL_NO_YOOTHEME'), 'warning');
        }

        return true;
    }
}
