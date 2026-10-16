<?php

/**
 * YOOtheme Pro module definition, loaded by the plugin into the YOOtheme application.
 *
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use WebG\Plugin\System\Yooarchive\YOOtheme\LoadBuilderConfig;
use WebG\Plugin\System\Yooarchive\YOOtheme\LoadTemplateUrl;
use WebG\Plugin\System\Yooarchive\YOOtheme\MatchTemplate;
use WebG\Plugin\System\Yooarchive\YOOtheme\SourceListener;
use YOOtheme\Builder\BuilderConfig;

return [
    'events' => [
        // Register the "Archived Articles" sources in the builder schema.
        'source.init' => [SourceListener::class => 'handle'],

        // Match a builder template with the Joomla archive view (frontend and customizer).
        'builder.template' => [MatchTemplate::class => '@handle'],

        // Give the customizer a preview URL for templates of this type.
        'builder.template.load' => [LoadTemplateUrl::class => '@handle'],

        // Add the template type and its assignment fields to the customizer.
        BuilderConfig::class => [LoadBuilderConfig::class => ['handle', 10]],
    ],
];
