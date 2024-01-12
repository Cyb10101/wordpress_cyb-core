<?php
namespace App\Utility;

class PaginationUtility {
    protected array $settings = [];
    protected int $count = 1;
    protected int $limit = 15;
    protected int $maxPageLinks = 10;
    protected string $url = '{page}';
    protected int $page = 1;
    protected int $pageLast = 1;

    public function __construct(array $settings = []) {
        $this->settings = array_merge($this->getDefaultSettings(), $settings);
    }

    protected function getDefaultSettings(): array {
        return [
            'class' => 'pagination',
            'buttonsStartEnd' => true,
            'labels' => [
                'start' => '<i class="glyphicon glyphicon-fast-backward"></i>',
                'previous' => '<i class="glyphicon glyphicon-backward"></i>',
                'next' => '<i class="glyphicon glyphicon-forward"></i>',
                'end' => '<i class="glyphicon glyphicon-fast-forward"></i>',
            ],
        ];
    }

    public function paginateArray(array $items): array {
        $itemsPaginated = [];
        for ($i = $this->getStart(); $i < count($items); $i++) {
            $itemsPaginated[] = $items[$i];
            if (count($itemsPaginated) >= $this->limit) {
                break;
            }
        }
        return $itemsPaginated;
    }

    /**
     * @param string|array $url '?page={page}' || 'method' || ['class', 'method']
     */
    public function createPagination(int $count, int $limit = 15, int $maxPageLinks = 10, string|array $url = '{page}', int $page = 1, array $settings = []): self {
        $this->settings = array_merge($this->settings, $settings);

        $this->count = $count;
        $this->limit = $limit;
        $this->maxPageLinks = $maxPageLinks;
        $this->url = $url;
        $this->page = $page;

        $this->calculate();
        return $this;
    }

    protected function calculate(): self {
        if ($this->maxPageLinks < 0) {
            $this->maxPageLinks = 0;
        }

        $this->page = (intval($this->page) > 0 ? intval($this->page) : 1);

        $this->pageLast = ($this->limit > 0 ? floor($this->count / $this->limit) : 1);
        if ($this->limit > 0 && ($this->count % $this->limit) > 0) {$this->pageLast++;} // If page rest, then add one page
        if ($this->pageLast <= 0) {$this->pageLast = 1;}

        if ($this->page > $this->pageLast) {$this->page = $this->pageLast;} // Set pages maximum
        return $this;
    }

    public function generateUrl(int $page): string {
        if (is_callable($this->url)) {
            return call_user_func_array($this->url, [$page]);
        }
        return str_replace('{page}', $page, $this->url);
    }

