<?php
$analyticsMatomo = get_option('cyb-core-analytics-matomo', []);
$analyticsMatomo = wp_parse_args($analyticsMatomo, [
    'enabled' => false,
    'code' => '',
]);
?>
<div class="card">
    <div class="card-header">Matomo</div>
    <div class="card-body">
        <form method="post" action="<?php echo admin_url('/admin-ajax.php'); ?>" class="xhr">
            <?php wp_nonce_field('admin-general-nonce'); ?>
            <input type="hidden" name="action" value="admin-general">
            <input type="hidden" name="task" value="analytics-matomo"/>
            <?php
            /** @noinspection PhpUndefinedVariableInspection */
            $wpFormUtility->renderCheckbox('analytics-matomo', 'enabled', $analyticsMatomo['enabled'], [
                'label' => 'Use Matomo',
                'help' => 'Website: <a href="https://matomo.org/" target="_blank">Matomo</a>',
            ]);
            $wpFormUtility->renderTextArea('analytics-matomo', 'code', $analyticsMatomo['code'], [
                'label' => 'Code',
                'rows' => 5,
            ]);
            $wpFormUtility->renderButtonSubmit('analytics-matomo', [
            ]);
            ?>
        </form>
    </div>
</div>