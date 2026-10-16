<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\YOOtheme;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Adds the "Archived Articles" template type to the YOOtheme customizer.
 *
 * The categories and languages option lists are already provided by YOOtheme
 * (yootheme.builder.categories / yootheme.builder.languages), so only the template
 * definition is merged here.
 *
 * @since  1.0.0
 */
class LoadBuilderConfig
{
    public const TYPE = 'com_content.archive';

    /**
     * @param   \YOOtheme\Builder\BuilderConfig  $config  The builder configuration.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handle($config): void
    {
        $config->merge([
            'templates' => [
                self::TYPE => [
                    'label'    => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_LABEL'),
                    'fieldset' => [
                        'default' => [
                            'fields' => [
                                'catid' => [
                                    'label'   => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_CATID_LABEL'),
                                    'type'    => 'select',
                                    'default' => [],
                                    'options' => [['evaluate' => "yootheme.builder['categories']"]],
                                    'attrs'   => [
                                        'multiple' => true,
                                        'class'    => 'uk-height-small',
                                    ],
                                ],
                                'include_child_categories' => [
                                    'type'        => 'select',
                                    'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_CATID_DESC'),
                                    'options'     => [
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_CHILD_EXCLUDE') => '',
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_CHILD_INCLUDE') => 'include',
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_CHILD_ONLY')    => 'only',
                                    ],
                                ],
                                'pages' => [
                                    'label'       => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_PAGES_LABEL'),
                                    'description' => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_PAGES_DESC'),
                                    'type'        => 'select',
                                    'options'     => [
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_PAGES_ALL')          => '',
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_PAGES_FIRST')        => 'first',
                                        Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_PAGES_EXCEPT_FIRST') => 'except_first',
                                    ],
                                ],
                                'lang' => [
                                    'label'        => Text::_('PLG_SYSTEM_YOOARCHIVE_TEMPLATE_LANG_LABEL'),
                                    'type'         => 'select',
                                    'defaultIndex' => 0,
                                    'options'      => [['evaluate' => 'yootheme.builder.languages']],
                                    'show'         => 'yootheme.builder.languages.length > 1 || lang',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
