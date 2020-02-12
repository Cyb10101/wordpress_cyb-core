<?php
namespace App\Utility;

class PluginUpdaterUtility {
    protected $pluginSlug = '';
    protected $pluginData = null;
    protected $pluginFile = '';
    protected $repository = null;
    protected $repositoryData = [];

    public function __construct(string $pluginFile, string $repositoryUrl = '') {
        $this->pluginFile = $pluginFile;
        if (!empty($repositoryUrl)) {
            $this->setRepositoryByUrl($repositoryUrl);
        }

        add_filter('pre_set_site_transient_update_plugins', [$this, 'setTransient']);
        add_filter('plugins_api', [$this, 'setPluginInfo'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'postInstall'], 10, 3);
    }

    protected function setRepositoryByUrl(string $url) {
        if ($this->repository) {
            return;
        }
        if (preg_match('/https?:\/\/(?:www.)?github.com\/([^\/]+)\/([^\/]+)/i', $url, $matches)) {
            $this->repository = new \stdClass();
            $this->repository->provider = 'github';
            $this->repository->url = $matches[0];
            $this->repository->username = $matches[1];
            $this->repository->project = $matches[2];
            $this->repository->apiUrlRoot = 'https://api.github.com/repos/' . $this->repository->username . '/' . $this->repository->project;
            $this->retrieveGithub();
        }
    }

    protected function retrieveGithub() {
        $tags = $this->getApi('tags');
        if ($tags && is_array($tags)) {
            $tags = $this->getHighestTag($tags);
            if (!empty($tags->name) && $this->getOnlyVersion($tags->name) && !empty($tags->zipball_url)) {
                $this->repositoryData['version'] = $this->getOnlyVersion($tags->name);
                $this->repositoryData['package'] = $tags->zipball_url;
                if (!empty($tags->commit->sha)) {
                    $this->repositoryData['hash'] = $tags->commit->sha;
                }
            }
        }
    }

    protected function retrieveCurrentPluginData() {
        if (empty($this->pluginSlug)) {
            $this->pluginSlug = plugin_basename($this->pluginFile);
        }
        if (!$this->pluginData) {
            $this->pluginData = get_plugin_data($this->pluginFile);
            if (!$this->repository && !empty($this->pluginData['PluginURI'])) {
                $this->setRepositoryByUrl($this->pluginData['PluginURI']);
            }
        }
    }

    protected function buildUrl(string $url) {
        if (!$this->repository) {
            if ($this->repository->provider === 'github' && !empty($this->githubAccessToken)) {
                $url = add_query_arg(['access_token' => $this->githubAccessToken], $url);
            }
        }
        return $url;
    }

    protected function getApi(string $path) {
        if (!$this->repository) {
            return null;
        }
        $url = $this->buildUrl($this->repository->apiUrlRoot . '/' . $path);
        $result = wp_remote_retrieve_body(wp_remote_get($url));
        if (empty($result)) {
            $this->renderError('Error: Api result empty!');
            return null;
        }

        $result = @json_decode($result);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->renderError('Error: No valid json received!');
            return null;
        }
        if ($this->repository->provider === 'github') {
            if ($result instanceof \stdClass && isset($result->message) && $result->message === 'Not Found') {
                $this->renderError('Error: Api not found!');
                return null;
            }
        }
        return $result;
    }

    /**
     * Push plugin information to get the update notification
     *
     * @param \stdClass $transient
     * @return \stdClass
     */
    public function setTransient($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->retrieveCurrentPluginData();
        if (!$this->repository || empty($this->repositoryData['version']) || empty($this->repositoryData['package'])) {
            return $transient;
        }

        $updateAvailable = version_compare($this->repositoryData['version'], $transient->checked[$this->pluginSlug]) ? true : false;
        if ($updateAvailable) {
            $obj = new \stdClass();
            $obj->slug = $this->pluginSlug;
            $obj->new_version = $this->repositoryData['version'];
            $obj->url = $this->pluginData['PluginURI'];
            $obj->package = $this->buildUrl($this->repositoryData['package']);

            $obj->icons = [];
            $pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->pluginSlug);
            if (is_readable($pluginFolder . '/assets/icon-256x256.png')) {
                $obj->icons['default'] = plugins_url('assets/icon-256x256.png', $this->pluginFile);
            }

            $transient->response[$this->pluginSlug] = $obj;
        }
        return $transient;
    }

    /**
     * Push plugin information to display in the details
     *
     * @param bool $false
     * @param string $action
     * @param \stdClass $response
     * @return bool|\stdClass
     */
    public function setPluginInfo($false, $action, $response) {
        $this->retrieveCurrentPluginData();
        if (empty($response->slug) || $response->slug != $this->pluginSlug) {
            return false;
        }
        if (!$this->repository) {
            return false;
        }

        // Add plugin information
        $response->slug = $this->pluginSlug;
        $response->plugin_name = $this->pluginData['Name'];
        $response->version = $this->repositoryData['version'];
        $response->author = $this->pluginData['AuthorName'];
        $response->homepage = $this->pluginData['PluginURI'];
        $response->download_link = $this->buildUrl($this->repositoryData['package']);
//            $response->requires = '5.0'; // WordPress required
//            $response->tested = '5.0'; // WordPress compatible

        // Create tabs
        $response->sections = [];
        $response->sections['description'] = $this->pluginData['Description'];

        // Retrieve more by provider
        if (!empty($this->repositoryData['hash'])) {
            if ($this->repository->provider === 'github') {
                $commit = $this->getApi('commits/' . $this->repositoryData['hash']);
                $response->last_updated = $commit->commit->author->date;
                $response->sections['changelog'] = '<b>Last commit:</b><br>' . $commit->commit->message;
            }
        }

        return $response;
    }

    /**
     * Perform additional actions to successfully install our plugin
     *
     * @param bool $true
     * @param array $hook_extra
     * @param array $result
     * @return array
     */
    public function postInstall($true, $hook_extra, $result) {
        $this->retrieveCurrentPluginData();
        $wasActivated = is_plugin_active($this->pluginSlug);

        // Rename folder
        global $wp_filesystem;
        $pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->pluginSlug);
        $wp_filesystem->move($result['destination'], $pluginFolder);
        $result['destination'] = $pluginFolder;

        if ($wasActivated) {
            $activate = activate_plugin($this->pluginSlug);
        }
        return $result;
    }

    protected function getOnlyVersion(string $version, bool $exact = true) {
        $expression = '/(?:v)' . ($exact ? '' : '?') . '((?:[0-9]+\.?)+)/i';
        preg_match($expression, $version, $matches);
        return $matches[1] ?? null;
    }

    protected function getHighestTag(array $tags) {
        return array_reduce($tags, function ($highest, $current) {
            if (!$highest) {
                return $current;
            }
            return version_compare($highest->name, $current->name, '>') ? $highest : $current;
        });
    }

    protected function renderError(string $message) {
        ?><script type="text/javascript">
        console.error('<?php echo '[' . $this->pluginSlug . ' updater] ' . $message; ?>');
        </script><?php
    }
}

