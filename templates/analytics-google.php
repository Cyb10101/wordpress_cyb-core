<?php
$wpFormUtility = \App\Utility\WpFormUtility::getInstance();
$analyticsGoogle = get_option('cyb-core-analytics-google', []);
$analyticsGoogle = wp_parse_args($analyticsGoogle, [
    'enabled' => false,
    'tag' => '',
    'ownCode' => false,
    'code' => '',
]);
?>
<div class="card">
    <div class="card-header">Google Analytics</div>
    <div class="card-body">
        <form method="post" action="<?php echo admin_url('/admin-ajax.php'); ?>" class="xhr">
            <?php wp_nonce_field('admin-general-nonce'); ?>
            <input type="hidden" name="action" value="admin-general">
            <input type="hidden" name="task" value="analytics-google"/>
            <?php
            $wpFormUtility->renderCheckbox('analytics-google', 'enabled', $analyticsGoogle['enabled'], [
                'label' => 'Use Google Analytics',
                'help' => 'Website: <a href="https://analytics.google.com/" target="_blank">Google Analytics</a>',
            ]);
            $wpFormUtility->renderTextBox('analytics-google', 'tag', $analyticsGoogle['tag'], [
                'label' => 'GTAG Nummer',
                'help' => 'Your Google Analytics ID (UA-XXXXXXXX-X)',
                'placeholder' => 'UA-XXXXXXXX-X',
                'pattern' => 'UA-[\d]+-[\d]+',
            ]);
            $wpFormUtility->renderCheckbox('analytics-google', 'ownCode', $analyticsGoogle['ownCode'], [
                'label' => 'Use own code',
            ]);
            $wpFormUtility->renderTextArea('analytics-google', 'code', $analyticsGoogle['code'], [
                'label' => 'Code',
                'rows' => 7,
            ]);
            $wpFormUtility->renderButtonSubmit('analytics-google', [
            ]);
            ?>
        </form>
    </div>
</div>