    public function getPagination(): string {
        if ($this->limit <= 0 || $this->count <= $this->limit) {
            return '';
        }

        $content = '<ul class="'.$this->settings['class'].'">';
        if ($this->page > 1) {
            if ($this->settings['buttonsStartEnd']) {
                $content .= '<li class="page-item"><a href="' . $this->generateUrl(1) . '" class="page-link">' . $this->settings['labels']['start'] . '</a></li>';
            }
            $content .= '<li class="page-item"><a href="' . $this->generateUrl($this->page - 1) . '" class="page-link">' . $this->settings['labels']['previous'] . '</a></li>';
        } else {
            if ($this->settings['buttonsStartEnd']) {
                $content .= '<li class="page-item disabled"><span class="page-link">' . $this->settings['labels']['start'] . '</span></li>';
            }
            $content .= '<li class="page-item disabled"><span class="page-link">' . $this->settings['labels']['previous'].'</span></li>';
        }

        $middle = floor($this->maxPageLinks / 2); $middleRest = ($this->maxPageLinks % 2);
        $pageAddBefore = 0; $pageAddAfter = 0;
        if (($this->pageLast - $middle) < $this->page) {$pageAddBefore = ($middle - ($this->pageLast - $this->page));}
        if (($middle - $this->page) > 0) {$pageAddAfter = ($middle - $this->page);}
        if ($middleRest && $this->page <= $middle) {$pageAddAfter++;}
        if ($middleRest && $this->page > $middle) {$pageAddBefore++;}

        for ($i = 1; $i <= $this->pageLast; $i++) {
            if ($i > $this->page - $middle - $pageAddBefore && $i < $this->page + $middle + 1 + $pageAddAfter) {
                if ($this->page === $i) {
                    $content .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    $hideResponsive = '';
                    if ($i !== $this->page) {
                        $hideResponsive = ' d-none d-sm-inline-block';
                        if ($i > $this->page + 1 || $i < $this->page - 1) {
                            $hideResponsive = ' d-none d-md-inline-block';
                        }
                        if ($i > $this->page + 2 || $i < $this->page - 2) {
                            $hideResponsive = ' d-none d-lg-inline-block';
                        }
                        if ($i > $this->page + 3 || $i < $this->page - 3) {
                            $hideResponsive = ' d-none d-xl-inline-block';
                        }
                    }
                    $content .= '<li class="page-item' . $hideResponsive . '"><a href="' . $this->generateUrl($i) . '" class="page-link">'.$i.'</a></li>';
                }
            }
        }

        if ($this->page < $this->pageLast) {
            $content .= '<li class="page-item"><a href="' . $this->generateUrl($this->page + 1) . '" class="page-link">' . $this->settings['labels']['next'] . '</a></li>';
            if ($this->settings['buttonsStartEnd']) {
                $content .= '<li class="page-item"><a href="' . $this->generateUrl($this->pageLast) . '" class="page-link">' . $this->settings['labels']['end'] . '</a></li>';
            }
        } else {
            $content .= '<li class="page-item disabled"><span class="page-link">' . $this->settings['labels']['next'] . '</span></li>';
            if ($this->settings['buttonsStartEnd']) {
                $content .= '<li class="page-item disabled"><span class="page-link">' . $this->settings['labels']['end'] . '</span></li>';
            }
        }
        $content .= '</ul>';

        return $content;
    }

    /**
     * Set icons for start, previous, next, end
     */
    public function setButtonsIcons(): self {
        $defaultSettings = $this->getDefaultSettings();
        $this->settings['labels']['start'] = $defaultSettings['labels']['start'];
        $this->settings['labels']['previous'] = $defaultSettings['labels']['previous'];
        $this->settings['labels']['next'] = $defaultSettings['labels']['next'];
        $this->settings['labels']['end'] = $defaultSettings['labels']['end'];
        return $this;
    }

    public function setClass(string $class = ''): self {
        $defaultSettings = $this->getDefaultSettings();
        $this->settings['class'] = (!empty($class) ? $class : $defaultSettings['class']);
        return $this;
    }

    /**
     * Set text for icons for start, previous, next, end
     */
    public function setButtonsText(string $start = '', string $previous = '', string $next = '', string $end = ''): self {
        $this->settings['labels']['start'] = (!empty($start) ? $start : 'Anfang');
        $this->settings['labels']['previous'] = (!empty($previous) ? $previous : 'Zurück');
        $this->settings['labels']['next'] = (!empty($next) ? $next : 'Weiter');
        $this->settings['labels']['end'] = (!empty($end) ? $end : 'Ende');
        return $this;
    }

    /**
     * Enable/disable start ... end buttons
     */
    public function setButtonsStartEnd(bool $bool): self {
        $this->settings['buttonsStartEnd'] = $bool;
        return $this;
    }

    public function getPagesDetails(): string {
        return 'Seite ' . $this->page . ' von ' . $this->pageLast;
    }

    public function getSqlLimit(): string {
        return 'LIMIT ' . $this->getStart() . ',' . $this->limit;
    }

    public function getStart(): int {
        return ($this->page - 1) * $this->limit;
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function getEnd(): int {
        return $this->getStart() + $this->limit;
    }
}
