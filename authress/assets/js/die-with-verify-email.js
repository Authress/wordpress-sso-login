/* globals jQuery, alert, WPAuthressEmailVerification */

jQuery( document ).ready( function ($) {
    'use strict';

    var $resendLink = $( '#js-a0-resend-verification' );

    $resendLink.click( function () {

        var postData = {
            action: 'resend_verification_email',
            _ajax_nonce: WPAuthressEmailVerification.nonce,
            sub: WPAuthressEmailVerification.sub
        };
        var errorMsg = WPAuthressEmailVerification.e_msg;

        $.post( WPAuthressEmailVerification.ajaxUrl, postData )
            .done( function( response ) {
                if ( response.success ) {
                    $resendLink.after( WPAuthressEmailVerification.s_msg );
                    $resendLink.remove();
                } else {
                    if ( response.data && response.data.error ) {
                        errorMsg = response.data.error;
                    }
                    alert( errorMsg );
                }
            } )
            .fail( function() {
                alert( errorMsg );
            } );
    } );
} );