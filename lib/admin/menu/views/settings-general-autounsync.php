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
    <tr class="timepad_unsync_tr"<?php echo ( !isset( $data['autounsync'] ) || empty( $data['autounsync'] ) ) ? ' style="display:none"' : ''; ?>>
        <th scope="row"><label for="timepad_autounsync_to_status"><?php _e( 'To status', 'timepad' ) ?>:</label></th>
        <td colspan="2">
            <select name="timepad_autounsync_to_status" id="timepad_autounsync_to_status">
                <option value="publish"<?php selected( 'publish', isset( $data['autounsync_to_status'] ) ? $data['autounsync_to_status'] : '' ); ?>><?php _e( 'Published' ); ?></option>
                <option value="draft"<?php selected( 'draft', isset( $data['autounsync_to_status'] ) ? $data['autounsync_to_status'] : '' ); ?>><?php _e( 'Draft' ); ?></option>
            </select>
        </td>
    </tr>