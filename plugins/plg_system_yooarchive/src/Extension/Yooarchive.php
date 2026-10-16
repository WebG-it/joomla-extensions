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
     * Loads the plugin language and hooks into YOOtheme Pro when it is installed.
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

        // Tell the administrators when the automation is paused because its category is gone.
        if ($this->params->get('automation') && $this->getApplication()->isClient('administrator') && !$this->hasValidTarget()) {
            $user = $this->getApplication()->getIdentity();

            if ($user && $user->authorise('core.manage', 'com_plugins')) {
                $this->getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_YOOARCHIVE_MSG_TARGET_INVALID'), 'warning');
            }
        }

        if (!class_exists(\YOOtheme\Application::class, false)) {
            return;
        }

        \YOOtheme\Application::getInstance()->load(JPATH_PLUGINS . '/system/yooarchive/bootstrap.php');
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
