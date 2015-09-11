jQuery(document).ready(function() {
    'use strict';
    
    if ( !jQuery.cookie( 'timepad_admin_url' ) ) {
        jQuery.cookie( 'timepad_admin_url', timepad._admin_url, { path: '/' } );
    }
    
    var _redirect_url = 'tools.php?page=timepad-events-redirect',
        _tools_a_elem = jQuery('a[href="' + _redirect_url + '"]');
    if ( _tools_a_elem.length ) {
        _tools_a_elem.parent('li').hide();
    }
});