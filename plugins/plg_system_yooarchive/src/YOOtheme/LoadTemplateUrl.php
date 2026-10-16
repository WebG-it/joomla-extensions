<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\YOOtheme;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Router\SiteRouter;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Gives the YOOtheme customizer the URL used to preview an archive template.
 *
 * The Joomla router does not choose an archive menu item by category, so the
 * matching "Archived Articles" menu item is looked up here: first one filtered on
 * a category of the template (or a child of it), then one without category filter,
 * then any archive menu item. Without menu items the plain archive URL is used.
 *
 * @since  1.0.0
 */
class LoadTemplateUrl
{
    /**
     * @param   SiteRouter      $router  The site router.
     * @param   DatabaseDriver  $db      The database.
     *
     * @since   1.0.0
     */
    public function __construct(public SiteRouter $router, public DatabaseDriver $db)
    {
    }

    /**
     * @param   array<string, mixed>  $template  The template being loaded in the customizer.
     *
     * @return  array<string, mixed>
     *
     * @since   1.0.0
     */
    public function handle(array $template): array
    {
        if (($template['type'] ?? '') !== LoadBuilderConfig::TYPE) {
            return $template;
        }

        $query    = (array) ($template['query'] ?? []);
        $catids   = array_values(array_filter(array_map('intval', (array) ($query['catid'] ?? []))));
        $children = (string) ($query['include_child_categories'] ?? '');
        $lang     = preg_replace_callback('/-\w{2}$/', fn ($m) => strtoupper($m[0]), (string) ($query['lang'] ?? ''));
        $itemId   = $this->findMenuItem($catids, $children, $lang);

        $url = $itemId ? 'index.php?Itemid=' . $itemId : 'index.php?option=com_content&view=archive';

        if (!$itemId && $catids) {
            $url .= '&catid[0]=' . $catids[0];
        }

        if ($lang) {
            $url .= '&lang=' . rawurlencode($lang);
        }

        try {
            $template['url'] = (string) $this->router->build($url);
        } catch (\Throwable $e) {
            $template['url'] = $url;
        }

        return $template;
    }

    /**
     * @param   array<int>  $catids    Categories of the template assignment.
     * @param   string      $children  '', 'include' or 'only'.
     * @param   string      $lang      Language of the template assignment.
     *
     * @return  int  The best matching menu item id, or 0.
     *
     * @since   1.0.0
     */
    protected function findMenuItem(array $catids, string $children, string $lang): int
    {
        $db    = $this->db;
        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('link'), $db->quoteName('language')])
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_content&view=archive%'))
            ->order($db->quoteName('lft'));

        try {
            $rows = $db->setQuery($query)->loadObjectList();
        } catch (\Throwable $e) {
            return 0;
        }

        $best = [0, 0];

        foreach ($rows as $row) {
            if ($lang && !\in_array($row->language, ['*', $lang], true)) {
                continue;
            }

            parse_str((string) parse_url($row->link, PHP_URL_QUERY), $vars);
            $menuCats = array_values(array_filter(array_map('intval', (array) ($vars['catid'] ?? []))));
            $score    = 1;

            if (!$catids && !$menuCats) {
                $score = 3;
            } elseif ($catids && $menuCats) {
                if ($children !== 'only' && array_intersect($catids, $menuCats)) {
                    $score = 3;
                } elseif ($children && $this->isChildOf($menuCats, $catids)) {
                    $score = 3;
                }
            } elseif (!$menuCats) {
                $score = 2;
            }

            if ($score > $best[1]) {
                $best = [(int) $row->id, $score];
            }
        }

        return $best[0];
    }

    /**
     * @param   array<int>  $menuCats  Categories of the menu item.
     * @param   array<int>  $parents   Categories of the template.
     *
     * @return  bool  True when one menu category is a descendant of a template category.
     *
     * @since   1.0.0
     */
    protected function isChildOf(array $menuCats, array $parents): bool
    {
        foreach ($menuCats as $catid) {
            $node = Categories::getInstance('content')->get($catid);

            while ($node && ($node = $node->getParent())) {
                if (\in_array((int) $node->id, $parents, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
