<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !empty( $data['organizations'] ) ) : ?>
<table class="form-table">
    <tr>
        <th scope="row"><?php _e( 'TimePad account', 'timepad' ); ?></th>
        <th><?php echo $data['organizations']['user_email']; ?></th>
        <td><input type="submit" name="cancel_account" class="button button-secondary" value="<?php _e( 'Cancel account', 'timepad' ); ?>" onclick="return confirm('<?php echo __( 'Are you sure about cancel account', 'timepad' ) . ' ' . $data['organizations']['user_email'] . '?'; ?>');" /></td>
        <td></td>
    </tr>
    <?php if ( empty( $data['current_organization_id'] ) ) : ?>
    <tr>
        <td colspan="3">
            <p><?php _e( 'To start work with events, select one of TimePad organizations', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row">
            <?php if ( isset( $data['organizations']['organizations'] ) ) : ?>
            <select name="organization">
                <?php foreach ( $data['organizations']['organizations'] as $organization_id => $organization ) : 
                    if ( !empty( $organization ) && isset( $organization['name'] ) ) : ?>
                    <option value="<?php echo intval( $organization_id ); ?>"><?php echo esc_attr( $organization['name'] ); ?></option>
                <?php endif;
                endforeach; ?>
            </select>
            <?php endif; ?>
        </th>
        <td><input type="submit" name="select_organization" class="button button-primary" value="<?php _e( 'Select', 'timepad' ); ?>" /></td>
        <td colspan="2"></td>
    </tr>
    <?php else : ?>
    <tr>
        <th scope="row"><?php _e( 'Organization', 'timepad' ) ?></th>
        <th><?php echo $data['organizations']['organizations'][$data['current_organization_id']]['name']; ?></th>
        <td><input type="submit" name="cancel_organization" class="button button-secondary" value="<?php _e( 'Cancel organization', 'timepad' ); ?>" onclick="return confirm('<?php echo __( 'Are you sure about cancel organization', 'timepad' ) . ' ' . $data['organizations']['organizations'][$data['current_organization_id']]['name'] . '?'; ?>');" /></td>
        <td></td>
    </tr>
    <?php endif;
    if ( !empty( $data['current_organization_id'] ) && !empty( $category ) ) : ?>
    <tr>
        <td colspan="3" width="60%">
            <h3 class="title"><?php _e( 'Import TimePad events as custom post types', 'timepad' ); ?></h3>
            <p><?php _e( 'All your organization events will be automatically imported to this site in the form of pages with customizable categories', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><?php _e( 'Import status', 'timepad' ) ?></th>
        <th colspan="2">
            <label for="timepad_autoimport">
                <?php $autoimport = isset( $data['autoimport'] ) ? 1 : 0; ?>
                <input type="checkbox" name="timepad_autoimport" id="timepad_autoimport" value="1" <?php checked( 1, $autoimport ); ?> />
                <?php _e( 'Automatically import events and create entries for the ones', 'timepad' ); ?>
            </label>
        </th>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><input type="submit" name="syncronize" class="button button-secondary" value="<?php _e( 'Synchronize now', 'timepad' ); ?>" /></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <th scope="row"><label for="cat_name"><?php _e( 'The name of the event category', 'timepad' ) ?></label></th>
        <td colspan="2">
            <input type="text" name="cat_name" id="cat_name" value="<?php echo esc_attr( $category->name ); ?>" maxlength="50" required="required" aria-required="true" autocomplete="off" />
            <input type="hidden" name="cat_name_current" value="<?php echo esc_attr( $category->name ); ?>" />
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><label for="cat_slug"><?php _e( 'Events category label', 'timepad' ) ?></label></th>
        <td colspan="2">
            <input type="text" name="cat_slug" id="cat_slug" value="<?php echo esc_attr( $category->slug ); ?>" maxlength="50" required="required" aria-required="true" autocomplete="off" />
            <input type="hidden" name="cat_slug_current" value="<?php echo sanitize_title( esc_attr( $category->slug ) ); ?>" />
        </td>
        <td></td>
    </tr>
</table>
<input type="submit" class="button button-primary" name="save_changes" value="<?php _e( 'Save changes', 'timepad' ); ?>" />
<?php else : ?>
</table>
<?php endif;
endif;