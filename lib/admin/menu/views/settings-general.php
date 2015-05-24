<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !empty( $error_response ) ) : ?><div class="updated"><p><?php echo $error_response; ?></p></div><?php endif; ?>
<div style="width:60%">
    <p><?php _e( 'If you\'re already using TimePad, simply sync your events with this website. To do this, simply click the &laquo;prepare the system for TimePad events organization&raquo;', 'timepad' ); ?></p>
    <a class="button button-primary" href="<?php echo $config['authorize_uri']; ?>?client_id=<?php echo $config['client_id']; ?>&redirect_uri=<?php echo urlencode( $config['redirect_uri'] . '/?redirect=' . TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'redirect.php' ); ?>&scope=add_organizations&response_type=token"><?php _e( 'Prepare the system for TimePad events organization', 'timepad' ); ?></a>
</div>