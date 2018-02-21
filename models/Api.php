<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\api\models;

use gplcart\core\helpers\Server;
use gplcart\core\Module;
use gplcart\modules\oauth\helpers\Jwt;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Manages basic behaviors and data related to API module
 */
class Api
{

    /**
     * JWT helper class instance
     * @var \gplcart\modules\oauth\helpers\Jwt $jwt
     */
    protected $jwt;

    /**
     * User model class instance
     * @var \gplcart\modules\api\models\User $user
     */
    protected $user;

    /**
     * Server helper class instance
     * @var \gplcart\core\helpers\Server $server
     */
    protected $server;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     * @param Server $server
     * @param User $user
     * @param Jwt $jwt
     */
    public function __construct(Module $module, Server $server, User $user, Jwt $jwt)
    {
        $this->jwt = $jwt;
        $this->user = $user;
        $this->module = $module;
        $this->server = $server;
    }

    /**
     * Returns encoded JWT token
     * @param int $user_id
     * @return array
     */
    public function getToken($user_id)
    {
        $time = time();
        $lifetime = $this->getLifetime();
        $host = $this->server->httpHost();

        $data = array(
            'iss' => $host,
            'iat' => $time,
            'aud' => $host,
            'sub' => $user_id,
            'exp' => $time + $lifetime
        );

        return array(
            'token_type' => 'bearer',
            'expires_in' => $lifetime,
            'access_token' => $this->jwt->encode($data, $this->getSecret(), $this->getAlg())
        );
    }

    /**
     * Returns an array of user data from the token
     * @param string $token
     * @return array
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function getUserFromToken($token)
    {
        $payload = $this->jwt->decode($token, $this->getSecret());

        if (empty($payload->sub) || !is_numeric($payload->sub)) {
            throw new UnexpectedValueException('Payload "sub" key must contain a valid user ID');
        }

        $user = $this->user->get($payload->sub);

        if (empty($user['status'])) {
            throw new UnexpectedValueException('Invalid or disabled API user');
        }

        return $user;
    }

    /**
     * Returns the secret key from the module settings
     * @return string
     * @throws InvalidArgumentException
     */
    public function getSecret()
    {
        $value = $this->module->getSettings('api', 'secret');

        if (empty($value)) {
            throw new InvalidArgumentException('Empty "secret" key in the module settings');
        }

        return $value;
    }

    /**
     * Returns the hashing algorithm from the module settings
     * @return string
     * @throws InvalidArgumentException
     */
    public function getAlg()
    {
        $value = $this->module->getSettings('api', 'jwt_alg');

        if (empty($value)) {
            throw new InvalidArgumentException('Empty "jwt_alg" key in the module settings');
        }

        return $value;
    }

    /**
     * Returns the token lifetime
     * @return int
     */
    public function getLifetime()
    {
        return (int) $this->module->getSettings('api', 'jwt_lifetime');
    }

    /**
     * Whether API access is allowed
     * @return bool
     */
    public function getStatus()
    {
        return (bool) $this->module->getSettings('api', 'status');
    }
}
