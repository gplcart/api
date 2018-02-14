<?php
/**
 * @package API
 * @author Iurii Makukh
 * @copyright Copyright (c) 2018, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($user['status']) ? '' : ' active'; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="1"<?php echo empty($user['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($user['status']) ? ' active' : ''; ?>">
          <input name="user[status]" type="radio" autocomplete="off" value="0"<?php echo empty($user['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
        <?php echo $this->text('Disabled users will not be allowed for API requests'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('user_id', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('User ID'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="user[user_id]" class="form-control" value="<?php echo isset($user['user_id']) ? $this->e($user['user_id']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('user_id'); ?>
        <div class="text-muted">
          <?php echo $this->text('<a href="@url">System user ID</a> associated with the API user', array('@url' => $this->url('admin/user/list'))); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group <?php echo $this->error('secret', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Secret'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="user[secret]" class="form-control" placeholder="<?php echo $this->text('Generate'); ?>" value="<?php echo isset($user['secret']) ? $this->e($user['secret']) : ''; ?>">
      <div class="help-block">
          <?php echo $this->error('secret'); ?>
        <div class="text-muted">
            <?php echo $this->text('This secret should be provided along with the API user ID to get authorization token while requesting API data. Leave empty to generate a random string'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group <?php echo $this->error('data.ip', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('IP'); ?></label>
    <div class="col-md-4">
      <textarea name="user[data][ip]" rows="6" class="form-control" placeholder="<?php echo $this->text('Unlimited'); ?>"><?php echo isset($user['data']['ip']) ? $this->e($user['data']['ip']) : ''; ?></textarea>
      <div class="help-block">
        <?php echo $this->error('data.ip'); ?>
        <div class="text-muted">
          <?php echo $this->text('List of client IP allowed for requesting API data. One address per line. Leave empty to allow all IP addresses'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if (isset($user['api_user_id']) && $this->access('module_api_user_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/user/api'); ?>" class="btn btn-default"><?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('module_api_user_add') || $this->access('module_api_user_edit')) { ?>
        <button class="btn btn-default save" name="save" value="1"><?php echo $this->text('Save'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>


