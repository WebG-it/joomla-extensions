<?php

/**
 * @package     WebG.Plugin
 * @subpackage  System.yooarchive
 *
 * @copyright   (C) 2026 WebG di Marco Galassi <https://www.webg.it>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace WebG\Plugin\System\Yooarchive\YOOtheme;

use WebG\Plugin\System\Yooarchive\YOOtheme\Type\ArchiveInfoType;
use WebG\Plugin\System\Yooarchive\YOOtheme\Type\ArchiveQueryType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers the dynamic sources of the archive template.
 *
 * The articles are exposed as the YOOtheme "Article" type, so every article field
 * and every custom field YOOtheme already knows is available without extra code.
 *
 * @since  1.0.0
 */
class SourceListener
{
    /**
     * @param   \YOOtheme\Builder\Source  $source  The source schema builder.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handle($source): void
    {
        $source->objectType('YooarchiveInfo', ArchiveInfoType::config());
        $source->queryType(ArchiveQueryType::config());
    }
}
