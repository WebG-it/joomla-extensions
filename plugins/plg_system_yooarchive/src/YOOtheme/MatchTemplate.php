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
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Matches YOOtheme builder templates with the Joomla archive view.
 *
 * Called by YOOtheme on the "builder.template" event. Returning null leaves the page
 * to Joomla, so without a matching template the archive view is rendered as usual.
 *
 * @since  1.0.0
 */
class MatchTemplate
{
    /**
     * @var    string
     * @since  1.0.0
     */
    public string $language;

    /**
     * @param   Document|null  $document  The current document.
     *
     * @since   1.0.0
     */
    public function __construct(?Document $document)
    {
        $this->language = $document->language ?? 'en-gb';
    }

    /**
     * @param   \YOOtheme\Theme\Joomla\LoadTemplateEvent  $event  The template event.
     *
     * @return  array<string, mixed>|null
     *
     * @since   1.0.0
     */
    public function handle($event): ?array
    {
        // YOOtheme Pro 5 passes a LoadTemplateEvent; older versions are not supported.
        if (!\is_object($event) || !method_exists($event, 'getTpl') || !method_exists($event, 'getContext')) {
            return null;
        }

        if ($event->getTpl() || $event->getContext() !== LoadBuilderConfig::TYPE) {
            return null;
        }

        $view = $event->getView();

        /** @var \Joomla\Component\Content\Site\Model\ArchiveModel $model */
        $model      = $view->getModel();
        $state      = $model->getState();
        $pagination = $model->getPagination();
        $items      = $model->getItems() ?: [];
        $input      = Factory::getApplication()->getInput();
        $catids     = array_values(array_filter(array_map('intval', (array) $input->get('catid', [], 'array'))));

        return [
            'type'  => LoadBuilderConfig::TYPE,
            'query' => [
                'catid' => fn ($ids, $query) => $this->matchCategories(
                    $catids,
                    (array) $ids,
                    $query['include_child_categories'] ?? ''
                ),
                'pages' => (int) $pagination->pagesCurrent <= 1 ? 'first' : 'except_first',
                'lang'  => $this->language,
            ],
            'params' => [
                'items'      => $items,
                'pagination' => $pagination,
                'archive'    => [
                    'total'      => (int) $pagination->total,
                    'month'      => (int) $state->get('filter.month'),
                    'year'       => (int) $state->get('filter.year'),
                    'search'     => htmlspecialchars((string) $state->get('list.filter'), ENT_QUOTES, 'UTF-8'),
                    'catids'     => $catids,
                    'page'       => (int) $pagination->pagesCurrent,
                    'pages'      => (int) $pagination->pagesTotal,
                    'filterForm' => fn () => $this->renderFilterForm($model, $pagination, $catids),
                ],
            ],
        ];
    }

    /**
     * Matches the categories of the archive menu item with the template assignment.
     *
     * An archive page without a category filter only matches templates that are not
     * limited by category (YOOtheme skips the callback for an empty assignment).
     *
     * @param   array<int>     $catids           Categories of the current archive page.
     * @param   array<string>  $ids              Categories selected in the template.
     * @param   string         $includeChildren  '', 'include' or 'only'.
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    protected function matchCategories(array $catids, array $ids, $includeChildren): bool
    {
        if (!$catids) {
            return false;
        }

        $ids = array_map('intval', $ids);

        foreach ($catids as $catid) {
            $direct = \in_array($catid, $ids, true);

            if ($direct && $includeChildren !== 'only') {
                return true;
            }

            if (!$includeChildren || $direct) {
                continue;
            }

            $category = Categories::getInstance('content')->get($catid);

            if ($category && array_intersect(array_map('intval', array_keys($category->getPath())), array_diff($ids, [$catid]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Renders the month, year, text and limit filter of the Joomla archive view with
     * UIkit markup, so it can be placed anywhere in the builder layout. The values
     * come from the archive model, exactly like the native Joomla layout.
     *
     * @param   \Joomla\Component\Content\Site\Model\ArchiveModel  $model       The archive model.
     * @param   \Joomla\CMS\Pagination\Pagination                    $pagination  The pagination.
     * @param   array<int>                                           $catids      Categories of the current page.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    protected function renderFilterForm($model, $pagination, array $catids): string
    {
        $state  = $model->getState();
        $params = $state->get('params');
        $month  = (int) $state->get('filter.month');
        $year   = (int) $state->get('filter.year');
        $filter = (string) $state->get('list.filter');
        $itemId = Factory::getApplication()->getInput()->getInt('Itemid');
        $action = Route::_('index.php?option=com_content&view=archive' . ($itemId ? '&Itemid=' . $itemId : ''));
        $esc    = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $months = ['' => Text::_('PLG_SYSTEM_YOOARCHIVE_FILTER_MONTH')];

        foreach (['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'] as $i => $name) {
            $months[(string) ($i + 1)] = Text::_($name . '_SHORT');
        }

        $years = ['' => Text::_('JYEAR')];

        foreach ($model->getYears() as $value) {
            $years[(string) $value] = (string) $value;
        }

        $options = static function (array $list, int $current) use ($esc): string {
            $html = '';

            foreach ($list as $value => $text) {
                $selected = $value !== '' && (int) $value === $current ? ' selected' : '';
                $html .= '<option value="' . $esc($value) . '"' . $selected . '>' . $esc($text) . '</option>';
            }

            return $html;
        };

        $html = '<form class="yooarchive-filter uk-grid-small uk-flex-middle" uk-grid method="get" action="' . $esc($action) . '">';

        if (!$params || $params->get('filter_field') !== 'hide') {
            $html .= '<div><input class="uk-input uk-form-width-medium" type="search" name="filter-search" value="' . $esc($filter)
                . '" placeholder="' . $esc(Text::_('PLG_SYSTEM_YOOARCHIVE_FILTER_TITLE')) . '" aria-label="'
                . $esc(Text::_('PLG_SYSTEM_YOOARCHIVE_FILTER_TITLE')) . '"></div>';
        }

        $html .= '<div><select class="uk-select uk-form-width-small" name="month" aria-label="' . $esc(Text::_('JMONTH')) . '">'
            . $options($months, $month) . '</select></div>';
        $html .= '<div><select class="uk-select uk-form-width-small" name="year" aria-label="' . $esc(Text::_('JYEAR')) . '">'
            . $options($years, $year) . '</select></div>';
        $html .= '<div>' . str_replace('form-select', 'uk-select uk-form-width-xsmall', (string) $pagination->getLimitBox()) . '</div>';

        foreach ($catids as $catid) {
            $html .= '<input type="hidden" name="catid[]" value="' . (int) $catid . '">';
        }

        $html .= '<div><button class="uk-button uk-button-primary" type="submit">' . $esc(Text::_('JGLOBAL_FILTER_BUTTON')) . '</button></div>';
        $html .= '</form>';

        return $html;
    }
}
