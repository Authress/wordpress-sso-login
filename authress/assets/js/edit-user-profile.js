/* global jQuery, wp_authressUserProfile, alert */

jQuery(function($) {
    'use strict';

    var passwordFieldRow = $('#password');
    var emailField = $('input[name=email]');
    var deleteUserDataButton = $('#authress_delete_data');

    /**
     * Hide the password field if not an Authress strategy.
     */
    if ( passwordFieldRow.length && wp_authressUserProfile.userStrategy && 'authress' !== wp_authressUserProfile.userStrategy ) {
        passwordFieldRow.hide();
    }

    /**
     * Disable email changes if not an Authress connection.
     */
    if ( emailField.length && wp_authressUserProfile.userStrategy && 'authress' !== wp_authressUserProfile.userStrategy ) {
        emailField.prop( 'readonly', true );
        $('<p>' + wp_authressUserProfile.i18n.cannotChangeEmail + '</p>')
            .addClass('description')
            .insertAfter(emailField);
    }

    /**
     * Delete Authress Data button click.
     */
    deleteUserDataButton.click(function (e) {
        if ( ! window.confirm(wp_authressUserProfile.i18n.confirmDeleteId) ) {
            return;
        }
        e.preventDefault();
        userProfileAjaxAction($(this), 'authress_delete_data', wp_authressUserProfile.deleteIdNonce );
    });

    /**
     * Perform a generic user profile AJAX call.
     *
     * @param uiControl
     * @param action
     * @param nonce
     */
    function userProfileAjaxAction( uiControl, action, nonce ) {
        var postData = {
            'action' : action,
            '_ajax_nonce' : nonce,
            'user_id' : wp_authressUserProfile.userId
        };
        var errorMsg = wp_authressUserProfile.i18n.actionFailed;
        uiControl.prop( 'disabled', true );
        $.post(
            wp_authressUserProfile.ajaxUrl,
            postData,
            function(response) {
                if ( response.success ) {
                    uiControl.val(wp_authressUserProfile.i18n.actionComplete);
                } else {
                    if (response.data && response.data.error) {
                        errorMsg = response.data.error;
                    }
                    alert(errorMsg);
                    uiControl.prop( 'disabled', false );
                }
            }
        );
    }
});
