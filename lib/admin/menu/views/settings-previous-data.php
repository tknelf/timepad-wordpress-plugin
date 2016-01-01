<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<table class="form-table">
    <tr>
        <th scope="row"><label for="timepad_previous_events"><?php _e( 'Previous events', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <select name="timepad_previous_events" id="timepad_previous_events">
                <option value="previous_ignore"<?php selected( 'previous_ignore', isset( $data['previous_events'] ) ? $data['previous_events'] : '' ); ?>><?php _e( 'Ignore previous events', 'timepad' ); ?></option>
                <option value="previous_accept"<?php selected( 'previous_accept', isset( $data['previous_events'] ) ? $data['previous_events'] : '' ); ?>><?php _e( 'Import previous events', 'timepad' ); ?></option>
            </select>
        </td>
        <td></td>
    </tr>
    <tr>
        <th scope="row"><label for="timepad_event_date"><?php _e( 'Event date and time', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <select name="timepad_event_date" id="timepad_event_date">
                <option value="natural"<?php selected( 'natural', isset( $data['event_date'] ) ? $data['event_date'] : '' ); ?>><?php _e( 'Natural TimePad event date and time', 'timepad' ); ?></option>
                <option value="current"<?php selected( 'current', isset( $data['event_date'] ) ? $data['event_date'] : '' ); ?>><?php _e( 'Current time', 'timepad' ); ?></option>
            </select>
        </td>
        <td></td>
    </tr>
</table>
<input type="submit" class="button button-primary" name="save_changes" value="<?php _e( 'Save changes', 'timepad' ); ?>" />