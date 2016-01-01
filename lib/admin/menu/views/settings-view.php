<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$maybe_class = 'TimepadEvents_Admin_Settings_' . ucfirst( $active_tab );
if ( class_exists( $maybe_class ) ) : ?>
    <div class="wrap timepadevents-settings">
        <?php if ( !empty( $logo ) ) : ?>
            <div class="timepad-admin-settings-logo"><img src="<?php echo $logo; ?>" /></div>
        <?php endif; ?>
        <?php if ( count( $tabs ) > 1 ) : ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach ( $tabs as $tab_id => $tab_array ) : ?>
                <a href="<?php echo add_query_arg( array( 'tab' => $tab_array['handler']), remove_query_arg( array( 'settings-updated', 'handler', 'action', '_wpnonce' ) ) ); ?>" class="nav-tab <?php echo $active_tab == $tab_array['handler'] ? 'nav-tab-active' : ''; ?>"><?php echo $tab_array['title']; ?></a>
            <?php endforeach; ?>
        </h2>
        <?php else : ?>
        <h1 class="timepadevents-title"><?php _e( 'Timepad Settings', 'timepad' ); ?></h1>
        <?php endif; ?>
        <div id="tab_container">
            <form method="post" action="<?php echo $maybe_class::getInstance()->action; ?>">
                <?php
                if ( !empty( $tabs ) && is_array( $tabs ) ) :
                    foreach ( $tabs as $tab_array ) :
                        $submit_button = true;
                        if ( $active_tab == $tab_array['handler'] ) {
                            do_action( 'timepadevents_settings_prepend_' . $tab_array['handler'] );

                            if ( method_exists( $tab_array['class'], 'display' ) ) {
                                $submit_button = false;
                                $tab_array['class']::getInstance()->display();
                            } else {
                                settings_fields('timepadevents_settings');
                                do_settings_sections('timepadevents_settings_' . $tab_array['handler']);
                            }

                            do_action( 'timepadevents_settings_append_' . $tab_array['handler'] );
                            echo '<input type="hidden" name="active_settings_tab" value="' . esc_attr( $active_tab ) . '" />';
                            echo '<input type="hidden" name="redirect_url" value="' . esc_attr( "http" . ( ( $_SERVER['SERVER_PORT'] == 443 ) ? "s://" : "://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) . '" />';

                            if ( $submit_button ) submit_button();
                        }
                    endforeach;
                endif;
                ?>
                <input type="hidden" name="timepad_post_type" value="<?php echo esc_attr( TIMEPADEVENTS_POST_TYPE ); ?>" />
                <?php wp_nonce_field( 'timepadevents-settings' ); ?>
            </form>
        </div>
    </div>
    <?php
endif;
?>