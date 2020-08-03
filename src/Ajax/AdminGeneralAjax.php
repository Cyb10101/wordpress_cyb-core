<?php
namespace App\Ajax;

use App\Utility\WpAjaxUtility;
use Masterminds\HTML5\Exception;

class AdminGeneralAjax extends WpAjaxUtility {
    protected $action = 'admin-general';

    protected function execute() {
        $task = !empty($_POST['task']) ? $_POST['task'] : '';
        $response = ['success' => false];

        switch ($task) {
            case 'analytics-google': $this->googleAnalytics($response); break;
            case 'analytics-matomo': $this->analyticsMatomo($response); break;
            case 'generate-translation': $this->generateTranslation($response); break;
            case 'id-columns': $this->idColumns($response); break;
            default:
        }

        wp_send_json($response);
    }

    protected function googleAnalytics(&$response) {
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
    }

    protected function analyticsMatomo(&$response) {
        $response['data'] = [
            'enabled' => (!empty($_POST['enabled']) ? true : false),
            'code' => stripslashes(!empty($_POST['code']) ? $_POST['code'] : ''),
        ];
        update_option('cyb-core-analytics-matomo', $response['data']);
        $response['success'] = true;
    }

    protected function generateTranslation(&$response) {
        $pluginLanguageDirectory = realpath($this->pluginDirectory . '/languages');
        $wpLanguageDirectory = realpath($this->pluginDirectory . '/../../languages/plugins');
        if (is_dir($this->pluginDirectory) && is_dir($pluginLanguageDirectory) && is_dir($wpLanguageDirectory)) {
            try {
                $languageNames = [
                    'cyb-core-de_DE',
                ];
                foreach ($languageNames as $languageName) {
                    // Generate binary *.mo file
                    $command = 'msgfmt -o ' . $pluginLanguageDirectory . '/' . $languageName . '.mo ' . $pluginLanguageDirectory . '/' . $languageName . '.po';
                    exec($command, $output, $returnVar);
                    if ($returnVar !== 0) {
                        throw new \Exception('Can\'t generate translation file: ' . $languageName . '.po');
                    }

                    // Copy translation files to wordpress directory
                    if (!copy($pluginLanguageDirectory . '/' . $languageName . '.po', $wpLanguageDirectory . '/' . $languageName . '.po')) {
                        throw new \Exception('Error copy file: ' . $languageName . '.po');
                    }
                    if (!copy($pluginLanguageDirectory . '/' . $languageName . '.mo', $wpLanguageDirectory . '/' . $languageName . '.mo')) {
                        throw new \Exception('Error copy file: ' . $languageName . '.mo');
                    }
                }
                $response['success'] = true;
            } catch (\Exception $exception) {
                // Only catch
            }
        }
    }

    protected function idColumns(&$response) {
        $idColumns = get_option('cyb-core-id-columns', []);
        $key = (!empty($_POST['key']) ? $_POST['key'] : '');
        if (isset($idColumns[$key])) {
            $response['data'] = [
                'enabled' => (!empty($_POST['enabled']) ? ($_POST['enabled'] === 'true') : false),
            ];

            $idColumns = array_merge($idColumns, [
                $key => $response['data']['enabled'],
            ]);
            update_option('cyb-core-id-columns', $idColumns);

            $response['success'] = true;
        }
    }
}
