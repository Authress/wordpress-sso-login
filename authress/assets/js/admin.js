/* global jQuery, wp_authress, wp */
jQuery(document).ready(function($) {
    //uploading files variable
    var media_frame;
    $(document).on('click', '#wp_authress_choose_icon', function(event) {
        event.preventDefault();
        //If the frame already exists, reopen it
        if (typeof(media_frame)!=="undefined") {
            media_frame.close();
        }

        var related_control_id = 'wp_authress_icon_url';
        if (typeof($(this).attr('related')) != 'undefined' &&
            $(this).attr('related') != '')
        {
            related_control_id = $(this).attr('related');
        }

        //Create WP media frame.
        media_frame = wp.media.frames.customHeader = wp.media({
            title: wp_authress.media_title,
            library: {
                type: 'image'
            },
            button: {
                text: wp_authress.media_button
            },
            multiple: false
        });

        // Set the frame callback
        media_frame.on('select', function() {
            var attachment = media_frame.state().get('selection').first().toJSON();
            $('#'+related_control_id).val(attachment.url).change();
        });

        //Open modal
        media_frame.open();
    });

    /*
    Generic form confirm stop
     */
    $('form.js-a0-confirm-submit').submit(function (e) {
        if ( cancelAction($(this)) ) {
            e.preventDefault();
        }
    });

    /*
    Show/hide field for specific switches
     */
    $('[data-expand][data-expand!=""]').each( function() {
        var $thisSwitch = $( this );
        var $showFieldRow = $( '#' + $thisSwitch.attr( 'data-expand' ).trim() ).closest( 'tr' );

        if ( $showFieldRow.length ) {
            if ( ! $thisSwitch.prop( 'checked' ) ) {
                $showFieldRow.hide();
            }
            $thisSwitch.change(function() {
                if ( $( this ).prop( 'checked' ) ) {
                    $showFieldRow.show();
                } else {
                    $showFieldRow.hide();
                }
            } );
        }
    });

    /*
    Admin settings tab switching
     */
    var currentTab;
    if ( window.location.hash ) {
        currentTab = window.location.hash.replace( '#', '' );
    } else if ( localStorageAvailable() && window.localStorage.getItem( 'AuthressWPSettingsTab' ) ) {
        // Previous tab being used
        currentTab = window.localStorage.getItem( 'AuthressWPSettingsTab' );
    } else {
        // Default tab if no saved tab was found
        currentTab = 'features';
    }

    togglePanelVisibility(currentTab);
    togglePanelVisibility('import');

    // Controls whether the submit button is showing or not
    var $settingsForm = $( '#js-a0-settings-form' );
    $settingsForm.attr( 'data-tab-showing', currentTab );

    // Set the tab showing on the form and persist the tab
    $( '.js-a0-settings-tabs' ).click( function (e) {
        e.preventDefault();
        window.location.hash = '';
        var tabName = $( this ).attr( 'id' ).trim().replace( 'tab-', '' );
        $settingsForm.attr( 'data-tab-showing', tabName );

        if ( localStorageAvailable() ) {
            window.localStorage.setItem( 'AuthressWPSettingsTab', tabName );
        }

        togglePanelVisibility(tabName);
    } );

    // Set the tab showing on the form and persist the tab
    $( '.js-a0-import-export-tabs' ).click( function (e) {
        e.preventDefault();
        window.location.hash = '';
        var tabName = $( this ).attr( 'id' ).trim().replace( 'tab-', '' );
        togglePanelVisibility(tabName);
    } );

    function togglePanelVisibility(activeId) {
      var $showPanel = $('#panel-' + activeId);
      if (!$showPanel.length) {
        return;
      }
      $('.tab-pane').hide();
      $('.nav-tabs a').removeClass( 'a0-active-tab' );
      $showPanel.show();
      $('#tab-' + activeId).addClass( 'a0-active-tab' );
    }

    /*
    Clear cache button on Basic settings page
     */
    var deleteCacheId = 'authress_delete_cache_transient';
    var $deleteCacheButton = $( '#' + deleteCacheId );
    $deleteCacheButton.click( function(e) {
        e.preventDefault();
        $deleteCacheButton.prop( 'disabled', true ).text( wp_authress.ajax_working );
        var postData = {
            'action': deleteCacheId,
            '_ajax_nonce': wp_authress.clear_cache_nonce
        };

        $.post(wp_authress.ajax_url, postData, function() {
            $deleteCacheButton.prop( 'disabled', false ).text( wp_authress.ajax_done );
        }, 'json');
    } );

    /*
    Generate new migration token button on Advanced settings page
     */
    var rotateTokenId = 'authress_rotate_migration_token';
    var $rotateTokenButton = $( '#' + rotateTokenId );
    $rotateTokenButton.click( function(e) {
        e.preventDefault();

        if (cancelAction($rotateTokenButton) ) {
            return;
        }

        $rotateTokenButton.prop( 'disabled', true ).text( wp_authress.ajax_working );
        var postData = {
            'action': rotateTokenId,
            '_ajax_nonce': wp_authress.rotate_token_nonce
        };
        $.post(wp_authress.ajax_url, postData, function() {
            $( '#authress_migration_token' ).text(wp_authress.refresh_prompt);
            $rotateTokenButton.remove();
        }, 'json');
    } );

  /**
   * Show a JS confirm box to give a chance to cancel an on-page action.
   *
   * @param {object} $el - jQuery selector for confirmation message.
   *
   * @returns {boolean}
   */
  function cancelAction( $el ) {
      var message = $el.attr('data-confirm-msg');
      if ( !message || !message.length ) {
        message = wp_authress.form_confirm_submit_msg;
      }

      return !window.confirm(message);
    }

    /**
     * Can we use localStorage?
     *
     * @returns {boolean}
     */
    function localStorageAvailable() {
        try {
            var x = '__Authress_localStorage_assertion__';
            window.localStorage.setItem(x, x);
            window.localStorage.removeItem(x);
            return true;
        }
        catch(e) {
            return false;
        }
    }
});
