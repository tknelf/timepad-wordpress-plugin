<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<table class="form-table">
    <tr>
        <td colspan="3" width="60%">
            <h3 class="title"><?php _e( 'Event/post widget output regulation', 'timepad' ); ?></h3>
            <p><?php _e( 'You can choose how to display the TimePad widget at the event/post or display the one singly', 'timepad' ); ?></p>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="4">
            <div class="card">
                <h2 class="title"><?php _e( 'Widget settings legend', 'timepad' ); ?></h2>
                <ol>
                    <li>
                        <h4><?php _e( 'Shortcode code example', 'timepad' ); ?>:</h4>
                        <code>[timepadregistration eventid="xxxxxx"]</code>
                    </li>
                    <li>
                        <h4><?php _e( 'PHP shortcode code output example', 'timepad' ); ?>:</h4>
                        <code>&lt;?php echo do_shortcode( '[timepadregistration eventid="xxxxxx"]' ); ?&gt;</code>
                    </li>
                </ol>
                <p><strong>xxxxxx</strong> &mdash; <?php _e( 'TimePad internal event id', 'timepad' ); ?></p>
            </div>
        </td>
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
    <tr>
        <th scope="row"><label for="timepad_widget_customization_id"><?php _e( 'Customization ID', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <input type="text" id="timepad_widget_customization_id" name="timepad_widget_customization_id" value="<?php echo ( isset( $data['widget_customization_id'] ) && !empty( $data['widget_customization_id'] ) ) ? intval( $data['widget_customization_id'] ) : ''; ?>" />
        </td>
        <td></td>
    </tr>
</table>
<input type="submit" class="button button-primary" name="save_changes" value="<?php _e( 'Save changes', 'timepad' ); ?>" />