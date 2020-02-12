<?php
namespace App\Widget;

class NavigationWidget extends \WP_Widget {
    public function __construct() {
        parent::__construct(false, 'Cyb Navigation');
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $menuSlug = apply_filters('widget_menuSlug', $instance['menuSlug']);
        $showWidgetFrame = (bool)apply_filters('widget_showWidgetFrame', $instance['showWidgetFrame']);
        $showTitle = (bool)apply_filters('widget_showTitle', $instance['showTitle']);
        $cssClasses = apply_filters('widget_cssClasses', $instance['cssClasses']);

        if ($showWidgetFrame) {
            if (!empty($cssClasses)) {
                $args['before_widget'] = preg_replace('/(<.*?class=["\'][^"\']+)(["\']>)/', '\1 ' . $cssClasses . '\2', $args['before_widget']);
            }
            echo $args['before_widget'];
        } else {
            ?><div class="widget-without-frame<?php echo (!empty($cssClasses) ? ' ' . $cssClasses : ''); ?>"><?php
        }
        if ($showTitle && !empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if (!empty($menuSlug)) {
            $wpNavMenu = [
                'menu' => 'primary-menu',
                'menu_id' => $args['widget_id'],
                'depth' => 2, // 1 = no dropdowns, 2 = with dropdowns.
                'container' => '',
                'container_id' => '',
                'menu_class' => 'nav flex-column d-xl-block d-lg-block d-none',
            ];
            if (class_exists('Cyb\Utility\NavWalkerBootstrapDropdownUtility')) {
                $wpNavMenu['walker'] = new \Cyb\Utility\NavWalkerBootstrapDropdownUtility();
            }
            wp_nav_menu($wpNavMenu);
        } else {
            echo 'Menu not defined!';
        }

        if ($showWidgetFrame) {
            echo $args['after_widget'];
        } else {
            ?></div><?php
        }
    }

     public function form($instance) {
        $defaults = [
            'title' => '',
            'menuSlug' => '',
            'showWidgetFrame' => '',
            'showTitle' => '',
            'cssClasses' => '',
        ];
        $instance = wp_parse_args((array)$instance, $defaults);
        $title = $instance['title'];
        $menuSlug = $instance['menuSlug'];
        $showWidgetFrame = (bool)$instance['showWidgetFrame'];
        $showTitle = (bool)$instance['showTitle'];
        $cssClasses = $instance['cssClasses'];

        ?>
        <p><b><?php echo __('Title'); ?>: </b></p>
        <p><input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" class="widefat" /></p>

        <p><b><?php echo __('Menu ID'); ?>: </b></p><select name="<?php echo $this->get_field_name('menuSlug'); ?>">
        <?php
        $menus = get_terms('nav_menu', ['hide_empty' => true]);

        $selected = ($menuSlug === '' ? ' selected' : '');
        echo '<option value=""' . $selected . '>' . __('- Please choose -') . '</option>';
        foreach($menus as $menu) {
            $selected = ($menuSlug === $menu->slug ? ' selected' : '');
            echo '<option value="' . $menu->slug . '"' . $selected . '>' . $menu->name . '</option>';
        }
        ?></select>

        <p><b><?php echo __('Show'); ?>: </b></p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('showWidgetFrame'); ?>" id="<?php echo $this->get_field_name('showWidgetFrame'); ?>" <?php echo $showWidgetFrame ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $this->get_field_name('showWidgetFrame'); ?>"><?php echo __('Widget frame'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('showTitle'); ?>" id="<?php echo $this->get_field_name('showTitle'); ?>" <?php echo $showTitle ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $this->get_field_name('showTitle'); ?>"><?php echo __('Title'); ?></label>
        </p>

        <p><b><?php echo __('CSS Classes'); ?>: </b></p>
        <p><input type="text" name="<?php echo $this->get_field_name('cssClasses'); ?>" value="<?php echo $cssClasses; ?>" class="widefat" /></p>
        <?php
    }

    public function update($newInstance, $oldInstance) {
        $instance = $oldInstance;
        $instance['title'] = strip_tags($newInstance['title']);
        $instance['menuSlug'] = strip_tags($newInstance['menuSlug']);
        $instance['showWidgetFrame'] = strip_tags($newInstance['showWidgetFrame']);
        $instance['showTitle'] = strip_tags($newInstance['showTitle']);
        $instance['cssClasses'] = strip_tags($newInstance['cssClasses']);
        return $instance;
    }
}
