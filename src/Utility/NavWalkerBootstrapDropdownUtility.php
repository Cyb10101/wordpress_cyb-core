<?php
namespace Cyb\Utility;

class NavWalkerBootstrapDropdownUtility extends NavWalkerBootstrapUtility {
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        $classes = ['collapse'];
        $classes = $this->navMenuSubmenuCssClassActive($classes, $output);
        $class_names = join(' ', apply_filters('nav_menu_submenu_css_class', $classes, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $labelledby = $this->navMenuSubmenuLabelledBy($output);
        $id = $this->navMenuSubmenuTargetId($output);

        $indent = $this->getIndent($args, $depth);
        $n = $this->getNewLine($args, $depth);
        $output .= "{$n}{$indent}<div$class_names $id $labelledby role=\"menu\">{$n}";
    }

    public function navMenuSubmenuCssClassActive(array $classes, string &$output): array {
        preg_match_all( '/(<li.*?class=\"|\')(.*?)\"|\'.*?>/im', $output, $matches );
        if (end($matches[2])) {
            $liClasses = explode(' ', end($matches[2]));
            if (in_array('current-menu-item', $liClasses) || in_array('current-menu-parent', $liClasses)) {
                $classes[] = 'show';
            }
        }
        return $classes;
    }

    protected function navMenuSubmenuTargetId(string &$output): string {
        preg_match_all('/(<a.*?data-target=\"|\')#(.*?)\"|\'.*?>/im', $output, $matches);
        if (end($matches[2])) {
            return 'id="' . end($matches[2]) . '"';
        }
        return '';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = $this->getIndent($args, $depth);
        $n = $this->getNewLine($args, $depth);
        $output .= "$indent</div>{$n}";
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $indent = $this->getIndent($args, $depth);
        $n = $this->getNewLine($args, $depth);
        $indent = ( $depth ) ? $indent : '';

        $classes = empty($item->classes) ? array() : (array)$item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

        // not in core
        $classes[] = 'nav-item';
        if ( isset( $args->has_children ) && $args->has_children ) {
            $classes[] = 'collapse-toggle';
        }
        if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
            $classes[] = 'active';
        }
        if ($depth > 0) {
            $classes[] = 'dropdown-item';
        }

        // in core
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        // not in core
        if ($depth < 1) {
            $output .= $indent . '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement"' . $id . $class_names . '>';
        }

        $atts = array();

        if ( empty( $item->attr_title ) ) {
            $atts['title'] = ! empty( $item->title ) ? strip_tags( $item->title ) : '';
        } else {
            $atts['title'] = $item->attr_title;
        }

        $atts['target'] = ! empty( $item->target ) ? $item->target : '';
        $atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
        // If item has_children add atts to <a>.
        if ( isset( $args->has_children ) && $args->has_children && 0 === $depth && $args->depth > 1 ) {
            $atts['href']          = '#';
            $atts['data-toggle']   = 'collapse';
            $atts['aria-haspopup'] = 'true';
            $atts['aria-expanded'] = 'false';
            $atts['class']         = 'dropdown-toggle nav-link ' . (in_array('active', $classes) ? '' : 'collapsed');
            $dropdownPreId = (!empty($args->menu_id) ? $args->menu_id . '_' : '');
            $atts['data-target']   = '#' . $dropdownPreId . 'menu-item-collapse-' . $item->ID;
            $atts['id']            = $dropdownPreId . 'menu-item-dropdown-' . $item->ID;
        } else {
            $atts['href'] = ! empty( $item->url ) ? $item->url : '#';
            // Items in dropdowns use .dropdown-item instead of .nav-link.
            if ( $depth > 0 ) {
                $atts['class'] = 'dropdown-item';
            } else {
                $atts['class'] = 'nav-link';
            }
            if (in_array('active', $classes)) {
                $atts['class'] .= ' active';
            }
        }

        // in core
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        $item_output  = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $title . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        $n = $this->getNewLine($args, $depth);

        if ($depth < 1) {
            $output .= "</li>{$n}";
        }
    }
}

