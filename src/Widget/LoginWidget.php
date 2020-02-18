<?php
namespace App\Widget;

class LoginWidget extends \WP_Widget {
    public function __construct() {
        parent::__construct(false, 'Cyb Login');
    }

    public function widget($args, $instance) {
        $args['widget_id'] = str_replace('\\', '_', $args['widget_id']);
        $title = apply_filters('widget_title', $instance['title']);
        $showWidgetFrame = (bool)apply_filters('widget_showWidgetFrame', $instance['showWidgetFrame']);
        $showTitle = (bool)apply_filters('widget_showTitle', $instance['showTitle']);

        if ($showWidgetFrame) {
            echo $args['before_widget'];
        } else {
            ?><div class="widget-without-frame"><?php
        }
        if ($showTitle && !empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if (is_user_logged_in()) {
            $onClick = 'document.location.href=\'' . wp_logout_url($_SERVER['REQUEST_URI']) . '\'';
            ?>
            <button type="submit" name="submit" class="btn btn-primary" style="width: 100%" onclick="<?php echo $onClick; ?>">
                <?php echo __('Logout'); ?>
            </button>
            <?php
        } else {
            ?>
            <form action="<?php echo site_url(); ?>/wp-login.php" method="post" autocomplete="on">
                <input type="hidden" name="rememberme" value="forever"/>
                <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>"/>

                <input type="text" name="log"  placeholder="<?php echo __('Username'); ?>" pattern="^[@_öäüöÄÜß\w\s\.]{3,30}$" style="margin-bottom: 3px; width: 100%"/>
                <input type="password" name="pwd"  placeholder="<?php echo __('Password'); ?>" autocomplete="off" style="margin-bottom: 3px; width: 100%"/>
                <button type="submit" name="submit" class="btn btn-primary" style="width: 100%">
                    <?php echo __('Login'); ?>
                </button>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Lost your password?'); ?></a>
                <?php wp_register('', ''); ?>
            </form>
            <?php
        }

        if ($showWidgetFrame) {
            echo $args['after_widget'];
        } else {
            ?></div><?php
        }
    }

    public function form($instance) {
        $defaults = [
            'title' => __('Member area'),
            'showWidgetFrame' => 'on',
            'showTitle' => 'on',
        ];
        $instance = wp_parse_args((array)$instance, $defaults);
        $title = $instance['title'];
        $showWidgetFrame = (bool)$instance['showWidgetFrame'];
        $showTitle = (bool)$instance['showTitle'];

        ?>
        <p><b><?php echo __('Title'); ?></b></p>
        <p><input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" class="widefat"/></p>

        <p><b><?php echo __('Show'); ?>: </b></p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('showWidgetFrame'); ?>" id="<?php echo $this->get_field_name('showWidgetFrame'); ?>" <?php echo $showWidgetFrame ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $this->get_field_name('showWidgetFrame'); ?>"><?php echo __('Widget frame'); ?></label>
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('showTitle'); ?>" id="<?php echo $this->get_field_name('showTitle'); ?>" <?php echo $showTitle ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $this->get_field_name('showTitle'); ?>"><?php echo __('Title'); ?></label>
        </p>
        <?php
    }

    public function update($newInstance, $oldInstance) {
        $instance = $oldInstance;
        $instance['title'] = strip_tags($newInstance['title']);
        $instance['showWidgetFrame'] = strip_tags($newInstance['showWidgetFrame']);
        $instance['showTitle'] = strip_tags($newInstance['showTitle']);
        return $instance;
    }
}
