<?php
/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if($this->access('module_api_user_add')) { ?>
  <div class="btn-toolbar actions">
      <a class="btn btn-default" href="<?php echo $this->url('admin/user/api/add'); ?>">
          <?php echo $this->text('Add'); ?>
      </a>
  </div>
<?php } ?>
<?php if (!empty($users)) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
<?php if ($this->access('module_api_user_delete')) { ?>
<div class="form-inline actions">
  <div class="input-group">
    <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
      <option value=""><?php echo $this->text('With selected'); ?></option>
      <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
        <?php echo $this->text('Delete'); ?>
      </option>
    </select>
    <span class="input-group-btn hidden-js">
      <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
    </span>
  </div>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>
          <input type="checkbox" onchange="Gplcart.selectAll(this);">
        </th>
        <th>
          <a href="<?php echo $sort_api_user_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a>
        </th>
        <th>
          <a href="<?php echo $sort_user_id; ?>"><?php echo $this->text('User'); ?> <i class="fa fa-sort"></i></a>
        </th>
        <th>
          <a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a>
        </th>
        <th>
          <a href="<?php echo $sort_modified; ?>"><?php echo $this->text('Modified'); ?> <i class="fa fa-sort"></i></a>
        </th>
        <th>
          <a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a>
        </th>
        <th>
          <?php echo $this->text('IP'); ?>
        </th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user) { ?>
      <tr>
        <td class="middle">
          <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $user['api_user_id']; ?>">
        </td>
        <td class="middle"><?php echo $this->e($user['api_user_id']); ?></td>
        <td class="middle"><?php echo $this->e($user['user_id']); ?></td>
        <td class="middle"><?php echo $this->date($user['created']); ?></td>
        <td class="middle"><?php echo $this->date($user['modified']); ?></td>
        <td class="middle"><?php echo empty($user['status']) ? $this->text('No') : $this->text('Yes'); ?></td>
        <td class="middle">
          <?php if(empty($user['data']['ip'])) { ?>
          <?php echo $this->text('Unlimited'); ?>
          <?php } else { ?>
              <?php echo count($user['data']['ip']); ?>
            <?php } ?>
        </td>
        <td class="middle">
          <ul class="list-inline">
            <?php if ($this->access('module_api_user_edit')) { ?>
            <a href="<?php echo $this->url("admin/user/api/edit/{$user['api_user_id']}"); ?>">
              <?php echo $this->lower($this->text('Edit')); ?>
            </a>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
</form>
<?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
<?php } ?>

