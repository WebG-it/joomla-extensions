<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\YOOtheme\Type;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Information about the current archive page: counts, active filters and a
 * ready-made filter form (month, year, text, items per page).
 *
 * @since  1.0.0
 */
class ArchiveInfoType
{
    /**
     * @return  array<string, mixed>
     *
     * @since   1.0.0
     */
    public static function config(): array
    {
        return [
            'fields' => [
                'total' => [
                    'type'     => 'Int',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_TOTAL')],
                ],
                'page' => [
                    'type'     => 'Int',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_PAGE')],
                ],
                'pages' => [
                    'type'     => 'Int',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_PAGES')],
                ],
                'year' => [
                    'type'     => 'Int',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_YEAR')],
                ],
                'month' => [
                    'type'     => 'Int',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_MONTH')],
                ],
                'monthName' => [
                    'type'       => 'String',
                    'metadata'   => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_MONTH_NAME')],
                    'extensions' => ['call' => __CLASS__ . '::monthName'],
                ],
                'search' => [
                    'type'     => 'String',
                    'metadata' => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_SEARCH')],
                ],
                'categories' => [
                    'type'       => 'String',
                    'metadata'   => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_CATEGORIES')],
                    'extensions' => ['call' => __CLASS__ . '::categories'],
                ],
                'filterForm' => [
                    'type'       => 'String',
                    'metadata'   => ['label' => Text::_('PLG_SYSTEM_YOOARCHIVE_INFO_FILTER_FORM')],
                    'extensions' => ['call' => __CLASS__ . '::filterForm'],
                ],
            ],
            'metadata' => [
                'type'  => true,
                'label' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_INFO'),
            ],
        ];
    }

    /**
     * @param   array<string, mixed>  $info  The archive information.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public static function monthName($info): string
    {
        $months = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
        $month  = (int) ($info['month'] ?? 0);

        return $month >= 1 && $month <= 12 ? Text::_($months[$month - 1]) : '';
    }

    /**
     * @param   array<string, mixed>  $info  The archive information.
     *
     * @return  string  The titles of the categories filtered by the menu item.
     *
     * @since   1.0.0
     */
    public static function categories($info): string
    {
        $titles = [];

        foreach ((array) ($info['catids'] ?? []) as $catid) {
            if ($category = Categories::getInstance('content')->get((int) $catid)) {
                $titles[] = htmlspecialchars((string) $category->title, ENT_QUOTES, 'UTF-8');
            }
        }

        return implode(', ', $titles);
    }

    /**
     * @param   array<string, mixed>  $info  The archive information.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public static function filterForm($info): string
    {
        $form = $info['filterForm'] ?? '';

        return \is_callable($form) ? (string) $form() : (string) $form;
    }
}
