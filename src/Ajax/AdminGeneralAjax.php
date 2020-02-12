<?php
namespace App\Ajax;

use App\Utility\WpAjaxUtility;

class AdminGeneralAjax extends WpAjaxUtility {
    protected $action = 'admin-general';

    protected function execute() {
        $task = !empty($_POST['task']) ? $_POST['task'] : '';
        $response = ['success' => false];

        if ($task === 'analytics-google') {
            $response['data'] = [
                'enabled' => (!empty($_POST['enabled']) ? true : false),
                'tag' => (!empty($_POST['tag']) ? $_POST['tag'] : ''),
                'ownCode' => (!empty($_POST['ownCode']) ? true : false),
                'code' => stripslashes(!empty($_POST['code']) ? $_POST['code'] : ''),
            ];

            if (!$response['data']['ownCode'] && $response['data']['tag'] !== '') {
                $response['data']['code'] = ''
                    . '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $response['data']['tag'] . '"></script>' . "\n"
                    . '<script>' . "\n"
                    . 'window.dataLayer = window.dataLayer || [];' . "\n"
                    . 'function gtag(){dataLayer.push(arguments);}' . "\n"
                    . 'gtag(\'js\', new Date());' . "\n"
                    . 'gtag(\'config\', \'' . $response['data']['tag'] . '\');' . "\n"
                    . '</script>' . "\n";
            } else if (!$response['data']['ownCode'] && $response['data']['enabled'] && $response['data']['tag'] === '') {
                $response['data']['code'] = '<!-- Google analytics tag missing -->';
            } else if (!$response['data']['ownCode'] && !$response['data']['enabled']) {
                $response['data']['code'] = '';
            }

            update_option('cyb-core-analytics-google', $response['data']);
            $response['success'] = true;
        } else if ($task === 'analytics-matomo') {
            $response['data'] = [
                'enabled' => (!empty($_POST['enabled']) ? true : false),
                'code' => stripslashes(!empty($_POST['code']) ? $_POST['code'] : ''),
            ];
            update_option('cyb-core-analytics-matomo', $response['data']);
            $response['success'] = true;
        }

        wp_send_json($response);
    }
}
