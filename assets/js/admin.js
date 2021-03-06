/**
 * Quotest plugin Saving process
 */
jQuery( document ).ready( function () {

    jQuery('.qtp_author').autocomplete({
        source: qtplugin_php_vars.authors
    });

    jQuery( document ).on( 'submit', '#qtplugin-admin-form', function ( e ) {

        e.preventDefault();

        // We inject some extra fields required for the security
        jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
        jQuery(this).append('<input type="hidden" name="security" value="'+ qtplugin_php_vars._nonce +'" />');

        // We make our call
        jQuery.ajax( {
            url: qtplugin_php_vars.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function ( response ) {
                alert(response);
                location.reload();
            }
        } );

    } );

} );