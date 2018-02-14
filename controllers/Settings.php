<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0-or-later
 */

namespace gplcart\modules\api\controllers;

use gplcart\core\controllers\backend\Controller;

/**
 * Handles incoming requests and outputs data related to API module
 */
class Settings extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('algs', $this->getHashingAlgorithms());
        $this->setData('settings', $this->getModuleSettings('api'));

        $this->submitSettings();
        $this->outputEditSettings();
    }

    /**
     * Returns an array of supported hashing algorithms fo JWT tokens
     * @return array
     */
    protected function getHashingAlgorithms()
    {

        /** @var \gplcart\modules\oauth\Main $module */
        $module = $this->module->getInstance('oauth');
        return $module->getJwtHelper()->getAlgs();
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $title = $this->text('Edit %name settings', array('%name' => $this->text('API')));
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings();
        }
    }

    /**
     * Validate submitted module settings
     * @return bool
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');
        $this->setSubmittedBool('status');

        if ($this->getSubmitted('secret', '') === '') {
            $this->setSubmitted('secret', gplcart_string_random());
        }

        $this->validateElement('secret', 'length', array(8, 255));
        $this->validateElement('jwt_lifetime', 'regexp', '/^[\d]{2,4}$/');

        return !$this->hasErrors();
    }

    /**
     * Update module settings
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('api', $this->getSubmitted());
        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('api|settings');
    }
}
