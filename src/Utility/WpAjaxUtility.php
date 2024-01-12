<?php
namespace App\Utility;

class WpAjaxUtility extends Singleton {
    protected $action = '';
    protected $public = false;
    protected $pluginDirectory = '';

    protected function initializeInstance() {
        $this->pluginDirectory = realpath(__DIR__ . '/../..');
        if ($this->public) {
            add_action('wp_ajax_nopriv_' . $this->action, [$this, 'bootstrap']);
        } else {
            add_action('wp_ajax_' . $this->action, [$this, 'bootstrap']);
        }
    }

    public function bootstrap() {
        $this->requireAdmin();
        $this->checkCSRF();
        $this->execute();
        $this->returnBack();
    }

    protected function requireAdmin() {
        if (!current_user_can('administrator')) {
            wp_die(__('Access denied!', 'cyb-core'), '', 403);
        }
    }

    protected function checkCSRF() {
        $submittedNonce = sanitize_text_field($_REQUEST['_wpnonce'] ?? '');
        if (!wp_verify_nonce($submittedNonce, $this->action . '-nonce')) {
            wp_die(__('CSRF ERROR: Nonce not valid!', 'cyb-core'), '', 403);
        }
    }

    protected function execute() {
        wp_die(__('Function was not developed correctly!', 'cyb-core'), '', 403);
    }

    public function isRequestAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    public function returnBack() {
        if (!$this->isRequestAjax() && isset($_SERVER['HTTP_REFERER'])) {
            \App\Utility\HttpUtility::redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
