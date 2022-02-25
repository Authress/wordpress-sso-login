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

    // Set state and nonce cookies to verify during callback.
    setStateCookie(opts.settings.auth.params.state);
    if ( opts.settings.auth.params.nonce ) {
        setNonceCookie(opts.settings.auth.params.nonce);
    }

    // Look for additional fields to display.
    if ( typeof wpAuthressLockGlobalFields === 'object' ) {
        opts.settings.additionalSignUpFields = wpAuthressLockGlobalFields;
    }

    // Set Lock to standard or Passwordless.
    var Lock = opts.usePasswordless ?
        new AuthressLockPasswordless( opts.clientId, opts.domain, opts.settings ) :
        new AuthressLock( opts.clientId, opts.domain, opts.settings );

    // Check if we're showing as a modal (used in shortcodes and widgets).
    if ( opts.showAsModal ) {
        $( '<button>' )
            .text( opts.i18n.modalButtonText )
            .attr( 'id', 'a0LoginButton' )
            .insertAfter( loginForm )
            .click(function () { Lock.show(); });
    } else {
        Lock.show();
    }

    /**
     * Set the state cookie for verification during callback.
     *
     * @param val string - Value for the state cookie.
     */
    function setStateCookie(val) {
        Cookies.set( opts.stateCookieName, val );
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
