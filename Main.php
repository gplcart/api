<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0-or-later
 */

namespace gplcart\modules\api;

use Exception;
use gplcart\core\Config;
use gplcart\core\Container;

/**
 * Main class for API module
 */
class Main
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->db = $config->getDb();
        $this->db->addScheme($this->getDbScheme());
    }

    /**
     * Implements hook "module.install.before"
     * @param null|string
     */
    public function hookModuleInstallBefore(&$result)
    {
        try {
            $this->db->importScheme('module_api_user', $this->getDbScheme());
        } catch (Exception $ex) {
            $result = $ex->getMessage();
        }
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->db->deleteTable('module_api_user');
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/api'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\Settings', 'editSettings')
            )
        );

        $routes['admin/user/api'] = array(
            'access' => 'module_api_user',
            'menu' => array(
                'admin' => 'API' // @text
            ),
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\User', 'listUser')
            )
        );

        $routes['admin/user/api/add'] = array(
            'access' => 'module_api_user_add',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\User', 'editUser')
            )
        );

        $routes['admin/user/api/edit/(\d+)'] = array(
            'access' => 'module_api_user_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\User', 'editUser')
            )
        );

        $routes['api'] = array(
            'internal' => true,
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\Api', 'callbackApi')
            )
        );

        $routes['api/(.*)'] = array(
            'internal' => true,
            'handlers' => array(
                'controller' => array('gplcart\\modules\\api\\controllers\\Api', 'callbackApi')
            )
        );

    }

    /**
     * Implements hook "user.role.permissions"
     * @param array $permissions
     */
    public function hookUserRolePermissions(array &$permissions)
    {
        $permissions['module_api_user'] = 'API: access users'; // @text
        $permissions['module_api_user_add'] = 'API: add user'; // @text
        $permissions['module_api_user_edit'] = 'API: edit user'; // @text
        $permissions['module_api_user_delete'] = 'API: delete user'; // @text
    }

    /**
     * Returns an array of database scheme
     * @return array
     */
    public function getDbScheme()
    {
        return array(
            'module_api_user' => array(
                'fields' => array(
                    'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
                    'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
                    'secret' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
                    'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
                    'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
                    'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
                    'api_user_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true)
                )
            )
        );
    }

    /**
     * Returns Api model instance
     * @return \gplcart\modules\api\models\Api
     */
    public function getApiModel()
    {
        /** @var \gplcart\modules\api\models\Api $instance */
        $instance = Container::get('gplcart\\modules\\api\\models\\Api');
        return $instance;
    }

    /**
     * Returns User model instance
     * @return \gplcart\modules\api\models\User
     */
    public function getUserModel()
    {
        /** @var \gplcart\modules\api\models\User $instance */
        $instance = Container::get('gplcart\\modules\\api\\models\\User');
        return $instance;
    }
}
