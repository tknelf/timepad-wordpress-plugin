<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !empty( $error_response ) ) : ?><div class="error"><p><?php echo $error_response; ?></p></div><?php endif; ?>

<table class="form-table">
    <tr>
        <td scope="row"><?php _e( 'To use this plugin enter a valid "timepad.ru" API token in the field here.', 'timepad' ); ?></td>
    </tr>
    <tr>
      <td>
        <input type="text" name="timepad_token" id="timepad_token" value="<?php echo (isset($data['token']) ? $data['token'] : '') ?>" size="50" maxlength="100" aria-required="true" autocomplete="off" />
        <input type="submit" class="button button-primary" name="save_token_form" value="<?php _e( 'Save', 'timepad' ); ?>" />
      </td>
    </tr>
    <tr>
      <td><p style="color:red;font-weight:bold"><?php _e( 'When you first events import check settings in the tabs to customize your events', 'timepad' ); ?></p></td>
    </tr>
</table>
