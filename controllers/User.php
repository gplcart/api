<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\api\controllers;

use gplcart\core\controllers\backend\Controller;
use gplcart\modules\api\models\User as UserModel;

/**
 * Handles incoming requests and outputs data related to API users
 */
class User extends Controller
{

    /**
     * User model class instance
     * @var \gplcart\modules\api\models\User $user_model
     */
    protected $user_model;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current updating user
     * @var array
     */
    protected $data_user = array();

    /**
     * @param UserModel $user
     */
    public function __construct(UserModel $user)
    {
        parent::__construct();

        $this->user_model = $user;
    }

    /**
     * Route callback
     * Displays the API users overview page
     */
    public function listUser()
    {
        $this->actionListUser();

        $this->setTitleListUser();
        $this->setBreadcrumbListUser();
        $this->setFilterListUser();
        $this->setPagerListUser();

        $this->setData('users', $this->getListUser());
        $this->outputListUser();
    }

    /**
     * Applies an action to the selected users
     */
    protected function actionListUser()
    {
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;

        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('module_api_user_delete')) {
                $deleted += (int) $this->user_model->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets filter parameters
     */
    protected function setFilterListUser()
    {
        $this->setFilter(array('created', 'modified', 'name', 'api_user_id', 'user_id'));
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListUser()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->user_model->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of API users
     * @return array
     */
    protected function getListUser()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;

        return $this->user_model->getList($options);
    }

    /**
     * Sets title on the credential overview page
     */
    protected function setTitleListUser()
    {
        $this->setTitle($this->text('API users'));
    }

    /**
     * Sets breadcrumbs on the user overview page
     */
    protected function setBreadcrumbListUser()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the user overview page
     */
    protected function outputListUser()
    {
        $this->output('api|user/list');
    }

    /**
     * Page callback
     * Displays the edit user page
     * @param null|int $api_user_id
     */
    public function editUser($api_user_id = null)
    {
        $this->setUser($api_user_id);
        $this->setTitleEditUser();
        $this->setBreadcrumbEditUser();

        $this->setData('user', $this->data_user);

        $this->submitEditUser();
        $this->setDataEditUser();
        $this->outputEditUser();
    }

    /**
     * Prepare template variables
     */
    protected function setDataEditUser()
    {
        $data = $this->getData('user.data.ip');

        if (is_array($data)) {
            $this->setData('user.data.ip', implode(PHP_EOL, $data));
        }
    }

    /**
     * Sets an API user
     * @param $api_user_id
     */
    protected function setUser($api_user_id)
    {
        if (is_numeric($api_user_id)) {
            $this->data_user = $this->user_model->get($api_user_id);
            if (empty($this->data_user)) {
                $this->outputHttpStatus(403);
            }
        }
    }

    /**
     * Sets titles on the edit user page
     */
    protected function setTitleEditUser()
    {
        if (isset($this->data_user['api_user_id'])) {
            $text = $this->text('Edit %name', array('%name' => $this->data_user['api_user_id']));
        } else {
            $text = $this->text('Add user');
        }

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the user edit page
     */
    protected function setBreadcrumbEditUser()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/user/api'),
            'text' => $this->text('Users')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Handles a submitted user
     */
    protected function submitEditUser()
    {
        if ($this->isPosted('delete') && isset($this->data_user['api_user_id'])) {
            $this->deleteUser();
        } else if ($this->isPosted('save') && $this->validateEditUser()) {
            if (isset($this->data_user['api_user_id'])) {
                $this->updateUser();
            } else {
                $this->addUser();
            }
        }
    }

    /**
     * Validates a submitted user data
     */
    protected function validateEditUser()
    {
        $this->setSubmitted('user');
        $this->setSubmittedBool('status');
        $this->setSubmittedArray('data.ip');

        if ($this->getSubmitted('secret', '') === '') {
            $this->setSubmitted('secret', gplcart_string_random(16));
        }

        $this->validateElement('secret', 'length', array(8, 255));
        $this->validateElement('user_id', 'regexp', '/^[\d]{1,10}$/');

        if ($this->isError()) {
            return !$this->hasErrors();
        }

        $user_id = $this->getSubmitted('user_id');

        if (!$this->user->get($user_id)) {
            $this->setError('user_id', $this->text('Invalid user'));
            return !$this->hasErrors();
        }

        $existing = $this->user_model->getList(array('user_id' => $user_id));

        if (isset($this->data_user['api_user_id'])) {
            unset($existing[$this->data_user['api_user_id']]);
        }

        if (!empty($existing)) {
            $this->setError('user_id', $this->text('API user already created for the system user ID'));
        }

        return !$this->hasErrors();
    }

    /**
     * Updates a submitted user
     */
    protected function updateUser()
    {
        $this->controlAccess('module_api_user_edit');

        if ($this->user_model->update($this->data_user['api_user_id'], $this->getSubmitted())) {
            $this->redirect('admin/user/api', $this->text('User has been updated'), 'success');
        }

        $this->redirect('', $this->text('User has not been updated'), 'warning');
    }

    /**
     * Adds a new user
     */
    protected function addUser()
    {
        $this->controlAccess('module_api_user_add');

        if ($this->user_model->add($this->getSubmitted())) {
            $this->redirect('admin/user/api', $this->text('User has been added'), 'success');
        }

        $this->redirect('', $this->text('User has not been added'), 'warning');
    }

    /**
     * Delete a submitted user
     */
    protected function deleteUser()
    {
        $this->controlAccess('module_api_user_delete');

        if ($this->user_model->delete($this->data_user['api_user_id'])) {
            $this->redirect('admin/user/api', $this->text('User has been deleted'), 'success');
        }

        $this->redirect('', $this->text('User has not been deleted'), 'warning');
    }

    /**
     * Render and output the user edit page
     */
    protected function outputEditUser()
    {
        $this->output('api|user/edit');
    }

}
