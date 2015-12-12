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
    <tr>
        <td colspan="3" width="60%">
            <h3 class="title"><?php _e( 'Automatic TimePad javascript file include (recommended)', 'timepad' ); ?></h3>
            <p><?php _e( 'External javascript file inits all TimePad scripts for widget output', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><?php _e( 'Include status', 'timepad' ) ?></th>
        <th colspan="2">
            <label for="timepad_auto_js_include">
                <?php $autojsinclude = ( isset( $data['auto_js_include'] ) && !empty( $data['auto_js_include'] ) ) ? 1 : 0; ?>
                <input type="checkbox" name="timepad_auto_js_include" id="timepad_auto_js_include" value="1" <?php checked( 1, $autojsinclude ); ?> />
                <?php _e( 'Automatically include external js file', 'timepad' ); ?>
            </label>
        </th>
        <td></td>
    </tr>
    <tr>
        <td colspan="3" width="60%">
            <h3 class="title"><?php _e( 'Automatic unsyncronize events to posts/other post types', 'timepad' ); ?></h3>
            <p><?php _e( 'All your organization events will be automatically unsyncronized from TimePad API', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><?php _e( 'Unsyncronize status', 'timepad' ) ?></th>
        <th colspan="2">
            <label for="timepad_auto_unsyncronize">
                <?php $autounsync = ( isset( $data['autounsync'] ) && !empty( $data['autounsync'] ) ) ? 1 : 0; ?>
                <input type="checkbox" name="timepad_auto_unsyncronize" id="timepad_auto_unsyncronize" value="1" <?php checked( 1, $autounsync ); ?> />
                <?php _e( 'Automatically unsyncronize events to posts/post types', 'timepad' ); ?>
            </label>
        </th>
        <td></td>
    </tr>
    <tr class="timepad_unsync_tr"<?php echo ( !isset( $data['autounsync'] ) || empty( $data['autounsync'] ) ) ? ' style="display:none"' : ''; ?>>
        <th scope="row"><label for="timepad_autounsync_to_post_type"><?php _e( 'To post type', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <div class="timepad-ajaxloader-wrapper">
                <select name="timepad_autounsync_to_post_type" id="timepad_autounsync_to_post_type">
                <?php foreach ( $post_types as $post_type => $post_type_name ) : ?>
                    <option value="<?php echo $post_type; ?>"<?php selected( $post_type, isset( $data['autounsync_to_post_type'] ) ? $data['autounsync_to_post_type'] : '' ); ?>><?php echo $post_type_name; ?></option>
                <?php endforeach; ?>
                </select>
            </div>
        </td>
        <td></td>
    </tr>
    <tr class="timepad_unsync_tr"<?php echo ( !isset( $data['autounsync'] ) || empty( $data['autounsync'] ) ) ? ' style="display:none"' : ''; ?>>
        <th scope="row"><label for="timepad_autounsync_to_post_category"><?php _e( 'To category', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <div id="timepad_autounsync_to_post_categories">
                <?php
                $categories = get_categories(
                    array(
                        'hide_empty' => false
                        ,'type'      => ( isset( $data['autounsync_to_post_type'] ) && !empty( $data['autounsync_to_post_type'] ) ) ? $data['autounsync_to_post_type'] : 'post'
                    )
                );
                if ( $categories ) :
                ?>
                <select name="timepad_autounsync_to_post_category" id="timepad_autounsync_to_post_category">
                    <?php
                    foreach( $categories as $category ) :
                        $cat_id = intval( $category->term_id );
                        if ( $category->slug != TIMEPADEVENTS_POST_TYPE_CATEGORY ) :
                        ?>
                        <option value="<?php echo $cat_id; ?>"<?php selected( $cat_id, isset( $data['autounsync_to_post_category'] ) ? $data['autounsync_to_post_category'] : '' ); ?>><?php echo $category->name; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3" width="60%">
            <h3 class="title"><?php _e( 'Event/post widget output regulation', 'timepad' ); ?></h3>
            <p><?php _e( 'You can choose how to display the TimePad widget at the event/post or display the one singly', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><label for="timepad_widget_regulation"><?php _e( 'To post type', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <select name="timepad_widget_regulation" id="timepad_widget_regulation">
                <option value="auto_after_desc"<?php selected( 'auto_after_desc', isset( $data['widget_regulation'] ) ? $data['widget_regulation'] : '' ); ?>><?php _e( 'Automatically after event description', 'timepad' ); ?></option>
                <option value="manually"<?php selected( 'manually', isset( $data['widget_regulation'] ) ? $data['widget_regulation'] : '' ); ?>><?php _e( 'Manually as shortcode/etc', 'timepad' ); ?></option>
            </select>
        </td>
        <td></td>
    </tr>
</table>
<input type="submit" class="button button-primary" name="save_changes" value="<?php _e( 'Save changes', 'timepad' ); ?>" />
<?php else : ?>
</table>
<?php endif;
endif;