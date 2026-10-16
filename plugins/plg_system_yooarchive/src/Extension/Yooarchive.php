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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use WebG\Plugin\System\Yooarchive\Automation\Automation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin "YOOtheme Archive Template".
 *
 * 1. Registers a YOOtheme Pro builder template type for the Joomla archive view
 *    (com_content.archive) with an "Archived Articles" dynamic source.
 * 2. Optional automation: archived articles are moved to a chosen category and
 *    published articles that land in that category are archived.
 *
 * @since  1.0.0
 */
final class Yooarchive extends CMSPlugin implements SubscriberInterface
{
    /**
     * Lazily created automation service.
     *
     * @var    Automation|null
     * @since  1.0.0
     */
    private ?Automation $automation = null;

    /**
     * Cached result of the archive category check (null = not checked yet).
     *
     * @var    bool|null
     * @since  1.1.0
     */
    private ?bool $validTarget = null;

    /**
     * @return  array<string, string>
     *
     * @since   1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise'    => 'onAfterInitialise',
            'onTableBeforeStore'   => 'onTableBeforeStore',
            'onTableAfterStore'    => 'onTableAfterStore',
            'onContentChangeState' => 'onContentChangeState',
            'onContentAfterSave'   => 'onFlush',
            'onContentCleanCache'  => 'onFlush',
        ];
    }

    /**
     * Minimum YOOtheme Pro version supported by the builder integration.
     */
    private const MIN_YOOTHEME = '5.0.0';

    /**
     * Loads the plugin language, warns the administrators about configuration problems
     * and hooks into YOOtheme Pro when a supported version is running.
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
     * Shows configuration warnings to the users who manage plugins: archive category
     * missing, trashed or unpublished; redirects on but the "System - Redirect" plugin
     * off; YOOtheme Pro missing or older than 5.0 (only on the extension, redirect and
     * template screens, to avoid noise on every page).
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

        if ($this->params->get('automation')) {
            if (!$this->hasValidTarget()) {
                $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_TARGET_INVALID'), 'warning');
            } elseif (Automation::getTargetState((int) $this->params->get('target_catid', 0)) !== 1) {
                $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_TARGET_UNPUBLISHED'), 'warning');
            }
        }

        $option = $app->getInput()->getCmd('option');

        // The redirects stored after a move are served by the core "System - Redirect" plugin.
        if (
            \in_array($option, ['com_plugins', 'com_redirect'], true)
            && $this->params->get('automation') && $this->params->get('move_on_archive', 1)
            && $this->params->get('redirect_on_move', 1) && !PluginHelper::isEnabled('system', 'redirect')
        ) {
            $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_REDIRECT_PLUGIN_OFF'), 'warning');
        }

        if (\in_array($option, ['com_plugins', 'com_installer', 'com_templates'], true)) {
            $version = $this->getYoothemeVersion();

            if ($version === null) {
                $app->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_YOOTHEME_MISSING'), 'warning');
            } elseif (version_compare($version, self::MIN_YOOTHEME, '<')) {
                $app->enqueueMessage(Text::sprintf('PLG_SYSTEM_YOOARCHIVE_MSG_YOOTHEME_OLD', htmlspecialchars($version, ENT_QUOTES, 'UTF-8'), self::MIN_YOOTHEME), 'warning');
            }
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

    /**
     * @param   Event  $event  The onTableBeforeStore event.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onTableBeforeStore(Event $event): void
    {
        if ($automation = $this->getAutomation()) {
            $automation->beforeStore($event->getArgument('subject'));
        }
    }

    /**
     * @param   Event  $event  The onTableAfterStore event.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onTableAfterStore(Event $event): void
    {
        if ($automation = $this->getAutomation()) {
            $automation->afterStore($event->getArgument('subject'), (bool) $event->getArgument('result'));
        }
    }

    /**
     * @param   Event  $event  The onContentChangeState event.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onContentChangeState(Event $event): void
    {
        if ($automation = $this->getAutomation()) {
            $automation->changeState(
                (string) $event->getArgument('context'),
                (array) $event->getArgument('subject'),
                (int) $event->getArgument('value')
            );
        }
    }

    /**
     * Runs the queued archive operations once the current save or batch is complete.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onFlush(): void
    {
        if ($automation = $this->getAutomation()) {
            $automation->flush();
        }
    }

    /**
     * Whether the archive category exists, belongs to com_content and is not trashed.
     * Checked once per request.
     *
     * @return  bool
     *
     * @since   1.1.0
     */
    private function hasValidTarget(): bool
    {
        return $this->validTarget ??= Automation::isValidTarget((int) $this->params->get('target_catid', 0));
    }

    /**
     * Returns the automation service, or null when the automation is switched off.
     *
     * @return  Automation|null
     *
     * @since   1.0.0
     */
    private function getAutomation(): ?Automation
    {
        if (!$this->params->get('automation') || !$this->hasValidTarget()) {
            return null;
        }

        return $this->automation ??= new Automation($this->params, $this->getApplication());
    }
}
