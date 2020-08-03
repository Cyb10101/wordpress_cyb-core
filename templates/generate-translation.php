<?php
$wpFormUtility = \App\Utility\WpFormUtility::getInstance();
?>
<div class="card">
    <div class="card-header"><?php _e('Development', 'cyb-core'); ?></div>
    <div class="card-body">
        <form method="post" action="<?php echo admin_url('/admin-ajax.php'); ?>" class="xhr">
            <?php wp_nonce_field('admin-general-nonce'); ?>
            <input type="hidden" name="action" value="admin-general">
            <input type="hidden" name="task" value="generate-translation"/>
            <?php
            $wpFormUtility->renderButtonSubmit('generate-translation', [
                'label' => 'Generate translation files',
                'help' => 'Maybe you need <i>msgfmt</i>: sudo apt install gettext',
            ]);
            ?>
        </form>
    </div>
</div>
