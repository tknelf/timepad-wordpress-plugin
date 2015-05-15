jQuery(document).ready(function() {
    'use strict';
    
    if ( !jQuery.cookie( 'timepad_site_url' ) ) {
        jQuery.cookie( 'timepad_site_url', timepad._site_url, { path: '/' } );
    }
});