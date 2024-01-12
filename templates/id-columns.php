<div class="card">
    <div class="card-header"><?php _e('Show id column lists (on Admin page)', 'cyb-core'); ?></div>
    <div class="card-body">
        <form id="cyb-core-id-columns-form">
            <input type="hidden" name="action" value="admin-general">
            <input type="hidden" name="task" value="id-columns"/>
            <?php
            wp_nonce_field('admin-general-nonce');
            $idColumns = get_option('cyb-core-id-columns', []);
            $postTypes = array_merge($this->getAllPostTypes(), [
                'media' => 'Media',
                'category' => 'Categories',
                'user' => 'Users',
                'comment' => 'Comments',
            ]);
            foreach ($postTypes as $key => $value) {
                $idColumns[$key] = isset($idColumns[$key]) ? (bool)$idColumns[$key] : false;
                ?>
                <div class="cyb-core-input-switch">
                    <input type="checkbox" id="cyb-core-input-switch[<?php echo $key; ?>]"
                           name="<?php echo $key; ?>" <?php checked(true, $idColumns[$key]); ?> >
                    <label for="cyb-core-input-switch[<?php echo $key; ?>]"></label>
                    <span><?php esc_html_e($value, 'cyb-core'); ?></span>
                    <div class="spinner-border" role="status"></div>
                </div>
            <?php }
            update_option('cyb-core-id-columns', $idColumns);
            ?>
        </form>
    </div>
</div>
