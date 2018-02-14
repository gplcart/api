<?php

/**
 * @package API
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2018, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0-or-later
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($settings['status']) ? '' : ' active'; ?>">
          <input name="settings[status]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['status']) ? '' : ' checked'; ?>>
            <?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($settings['status']) ? ' active' : ''; ?>">
          <input name="settings[status]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['status']) ? ' checked' : ''; ?>>
            <?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
          <?php echo $this->text('If disabled then all requests from all users will be rejected'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('secret', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text("Secret"); ?></label>
    <div class="col-md-4">
      <input name="settings[secret]" class="form-control" placeholder="<?php echo $this->text('Generate'); ?>" value="<?php echo isset($settings["secret"]) ? $this->e($settings["secret"]) : ""; ?>">
      <div class="help-block">
        <?php echo $this->error('secret'); ?>
        <div class="text-muted">
          <?php echo $this->text('This secret is used to sign and verify all JWT tokens issued by the module. Keep it privately! Leave empty to generate a random string'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('jwt_alg', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Hashing algorithm'); ?></label>
    <div class="col-md-4">
      <select name="settings[jwt_alg]" class="form-control">
        <?php foreach($algs as $id => $alg) { ?>
          <option value="<?php echo $this->e($id); ?>"<?php echo isset($settings['jwt_alg']) && $settings['jwt_alg'] === $id ? ' selected' : ''; ?>>
              <?php echo $this->e($id); ?>
          </option>
        <?php } ?>
      </select>
      <div class="help-block">
          <?php echo $this->error('jwt_alg'); ?>
        <div class="text-muted">
            <?php echo $this->text('Select a hashing algorithm for use in JWT header'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="form-group required<?php echo $this->error('jwt_lifetime', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Token lifetime'); ?></label>
    <div class="col-md-4">
      <input name="settings[jwt_lifetime]" class="form-control" value="<?php echo isset($settings['jwt_lifetime']) ? $this->e($settings['jwt_lifetime']) : ''; ?>">
      <div class="help-block">
          <?php echo $this->error('jwt_lifetime'); ?>
        <div class="text-muted">
            <?php echo $this->text('How many seconds a JWT token will stay valid until expired'); ?>
        </div>
      </div>
    </div>
  </div>


  <div class="form-group">
    <div class="col-md-4 col-md-offset-2">
      <div class="btn-toolbar">
        <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text("Cancel"); ?></a>
        <button class="btn btn-default save" name="save" value="1"><?php echo $this->text("Save"); ?></button>
      </div>
    </div>
  </div>
</form>