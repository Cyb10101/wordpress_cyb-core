<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
} else {
    ob_start();
    ?>
    <p>This plugin install is incomplete, run <code style="color: red;">composer install</code> on the
    <code style="color: red;">wp-content/plugins/<?php echo basename(__DIR__); ?></code> plugin folder to install dependencies.</p>
    <p>For instructions on installing composer:</p>
    <ul>
        <li><a href="http://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos" target="_blank">Installation - Linux / Unix / macOS</a></li>
        <li><a href="http://getcomposer.org/doc/00-intro.md#installation-windows" target="_blank">Installation - Windows</a></li>
    </ul>
    <?php
    $message = ob_get_clean();
    if (function_exists('wp_die')) {
        wp_die($message);
    } else {
        error_log('composer install command needs to be run on this plugin');
    }
}

$doctrineUtility = \App\Utility\DoctrineUtility::getInstance();
$doctrineUtility->addMappingPath(__DIR__ . '/Classes/Entity');
$entityManager = $doctrineUtility->getEntityManager();
