<?php
/*
Plugin Name: Cyb Core
Plugin URI: https://github.com/Cyb10101/wordpress_cyb-core
Description: Core plugin
Author: Thomas Schur
Version: 1.0.0
Author URI: https://github.com/Cyb10101
*/

use App\Ajax\AdminGeneralAjax;

require_once(__DIR__ . '/autoload.php');

class CybCore extends \App\Utility\Singleton {
    /**
     * @var \App\Utility\DoctrineUtility
     */
    protected $doctrineUtility = null;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager = null;

    public function initializeInstance() {
        $this->doctrineUtility = \App\Utility\DoctrineUtility::getInstance();
        $this->doctrineUtility->addMappingPath(__DIR__ . '/Classes/Entity');
        $this->entityManager = $this->doctrineUtility->getEntityManager();

        AdminGeneralAjax::getInstance();

        add_action('admin_init', [$this, 'wpAdminInit']);
        add_action('admin_menu', [$this, 'wpAdminMenu']);

        add_action('widgets_init', [$this, 'wpWidgetsInit']);

        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts']);
        add_action('wp_footer', [$this, 'wpFooter']);
    }

    public function wpAdminInit() {
        wp_enqueue_style('cyb-core-admin', plugins_url('assets/admin.css', __DIR__ . '/.'), [], false);
        wp_enqueue_script('cyb-core-admin', plugins_url('assets/admin.js', __DIR__ . '/.'), ['jquery'], false, true);
        new \App\Utility\PluginUpdaterUtility(__FILE__);
    }

    public function wpAdminMenu() {
        /** @var \WP_User $user */
        $user = wp_get_current_user();
        $allowedRoles = ['administrator'];

        if (array_intersect($allowedRoles, $user->roles) || is_super_admin()) {
            $iconPath = plugin_dir_path(__DIR__ . '/.') . 'images/rocket_1054990.svg';
            $iconSvg = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($iconPath));
            add_menu_page('Cyb Core', 'Cyb Core', 'manage_options', 'cyb-core', [$this, 'adminPage'], $iconSvg, null);
            add_submenu_page('cyb-core', __('General', 'cyb-core'), __('General', 'cyb-core'), 'manage_options', 'cyb-core', [$this, 'adminPage']);
            add_submenu_page('cyb-core', __('Statistics & Analytics', 'cyb-core'), __('Statistics & Analytics', 'cyb-core'), 'manage_options', 'cyb-core-statistics', [$this, 'adminPageStatistics']);
        }
    }

    protected function renderBreadcrumb(array $list = []){
        ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <img src="<?php echo plugin_dir_url(__DIR__ . '/.') . 'images/rocket_1054990.svg'; ?>" class="d-inline-block align-top" alt="">
                <li class="breadcrumb-item">
                    <a href="#"><?php _e('Cyb Core', 'cyb-core'); ?></a>
                </li>
                <?php
                if (!empty($list)) {
                    for ($i = 0; $i < count($list); $i++){
                        $item = $list[$i];
                        echo '<li class="breadcrumb-item' . ($i === count($list) - 1 ? ' active' : '') . '" aria-current="page">';
                            if (!empty($item['url'])) {
                                echo '<a href="' . $item['url'] . '">';
                            }
                            echo __($item['title'], 'cyb-core');
                            if (!empty($item['url'])) {
                                echo '</a>';
                            }
                        echo '</li>';
                    }
                }
                ?>
            </ol>
        </nav>
        <?php
    }

    public function adminPage() {
        ?>
        <div class="wrap cyb-core-admin">
            <?php $this->renderBreadcrumb([
                ['title' => 'General Settings'],
            ]); ?>
            <div class="container-3">
                <div>

                    <div class="card">
                        <div class="card-header">In development</div>
                        <div class="card-body">
                            In development
                        </div>
                    </div>

                </div>
                <div>
                    <?php
                    var_dump($this->entityManager->getRepository(\App\Entity\WordPressOptions::class)->findOneBy([]));
                    var_dump($this->entityManager->getRepository(\App\Entity\WordPressUserMeta::class)->findOneBy([]));
                    ?>
                </div>
                <div>
                    <style>
                        .cyb-core-admin .pagination li {
                            display: inline-block;
                            border: 1px solid red;
                            margin-right: 1px;
                            text-decoration: none;
                            cursor: pointer;
                        }
                        .cyb-core-admin .pagination a,
                        .cyb-core-admin .pagination span {
                            display: inline-block;
                            padding: 2px 5px;
                        }
                    </style>
                    <?php
                    $page = (isset($_GET['page']) ? $_GET['page'] : '');
                    $pageSite = (int)(isset($_GET['pageSite']) ? $_GET['pageSite'] : 1);
                    $pagination = new \App\Utility\PaginationUtility();
                    $pagination->createPagination(33, 3, 10, '?page=' . $page . '&pageSite={page}', $pageSite)
                        ->setClass('pagination justify-content-center')
                        ->setButtonsText('&lt;&lt;', '&lt;', '&gt;', '&gt;&gt;');

                    echo 'Items:';
                    for($i = $pageSite; $i < $pageSite + 3; $i++) {
                        echo ' ' . $i;
                    }
                    echo $pagination->getPagination();
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function adminPageStatistics() {
        $wpFormUtility = \App\Utility\WpFormUtility::getInstance();

        $analyticsGoogle = get_option('cyb-core-analytics-google', []);
        $analyticsGoogle = wp_parse_args($analyticsGoogle, [
            'enabled' => false,
            'tag' => '',
            'ownCode' => false,
            'code' => '',
        ]);

        $analyticsMatomo = get_option('cyb-core-analytics-matomo', []);
        $analyticsMatomo = wp_parse_args($analyticsMatomo, [
            'enabled' => false,
            'code' => '',
        ]);
        ?>
        <div class="wrap cyb-core-admin">
            <?php $this->renderBreadcrumb([
                ['title' => 'Statistics & Analytics'],
            ]); ?>
            <div class="container-3">
                <div>

                    <div class="card">
                        <div class="card-header">Google Analytics</div>
                        <div class="card-body">
                            <form method="post" action="<?php echo admin_url('/admin-ajax.php'); ?>" class="analytics-google">
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

                </div>
                <div>

                    <div class="card">
                        <div class="card-header">Matomo</div>
                        <div class="card-body">
                            <form method="post" action="<?php echo admin_url('/admin-ajax.php'); ?>" class="analytics-matomo">
                                <?php wp_nonce_field('admin-general-nonce'); ?>
                                <input type="hidden" name="action" value="admin-general">
                                <input type="hidden" name="task" value="analytics-matomo"/>
                                <?php
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

                </div>
                <div>

                </div>
            </div>
        </div>
        <?php
    }

    public function wpWidgetsInit() {
        register_widget('App\Widget\LoginWidget');
        register_widget('App\Widget\NavigationWidget');
    }

    public function wpEnqueueScripts() {
        wp_enqueue_style('cyb-core-frontend', plugins_url('assets/frontend.css', dirname(__DIR__)));
    }

    public function wpFooter() {
        $analyticsGoogle = get_option('cyb-core-analytics-google', []);
        if (isset($analyticsGoogle['enabled']) && $analyticsGoogle['enabled']) {
            echo $analyticsGoogle['code'];
        }

        $analyticsMatomo = get_option('cyb-core-analytics-matomo', []);
        if (isset($analyticsMatomo['enabled']) && $analyticsMatomo['enabled']) {
            echo $analyticsMatomo['code'];
        }
    }
}

CybCore::getInstance();
