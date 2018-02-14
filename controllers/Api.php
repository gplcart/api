<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\api\controllers;

use Exception;
use gplcart\core\controllers\frontend\Controller;
use gplcart\modules\api\models\Api as ApiModel;
use gplcart\modules\api\models\User;
use RuntimeException;
use UnexpectedValueException;

/**
 * Handles incoming requests and outputs data related to API requests
 */
class Api extends Controller
{

    /**
     * Api model class instance
     * @var \gplcart\modules\api\models\Api $api
     */
    protected $api;

    /**
     * User model class instance
     * @var \gplcart\modules\api\models\User $apiuser
     */
    protected $apiuser;

    /**
     * The current API user
     * @var array
     */
    protected $data_user = array();

    /**
     * An array of arguments from URL
     * @var array
     */
    protected $data_arguments = array();

    /**
     * An array of API data to output
     * @var array
     */
    protected $data_response;

    /**
     * @param User $user
     * @param ApiModel $api
     */
    public function __construct(User $user, ApiModel $api)
    {
        parent::__construct();

        $this->api = $api;
        $this->apiuser = $user;
    }

    /**
     * Page callback
     * @param string $arguments
     */
    public function callbackApi($arguments = null)
    {
        try {
            $this->controlAccessApi();
            $this->setUserApi();
            $this->outputTokenApi();
            $this->setArgumentsApi($arguments);
            $this->setResponseApi();
        } catch (Exception $ex) {
            $code = $ex->getCode() ?: 403;
            $this->response->outputStatus($code);
        }

        $this->response->outputStatus(501);
    }

    /**
     * Controls access to API
     * @throws RuntimeException
     */
    protected function controlAccessApi()
    {
        if (!$this->api->getStatus()) {
            throw new RuntimeException('API is disabled', 403);
        }
    }

    /**
     * Sets the user data
     */
    protected function setUserApi()
    {
        $this->data_user = array();

        $client_id = $this->getPosted('client_id');
        $client_secret = $this->getPosted('client_secret');

        if (isset($client_id) && isset($client_secret)) {

            $condition = array(
                'secret' => $client_secret,
                'api_user_id' => $client_id
            );

            $this->data_user = $this->apiuser->get($condition);

            if (empty($this->data_user['status'])) {
                throw new RuntimeException('API user is disabled or not found', 403);
            }

            if (empty($this->data_user['user_status'])) {
                throw new RuntimeException('System user is disabled or not found', 403);
            }

            if (!empty($this->data_user['data']['ip']) && !in_array($this->getIp(), $this->data_user['data']['ip'])) {
                throw new RuntimeException('IP is not allowed for this API user', 403);
            }
        }
    }

    /**
     * Output JWT token
     */
    protected function outputTokenApi()
    {
        if (isset($this->data_user['api_user_id'])) {
            $token = $this->api->getToken($this->data_user['api_user_id']);
            $this->outputJson($token);
        }
    }

    /**
     * Output API response
     */
    protected function setResponseApi()
    {
        $this->data_response = null;

        $token = $this->getTokenFromHeaderApi();

        if (isset($token)) {

            try {
                $this->data_user = $this->api->getUserFromToken($token);
            } catch (Exception $ex) {
                throw new RuntimeException($ex->getMessage(), 401);
            }

            try {

                $this->hook->attach('module.api.data', $this->data_arguments, $this->data_user, $this->data_response, $this);

                if (isset($this->data_response)) {
                    $this->hook->attach('module.api.output', $this->data_arguments, $this->data_user, $this->data_response, $this);
                    $this->outputJson($this->data_response);
                }

            } catch (Exception $ex) {
                throw new RuntimeException($ex->getMessage(), 500);
            }
        }
    }

    /**
     * Returns a JWT token from the request header
     * @return null|string
     * @throws UnexpectedValueException
     */
    protected function getTokenFromHeaderApi()
    {
        $header = $this->server->header('Authorization');

        if (!isset($header)) {
            return null;
        }

        $parts = explode(' ', $header);

        if (count($parts) != 2) {
            throw new UnexpectedValueException('Invalid authorization header value', 400);
        }

        if (empty($parts[1])) {
            throw new UnexpectedValueException('Empty authorization token', 400);
        }

        return $parts[1];
    }

    /**
     * Sets an array of arguments from a string
     * @param string $path
     * @throws UnexpectedValueException
     */
    protected function setArgumentsApi($path)
    {
        $this->data_arguments = array_filter(explode('/', trim($path, '/')));

        if (empty($this->data_arguments)) {
            throw new UnexpectedValueException('Invalid number of arguments passed', 400);
        }

        $version = $this->getQuery('version');

        if (isset($version) && version_compare($version, '0.0.1', '>=') < 0) {
            throw new UnexpectedValueException('Version has invalid value', 400);
        }

        $this->data_arguments[] = $version;
    }

}
