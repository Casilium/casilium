<?php
declare(strict_types=1);

namespace App\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use function count;

/**
 * Breadcrumbs
 *
 * Breadcrumbs view helper to allow breadcrumbs to be defined within views
 */
class Breadcrumbs extends AbstractHelper
{
    /** @var array */
    private $items;

    /**
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Renders the breadcrumbs
     */
    public function render(): string
    {
        if (count($this->items) === 0) {
            return '';
        }

        // resulting HTML code will be stored in this var
        $result = <<<HTML
        <div class="container" aria-label="Breadcrumb">
        <ol class="breadcrumb" style="background-color: transparent" 
            itemscope itemtype="http://schema.org/BreadcrumbList">
        HTML;

        // get item count
        $itemCount = count($this->items);

        // item counter
        $itemNum = 1;

        // walk through items
        foreach ($this->items as $label => $link) {
            // make the last item inactive
            $isActive = $itemNum === $itemCount;

            $result .= $this->renderItem($label, $link, $itemNum, $isActive);

            // increment item counter
            $itemNum++;
        }

        $result .= '</ol></div>';
        return $result;
    }

    /**
     * Renders an item
     */
    public function renderItem(string $label, string $link, int $itemNum, bool $isActive): string
    {
        $result  = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" ';
        $result .= $isActive ? 'class="breadcrumb-item active">' : 'class="breadcrumb-item">';

        if (! $isActive) {
            $result .= '<a itemprop="item" href="' . $link . '"><span itemprop="name">' . $label . '</span></a>';
        } else {
            $result .= '<link itemprop="item" href="' . $link . '" aria-current="page">';
            $result .= '<span itemprop="name">' . $label . '</span></link>';
            //$result .= '<span itemprop="name">' . $label . '</span>';
        }

        $result .= '<meta itemprop="position" content="' . $itemNum . '" />';
        $result .= '</li>';
        return $result;
    }
}
