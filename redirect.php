<script type="text/javascript" src="../../../wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="assets/js/admin/jquery.cookie.js"></script>
<script type="text/javascript">
    if ( jQuery.cookie( 'timepad_site_url' ) != null ) {
        
        var getParameterByName = function(name) {
            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search);
            return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        };
    
        var explode = function( delimiter, string ) {
                var emptyArray = { 0: '' };

                if ( arguments.length != 2
                        || typeof arguments[0] == 'undefined'
                        || typeof arguments[1] == 'undefined' )
                {
                        return null;
                }

                if ( delimiter === ''
                        || delimiter === false
                        || delimiter === null )
                {
                        return false;
                }

                if ( typeof delimiter == 'function'
                        || typeof delimiter == 'object'
                        || typeof string == 'function'
                        || typeof string == 'object' )
                {
                        return emptyArray;
                }

                if ( delimiter === true ) {
                        delimiter = '1';
                }

                return string.toString().split ( delimiter.toString() );
        };

        var _cookie_var = explode( '=', location.href ),
        _error_url = '';
        if ( _cookie_var.length === 2 ) {
            if ( _cookie_var[1] !== '' ) {
                jQuery.cookie( 'timepad_token', _cookie_var[1], {
                    expires: 5, path: '/'
                } );
            } else jQuery.removeCookie('timepad_token');
        } else {
            jQuery.removeCookie('timepad_token');
            if ( _cookie_var.length > 2 ) {
                var _error = getParameterByName('error'),
                        _error_description = getParameterByName('error_description');
                if ( _error ) {
                    _error_url = '&timepad_error=' + _error_description;
                }
            }
        }

        var _url_to_go = jQuery.cookie( 'timepad_site_url' ) + '/wp-admin/edit.php?post_type=timepad-events&page=timepad-events-options' + _error_url;
        location.href = _url_to_go;
        
    }
</script>