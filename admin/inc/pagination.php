<?php
/**
 * Pagination class for admin tables
 */
class Pagination {
    private $total_items;
    private $items_per_page;
    private $current_page;
    private $total_pages;
    
    public function __construct($total_items, $items_per_page = 10, $current_page = 1) {
        $this->total_items = $total_items;
        $this->items_per_page = $items_per_page;
        $this->current_page = $current_page;
        $this->total_pages = ceil($total_items / $items_per_page);
    }
    
    public function getOffset() {
        return ($this->current_page - 1) * $this->items_per_page;
    }
    
    public function getTotalPages() {
        return $this->total_pages;
    }
    
    public function getCurrentPage() {
        return $this->current_page;
    }
    
    public function getLimit() {
        return $this->items_per_page;
    }
    
    public function generatePaginationLinks($base_url, $page_param = 'page') {
        if ($this->total_pages <= 1) {
            return '';
        }
        
        // Check if the base URL already has query parameters
        $separator = (strpos($base_url, '?') !== false) ? '&' : '?';
        
        $links = '<div class="pagination-wrapper">';
        $links .= '<nav aria-label="Page navigation">';
        $links .= '<ul class="pagination">';
        
        // Previous button
        $prev_page = max(1, $this->current_page - 1);
        $links .= '<li class="page-item ' . ($this->current_page == 1 ? 'disabled' : '') . '">';
        $links .= '<a class="page-link" href="' . $base_url . $separator . $page_param . '=' . $prev_page . '" tabindex="-1">Previous</a>';
        $links .= '</li>';
        
        // Page numbers
        $start_page = max(1, $this->current_page - 2);
        $end_page = min($this->total_pages, $this->current_page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i >= 1 && $i <= $this->total_pages) {
                $links .= '<li class="page-item ' . ($i == $this->current_page ? 'active' : '') . '">';
                $links .= '<a class="page-link" href="' . $base_url . $separator . $page_param . '=' . $i . '">' . $i . '</a>';
                $links .= '</li>';
            }
        }
        
        // Next button
        $next_page = min($this->total_pages, $this->current_page + 1);
        $links .= '<li class="page-item ' . ($this->current_page == $this->total_pages ? 'disabled' : '') . '">';
        $links .= '<a class="page-link" href="' . $base_url . $separator . $page_param . '=' . $next_page . '" tabindex="-1">Next</a>';
        $links .= '</li>';
        
        $links .= '</ul>';
        $links .= '</nav>';
        $links .= '</div>';
        
        return $links;
    }
    
    public function getPaginationInfo() {
        $start = ($this->current_page - 1) * $this->items_per_page + 1;
        $end = min($this->total_items, $this->current_page * $this->items_per_page);
        
        return "Showing " . $start . " to " . $end . " of " . $this->total_items . " entries";
    }
}
?>
