/* global jQuery, wp_authressUserProfile, alert */

// Used to update the user profile screen in the admin console adding a connection to delete the Authress data and add a button to jump to Authress

jQuery(function($) {
    'use strict';

    var passwordFieldRow = $('#password');
    var emailField = $('input[name=email]');
    var deleteUserDataButton = $('#authress_delete_data');

    /**
     * Hide the password field if not an authress strategy.
     */
    if ( passwordFieldRow.length && wp_authressUserProfile.userStrategy && 'authress' === wp_authressUserProfile.userStrategy ) {
        passwordFieldRow.hide();
        var resetPasswordFieldRow = $('.user-generate-reset-link-wrap');
        if (resetPasswordFieldRow.length) {
            resetPasswordFieldRow.hide();
        }

        $('<p>This account was created through an SSO federated login provider.</p>')
            .addClass('description')
            .insertAfter(resetPasswordFieldRow);

            
        var applicationPasswords = $('.application-passwords');
        if (applicationPasswords.length) {
            applicationPasswords.hide();
        }
    }

    /**
     * Disable email changes if not an authress connection.
     */
    if ( emailField.length && wp_authressUserProfile.userStrategy && 'authress' === wp_authressUserProfile.userStrategy ) {
        emailField.prop( 'readonly', true );
        $('<p>' + wp_authressUserProfile.i18n.cannotChangeEmail + '</p>')
            .addClass('description')
            .insertAfter(emailField);
    }

    /**
     * Delete authress Data button click.
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
