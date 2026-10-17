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
use Joomla\Registry\Registry;

/**
 * Installation script: checks PHP and Joomla versions, enables the plugin on the
 * first install, warns when YOOtheme Pro is not installed and, on an update from 1.x,
 * stops the update while the archive automation is on and Auto Archive is not installed.
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
     * Files of the 1.x automation that Joomla does not remove on update.
     *
     * @var    string[]
     * @since  2.0.0
     */
    protected $deleteFiles = ['/plugins/system/yooarchive/src/Automation/Automation.php'];

    /**
     * @var    string[]
     * @since  2.0.0
     */
    protected $deleteFolders = ['/plugins/system/yooarchive/src/Automation'];

    /**
     * Version 2.0.0 no longer contains the archive automation (it is the separate plugin
     * Auto Archive). A site that has the automation switched on would silently lose it, so the
     * update is refused, with nothing changed, until Auto Archive is installed and enabled:
     * Auto Archive imports the old settings when it is installed.
     *
     * @param   string            $type    install, update or discover_install.
     * @param   InstallerAdapter  $parent  The installer adapter.
     *
     * @return  boolean
     *
     * @since   2.0.0
     */
    public function preflight($type, $parent)
    {
        if (!parent::preflight($type, $parent)) {
            return false;
        }

        if ($type !== 'update') {
            return true;
        }

        $db  = Factory::getContainer()->get(DatabaseInterface::class);
        $old = $this->getPlugin($db, 'yooarchive');

        // Only an update from 1.x matters: 2.0 and later hold no automation.
        if (!$old || !$this->isLegacy($old)) {
            return true;
        }

        $oldParams = $this->params($old);

        // Nothing to carry over when the automation is off or has no category.
        if (!(int) $oldParams->get('automation', 0) || (int) $oldParams->get('target_catid', 0) <= 0) {
            return true;
        }

        $new = $this->getPlugin($db, 'autoarchive');

        // Auto Archive must be there, enabled and with its automation switched on.
        if ($new && (int) $new->enabled === 1 && (int) $this->params($new)->get('automation', 0) === 1) {
            return true;
        }

        $app  = Factory::getApplication();
        $path = $parent->getParent()->getPath('source');

        // The new language files are still in the package folder at this point.
        $app->getLanguage()->load('plg_system_yooarchive.sys', $path);
        $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_UPDATE_BLOCKED'), 'error');

        return false;
    }

    /**
     * @param   object  $plugin  A row from getPlugin().
     *
     * @return  Registry  The parameters (empty when the stored JSON is not valid).
     *
     * @since   2.0.0
     */
    private function params(object $plugin): Registry
    {
        try {
            return new Registry($plugin->params ?: '{}');
        } catch (\Throwable $e) {
            return new Registry();
        }
    }

    /**
     * @param   object  $plugin  A row from getPlugin().
     *
     * @return  bool  True when the installed version is older than 2.0.0.
     *
     * @since   2.0.0
     */
    private function isLegacy(object $plugin): bool
    {
        try {
            $version = (string) (new Registry($plugin->manifest_cache ?: '{}'))->get('version', '');
        } catch (\Throwable $e) {
            return false;
        }

        return $version !== '' && version_compare($version, '2.0.0', '<');
    }

    /**
     * @param   DatabaseInterface  $db       The database.
     * @param   string             $element  The system plugin element.
     *
     * @return  object|null  params and enabled flag of the plugin, or null when not installed.
     *
     * @since   2.0.0
     */
    private function getPlugin(DatabaseInterface $db, string $element): ?object
    {
        $query = $db->createQuery()
            ->select($db->quoteName(['params', 'enabled', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':element', $element);

        return $db->setQuery($query)->loadObject() ?: null;
    }

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

        if ($type === 'update') {
            $this->removeFiles();
        }

        if ($type === 'update' && $this->hasOldAutomationSettings()) {
            $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_UPDATE_AUTOMATION_MOVED'), 'info');
        }

        return true;
    }

    /**
     * Whether Auto Archive is installed and this plugin still holds settings from the
     * 1.x automation: they are no longer used here.
     *
     * @return  bool
     *
     * @since   2.0.0
     */
    private function hasOldAutomationSettings(): bool
    {
        $db  = Factory::getContainer()->get(DatabaseInterface::class);
        $old = $this->getPlugin($db, 'yooarchive');

        return $old && (int) $this->params($old)->get('target_catid', 0) > 0
            && $this->getPlugin($db, 'autoarchive') !== null;
    }
}
