/* globals console, jQuery, Cookies, wpAuthressLockGlobal, wpAuthressLockGlobalFields, AuthressLock, AuthressLockPasswordless, authress */
jQuery(document).ready(function ($) {
    'use strict';

    var opts = wpAuthressLockGlobal;
    var loginForm = $( '#' + opts.loginFormId );

    // Missing critical Authress settings.
    if ( ! opts.ready ) {
        resetWpLoginForm();
        console.error( opts.i18n.notReadyText );
        return;
    }

    // Missing the Lock container.
    if ( ! loginForm.length ) {
        resetWpLoginForm();
        console.error( opts.i18n.cannotFindNodeText + '"' + opts.loginFormId + '"' );
        return;
    }

    /**
     * Set the nonce cookie for verification during callback.
     *
     * @param val string - Value for the nonce cookie.
     */
    function setNonceCookie(val) {
        Cookies.set( opts.nonceCookieName, val );
    }

    /**
     * Show the WordPress login form.
     */
    function resetWpLoginForm() {
        $( '#form-signin-wrapper' ).hide();
        $( '#loginform' ).show();
        $( '#login' ).find( 'h1' ).show();
    }
});
