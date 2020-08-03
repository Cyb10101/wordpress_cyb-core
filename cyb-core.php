<?php
/**
 * Plugin Name: Cyb Core
 * Plugin URI: https://github.com/Cyb10101/wordpress_cyb-core
 * Description: Core plugin
 * Author: Thomas Schur
 * Version: 1.0.0
 * Author URI: https://github.com/Cyb10101
 * Text Domain: cyb-core
 * Domain Path: /languages
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

        add_filter('plugin_action_links', [$this, 'wpPluginActionLinks'], 10, 2);
    }

    public function wpAdminInit() {
        wp_enqueue_style('cyb-core-admin', plugins_url('assets/admin.css', __DIR__ . '/.'), [], false);
        wp_enqueue_script('cyb-core-admin', plugins_url('assets/admin.js', __DIR__ . '/.'), ['jquery'], false, true);
        new \App\Utility\PluginUpdaterUtility(__FILE__);
        $this->idColumnsAddToLists();
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
        $templatesDirectory = plugin_dir_path(__FILE__) . 'templates';
        ?>
        <div class="wrap cyb-core-admin">
            <?php $this->renderBreadcrumb([
                ['title' => 'General Settings'],
            ]); ?>

            <div class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" id="dashboard-tab" href="#dashboard"><?php _e('Dashboard', 'cyb-core'); ?></a>
                <a class="nav-tab" id="features-tab" href="#development"><?php _e('Development', 'cyb-core'); ?></a>
            </div>

            <div id="dashboard" class="container-3">
                <div><?php require_once($templatesDirectory . '/id-columns.php'); ?></div>
                <div>
                </div>
                <div>
                </div>
            </div>

            <div id="development" class="container-3" style="display: none;">
                <div><?php
                    // @todo translations should be copied on installation/activation
                    require_once($templatesDirectory . '/generate-translation.php'); ?></div>
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
        $templatesDirectory = plugin_dir_path(__FILE__) . 'templates';
        ?>
        <div class="wrap cyb-core-admin">
            <?php $this->renderBreadcrumb([
                ['title' => 'Statistics & Analytics'],
            ]); ?>
            <div class="container-3">
                <div><?php require_once($templatesDirectory . '/analytics-google.php'); ?></div>
                <div><?php require_once($templatesDirectory . '/analytics-matomo.php'); ?></div>
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
        wp_enqueue_style('cyb-core-frontend', plugins_url('assets/frontend.css', __FILE__), [], false);
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

    public function wpPluginActionLinks(array $links, string $file) {
        if ($file === 'cyb-core/cyb-core.php') {
            $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=cyb-core')) . '">' . __('Settings') . '</a>';
            array_push($links, $settings_link);
        }
        return $links;
    }

    protected function getAllPostTypes(array $args = ['public' => true]) {
        global $wp_post_types;

        $list = [];
        $postTypes = wp_filter_object_list($wp_post_types, $args, 'AND');
        /** @var \WP_Post_Type $postType */
        foreach ($postTypes as $postType) {
            if ($postType->name === 'attachment') {
                continue;
            }
            $list[$postType->name] = $postType->label;
        }
        return $list;
    }

    protected function idColumnsAddToLists() {
        $capabilitySorting = version_compare($GLOBALS['wp_version'], '3.0.999', '>');
        $idColumns = get_option('cyb-core-id-columns', []);

        // Posts & Pages Management
        foreach ($this->getAllPostTypes() as $key => $label) {
            if (isset($idColumns[$key]) && $idColumns[$key]) {
                add_action('manage_edit-' . $key . '_columns', [$this, 'idColumnsColumn']);
                add_filter('manage_' . $key . '_posts_custom_column', [$this, 'idColumnsValue'], 10, 3);
                if ($capabilitySorting) {
                    add_filter('manage_edit-' . $key . '_sortable_columns', [$this, 'idColumnsColumn']);
                }
            }
        }

        // Media Management
        if (isset($idColumns['media']) && $idColumns['media']) {
            add_action('manage_media_columns', [$this, 'idColumnsColumn']);
            add_filter('manage_media_custom_column', [$this, 'idColumnsValue'], 10, 3);
            if ($capabilitySorting) {
                add_filter('manage_upload_sortable_columns', [$this, 'idColumnsColumn']);
            }
        }

        // User Management
        if (isset($idColumns['user']) && $idColumns['user']) {
            add_action('manage_users_columns', [$this, 'idColumnsColumn']);
            add_filter('manage_users_custom_column', [$this, 'idColumnsReturnValue'], 10, 3);
            if ($capabilitySorting) {
                add_filter('manage_users_sortable_columns', [$this, 'idColumnsColumn']);
            }
        }

        // Comment Management
        if (isset($idColumns['comment']) && $idColumns['comment']) {
            add_action('manage_edit-comments_columns', [$this, 'idColumnsColumn']);
            add_action('manage_comments_custom_column', [$this, 'idColumnsValue'], 10, 2);
            if ($capabilitySorting) {
                add_filter('manage_edit-comments_sortable_columns', [$this, 'idColumnsColumn']);
            }
        }

        // Link Management
        add_action('manage_link_custom_column', [$this, 'idColumnsValue'], 10, 2);
        add_filter('manage_link-manager_columns', [$this, 'idColumnsColumn']);

        // Link Management: Category
        add_action('manage_edit-link-categories_columns', [$this, 'idColumnsColumn']);
        add_filter('manage_link_categories_custom_column', [$this, 'idColumnsReturnValue'], 10, 3);

        // Category, Tags and other custom taxonomies Management
        foreach (get_taxonomies() as $key => $label) {
            if (isset($idColumns['category']) && $idColumns['category']) {
                add_action('manage_edit-' . $key . '_columns', [$this, 'idColumnsColumn']);
                add_filter('manage_' . $key . '_custom_column', [$this, 'idColumnsReturnValue'], 10, 3);
                if ($capabilitySorting) {
                    add_filter('manage_edit-' . $key . '_sortable_columns', [$this, 'idColumnsColumn']);
                }
            }
        }
    }

    public function idColumnsColumn($cols) {
        $columnId = ['cyb-core-id-column' => __('ID', 'cyb-core')];
        $cols = array_slice($cols, 0, 1, true) + $columnId + array_slice($cols, 1, null, true);
        return $cols;
    }

    public function idColumnsValue($column_name, $id) {
        if ($column_name === 'cyb-core-id-column') {
            echo $id;
        }
    }

    public function idColumnsReturnValue($value, $column_name, $id) {
        if ($column_name === 'cyb-core-id-column') {
            $value .= $id;
        }
        return $value;
    }
}

CybCore::getInstance();
