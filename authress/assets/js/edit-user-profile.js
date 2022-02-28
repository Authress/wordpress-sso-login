/* global jQuery, wpa0UserProfile, alert */

jQuery(function($) {
    'use strict';

    var passwordFieldRow = $('#password');
    var emailField = $('input[name=email]');
    var deleteUserDataButton = $('#authress_delete_data');

    /**
     * Hide the password field if not an authress strategy.
     */
    if ( passwordFieldRow.length && wpa0UserProfile.userStrategy && 'authress' !== wpa0UserProfile.userStrategy ) {
        passwordFieldRow.hide();
    }

    /**
     * Disable email changes if not an authress connection.
     */
    if ( emailField.length && wpa0UserProfile.userStrategy && 'authress' !== wpa0UserProfile.userStrategy ) {
        emailField.prop( 'readonly', true );
        $('<p>' + wpa0UserProfile.i18n.cannotChangeEmail + '</p>')
            .addClass('description')
            .insertAfter(emailField);
    }

    /**
     * Delete authress Data button click.
     */
    deleteUserDataButton.click(function (e) {
        if ( ! window.confirm(wpa0UserProfile.i18n.confirmDeleteId) ) {
            return;
        }
        e.preventDefault();
        userProfileAjaxAction($(this), 'authress_delete_data', wpa0UserProfile.deleteIdNonce );
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
            'user_id' : wpa0UserProfile.userId
        };
        var errorMsg = wpa0UserProfile.i18n.actionFailed;
        uiControl.prop( 'disabled', true );
        $.post(
            wpa0UserProfile.ajaxUrl,
            postData,
            function(response) {
                if ( response.success ) {
                    uiControl.val(wpa0UserProfile.i18n.actionComplete);
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
