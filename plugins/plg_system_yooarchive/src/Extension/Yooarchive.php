<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin "Archive Layouts for YOOtheme".
 *
 * Registers a YOOtheme Pro builder template type for the Joomla archive view
 * (com_content.archive) with an "Archived Articles" dynamic source. The archive
 * automation that this plugin contained up to 1.x is the separate plugin "Auto Archive".
 *
 * @since  1.0.0
 */
final class Yooarchive extends CMSPlugin implements SubscriberInterface
{
    /**
     * Minimum YOOtheme Pro version supported by the builder integration.
     */
    private const MIN_YOOTHEME = '5.0.0';

    /**
     * @return  array<string, string>
     *
     * @since   1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
        ];
    }

    /**
     * Loads the plugin language, warns the administrators when YOOtheme Pro is missing or
     * too old and hooks into YOOtheme Pro when a supported version is running.
     *
     * YOOtheme's own system plugin bootstraps the theme on this same event with a
     * high priority, so the YOOtheme application class already exists here.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onAfterInitialise(): void
    {
        $this->loadLanguage();

        $app = $this->getApplication();

        if ($app->isClient('administrator')) {
            $this->warnAdministrators();
        }

        if (!class_exists(\YOOtheme\Application::class, false)) {
            return;
        }

        $version = $this->getYoothemeVersion();

        if ($version === null || version_compare($version, self::MIN_YOOTHEME, '<')) {
            return;
        }

        \YOOtheme\Application::getInstance()->load(JPATH_PLUGINS . '/system/yooarchive/bootstrap.php');
    }

    /**
     * Warns the users who manage plugins when YOOtheme Pro is missing or older than 5.0
     * (only on the extension, plugin and template screens, to avoid noise on every page).
     *
     * @return  void
     *
     * @since   1.1.0
     */
    private function warnAdministrators(): void
    {
        $app  = $this->getApplication();
        $user = $app->getIdentity();

        if (!$user || !$user->authorise('core.manage', 'com_plugins')) {
            return;
        }

        if (!\in_array($app->getInput()->getCmd('option'), ['com_plugins', 'com_installer', 'com_templates'], true)) {
            return;
        }

        $version = $this->getYoothemeVersion();

        if ($version === null) {
            $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_YOOTHEME_MISSING'), 'warning');
        } elseif (version_compare($version, self::MIN_YOOTHEME, '<')) {
            $app->enqueueMessage(Text::sprintf('PLG_SYSTEM_YOOARCHIVE_MSG_YOOTHEME_OLD', htmlspecialchars($version, ENT_QUOTES, 'UTF-8'), self::MIN_YOOTHEME), 'warning');
        }
    }

    /**
     * Returns the installed YOOtheme Pro version, read from the theme configuration
     * file (the theme is not bootstrapped on every administrator page), or null.
     *
     * @return  string|null
     *
     * @since   1.1.0
     */
    private function getYoothemeVersion(): ?string
    {
        static $version = false;

        if ($version !== false) {
            return $version;
        }

        $version = null;
        $file    = JPATH_ROOT . '/templates/yootheme/config.php';

        if (is_file($file) && preg_match("/'version'\s*=>\s*'([0-9][0-9A-Za-z.\-]*)'/", (string) file_get_contents($file, false, null, 0, 4096), $m)) {
            $version = $m[1];
        }

        return $version;
    }
}
