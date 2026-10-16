<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\YOOtheme\Type;

use Joomla\CMS\Language\Text;
use WebG\Plugin\System\Yooarchive\YOOtheme\LoadBuilderConfig;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * "Page" sources of the archive template: the archived articles of the current
 * page (list and single) and the archive information (count, filters, form).
 *
 * The articles, their order across pages and the page size come from the Joomla
 * archive model and the menu item, so pagination stays consistent. The optional
 * "order" argument only re-sorts the articles of the current page.
 *
 * @since  1.0.0
 */
class ArchiveQueryType
{
    /**
     * Article properties that can be used to re-sort the current page.
     */
    private const ORDER_FIELDS = ['publish_up', 'created', 'modified', 'title', 'hits', 'ordering', 'id'];

    /**
     * @return  array<string, mixed>
     *
     * @since   1.0.0
     */
    public static function config(): array
    {
        $view  = [LoadBuilderConfig::TYPE];
        $group = Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_GROUP');

        return [
            'fields' => [
                'yooarchiveArticles' => [
                    'type' => ['listOf' => 'Article'],
                    'args' => [
                        'offset'          => ['type' => 'Int', 'defaultValue' => 0],
                        'limit'           => ['type' => 'Int', 'defaultValue' => null],
                        'order'           => ['type' => 'String', 'defaultValue' => ''],
                        'order_direction' => ['type' => 'String', 'defaultValue' => 'DESC'],
                    ],
                    'metadata' => [
                        'label'       => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ARTICLES'),
                        'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ARTICLES_DESC'),
                        'view'        => $view,
                        'group'       => $group,
                        'fields'      => [
                            '_offset' => [
                                'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_OFFSET_DESC'),
                                'type'        => 'grid',
                                'width'       => '1-2',
                                'fields'      => [
                                    'offset' => [
                                        'label'    => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_START'),
                                        'type'     => 'number',
                                        'modifier' => 1,
                                        'attrs'    => ['min' => 1, 'required' => true],
                                    ],
                                    'limit' => [
                                        'label' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_QUANTITY'),
                                        'type'  => 'limit',
                                        'attrs' => [
                                            'placeholder' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_NO_LIMIT'),
                                            'min'         => 0,
                                        ],
                                    ],
                                ],
                            ],
                            '_order' => [
                                'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ORDER_DESC'),
                                'type'        => 'grid',
                                'width'       => '1-2',
                                'fields'      => [
                                    'order' => [
                                        'label'   => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ORDER'),
                                        'type'    => 'select',
                                        'options' => [
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_MENU')       => '',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_PUBLISH_UP') => 'publish_up',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_CREATED')    => 'created',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_MODIFIED')   => 'modified',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_TITLE')      => 'title',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_HITS')       => 'hits',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_ORDERING')   => 'ordering',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_ORDER_ID')         => 'id',
                                        ],
                                    ],
                                    'order_direction' => [
                                        'label'   => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_DIRECTION'),
                                        'type'    => 'select',
                                        'enable'  => 'order',
                                        'options' => [
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_DIRECTION_ASC')  => 'ASC',
                                            Text::_('PLG_SYSTEM_YOOARCHIVE_DIRECTION_DESC') => 'DESC',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolveArticles',
                    ],
                ],

                'yooarchiveArticle' => [
                    'type' => 'Article',
                    'args' => [
                        'offset' => ['type' => 'Int', 'defaultValue' => 0],
                    ],
                    'metadata' => [
                        'label'       => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ARTICLE'),
                        'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_ARTICLE_DESC'),
                        'view'        => $view,
                        'group'       => $group,
                        'fields'      => [
                            'offset' => [
                                'label'       => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_START'),
                                'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_SINGLE_DESC'),
                                'type'        => 'number',
                                'modifier'    => 1,
                                'attrs'       => ['min' => 1, 'required' => true],
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolveArticle',
                    ],
                ],

                'yooarchive' => [
                    'type'     => 'YooarchiveInfo',
                    'metadata' => [
                        'label'       => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_INFO'),
                        'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_SOURCE_INFO_DESC'),
                        'view'        => $view,
                        'group'       => $group,
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolveInfo',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param   array<string, mixed>  $root  The template parameters.
     * @param   array<string, mixed>  $args  The source arguments.
     *
     * @return  array<object>|null
     *
     * @since   1.0.0
     */
    public static function resolveArticles($root, array $args)
    {
        if (($root['template'] ?? '') !== LoadBuilderConfig::TYPE) {
            return null;
        }

        $items = self::prepareItems($root['items'] ?? []);
        $order = (string) ($args['order'] ?? '');

        if (\in_array($order, self::ORDER_FIELDS, true)) {
            $desc = strtoupper((string) ($args['order_direction'] ?? 'DESC')) === 'DESC';

            usort($items, static function ($a, $b) use ($order, $desc) {
                $x = $a->$order ?? null;
                $y = $b->$order ?? null;

                if (\in_array($order, ['hits', 'ordering', 'id'], true)) {
                    $result = (int) $x <=> (int) $y;
                } elseif ($order === 'title') {
                    $result = strnatcasecmp((string) $x, (string) $y);
                } else {
                    $result = strcmp((string) $x, (string) $y);
                }

                return $desc ? -$result : $result;
            });
        }

        $offset = (int) ($args['offset'] ?? 0);
        $limit  = (int) ($args['limit'] ?? 0);

        if ($offset || $limit) {
            $items = \array_slice($items, $offset, $limit ?: null);
        }

        return $items;
    }

    /**
     * @param   array<string, mixed>  $root  The template parameters.
     * @param   array<string, mixed>  $args  The source arguments.
     *
     * @return  object|null
     *
     * @since   1.0.0
     */
    public static function resolveArticle($root, array $args)
    {
        if (($root['template'] ?? '') !== LoadBuilderConfig::TYPE) {
            return null;
        }

        return self::prepareItems($root['items'] ?? [])[(int) ($args['offset'] ?? 0)] ?? null;
    }

    /**
     * @param   array<string, mixed>  $root  The template parameters.
     *
     * @return  array<string, mixed>|null
     *
     * @since   1.0.0
     */
    public static function resolveInfo($root)
    {
        if (($root['template'] ?? '') !== LoadBuilderConfig::TYPE) {
            return null;
        }

        return $root['archive'] ?? [];
    }

    /**
     * Makes the custom fields of archived articles available to YOOtheme.
     *
     * Joomla prepares the archive items with the context "com_content.archive", a
     * section com_content does not map to "article": the Fields plugin then sets an
     * empty "jcfields" list and YOOtheme would show no custom field. Removing the
     * empty list lets YOOtheme load the fields with the "com_content.article" context,
     * exactly as for category and featured pages.
     *
     * @param   array<object>  $items  The archived articles.
     *
     * @return  array<object>
     *
     * @since   1.0.0
     */
    private static function prepareItems(array $items): array
    {
        foreach ($items as $item) {
            if (\is_object($item) && isset($item->jcfields) && $item->jcfields === []) {
                unset($item->jcfields);
            }
        }

        return array_values($items);
    }
}
