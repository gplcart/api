<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\api\models;

use gplcart\core\Config;
use gplcart\core\interfaces\Crud as CrudInterface;

/**
 * Manages basic behaviors and data related API users
 */
class User implements CrudInterface
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
    }

    /**
     * Loads an API user
     * @param array|int $condition
     * @return array
     */
    public function get($condition)
    {
        if (!is_array($condition)) {
            $condition = array('api_user_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);

        return empty($list) ? array() : reset($list);
    }

    /**
     * Returns an array of API users or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $sql = 'SELECT au.*, u.status AS user_status, u.role_id AS user_role_id';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(au.api_user_id)';
        }

        $sql .= ' FROM module_api_user au
                  LEFT JOIN user u ON(au.user_id = u.user_id)';

        $conditions = array();

        if (isset($options['api_user_id'])) {
            $sql .= ' WHERE au.api_user_id=?';
            $conditions[] = $options['api_user_id'];
        } else {
            $sql .= ' WHERE au.api_user_id IS NOT NULL';
        }

        if (isset($options['user_id'])) {
            $sql .= ' AND au.user_id=?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['secret'])) {
            $sql .= ' AND au.secret=?';
            $conditions[] = $options['secret'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND au.status=?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'api_user_id', 'user_id', 'created', 'modified', 'status');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY au.{$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY au.created DESC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $fetch_options = array('index' => 'api_user_id', 'unserialize' => 'data');
            $result = $this->db->fetchAll($sql, $conditions, $fetch_options);
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        return $result;
    }

    /**
     * Adds a new API user
     * @param array $data
     * @return int
     */
    public function add(array $data)
    {
        $data['created'] = $data['modified'] = GC_TIME;
        return (int) $this->db->insert('module_api_user', $data);
    }

    /**
     * Deletes a user
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return (bool) $this->db->delete('module_api_user', array('api_user_id' => $id));
    }

    /**
     * Updates a user
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $data['modified'] = GC_TIME;
        return (bool) $this->db->update('module_api_user', $data, array('api_user_id' => $id));
    }
}
