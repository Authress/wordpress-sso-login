/* global jQuery, authress_sso_loginUserProfile, alert */

// Used to update the user profile screen in the admin console adding a connection to delete the Authress data and add a button to jump to Authress

jQuery(function($) {
    'use strict';

    var passwordFieldRow = $('#password');
    var emailField = $('input[name=email]');
    var deleteUserDataButton = $('#authress_delete_data');

    var userLoginDescription = $('#user_login + span.description');
    if (userLoginDescription.length) {
        userLoginDescription.hide();
    }

    /**
     * Hide the password field if not an authress strategy.
     */
    if ( passwordFieldRow.length && authress_sso_loginUserProfile.userStrategy && 'authress' === authress_sso_loginUserProfile.userStrategy ) {
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
    if (authress_sso_loginUserProfile.userStrategy && 'authress' === authress_sso_loginUserProfile.userStrategy ) {
        if (emailField.length) {
            emailField.prop( 'readonly', true );
            $('<p>' + authress_sso_loginUserProfile.i18n.cannotChangeEmail + '</p>')
                .addClass('description')
                .insertAfter(emailField);
        }

        var emailDescription = $('#email-description');
        if (emailDescription.length) {
            emailDescription.hide();
        }
    }

    /**
     * Delete authress Data button click.
     */
    deleteUserDataButton.click(function (e) {
        if ( ! window.confirm(authress_sso_loginUserProfile.i18n.confirmDeleteId) ) {
            return;
        }
        e.preventDefault();
        userProfileAjaxAction($(this), 'authress_delete_data', authress_sso_loginUserProfile.deleteIdNonce );
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
            'user_id' : authress_sso_loginUserProfile.userId
        };
        var errorMsg = authress_sso_loginUserProfile.i18n.actionFailed;
        uiControl.prop( 'disabled', true );
        $.post(
            authress_sso_loginUserProfile.ajaxUrl,
            postData,
            function(response) {
                if ( response.success ) {
                    uiControl.val(authress_sso_loginUserProfile.i18n.actionComplete);
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
