/**
 * AI Tools for WP - Admin Settings JavaScript
 */

(function($) {
    'use strict';

    // Check jQuery availability
    if ( typeof $ === 'undefined' || typeof $ !== 'function' ) {
        console.error( 'AI Tools for WP: jQuery is required but not available.' );
        return;
    }

    // Check required global data
    if ( typeof aitwpAdmin === 'undefined' ) {
        console.error( 'AI Tools for WP: Admin configuration not loaded.' );
        return;
    }

    // Cache DOM elements
    var $profilesList = $('#aitwp-profiles-list');
    var $profileForm = $('#aitwp-profile-form');
    var $audiencesList = $('#aitwp-audiences-list');
    var $audienceForm = $('#aitwp-audience-form');

    /**
     * Voice Profiles Management
     */
    var VoiceProfiles = {
        init: function() {
            $profileForm.on('submit', this.save.bind(this));
            $profilesList.on('click', '.aitwp-edit-profile', this.edit.bind(this));
            $profilesList.on('click', '.aitwp-delete-profile', this.delete.bind(this));
            $('#aitwp-cancel-profile').on('click', this.resetForm.bind(this));
        },

        save: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var data = {
                action: 'aitwp_save_voice_profile',
                nonce: aitwpAdmin.nonce,
                id: $('#aitwp-profile-id').val(),
                name: $('#aitwp-profile-name').val(),
                content: $('#aitwp-profile-content').val()
            };

            $form.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, data, function(response) {
                $form.removeClass('aitwp-loading');

                if (response.success) {
                    VoiceProfiles.updateList(response.data.profiles);
                    VoiceProfiles.resetForm();
                    VoiceProfiles.showMessage('success', aitwpAdmin.strings.saveSuccess);
                } else {
                    VoiceProfiles.showMessage('error', response.data || aitwpAdmin.strings.saveError);
                }
            }).fail(function() {
                $form.removeClass('aitwp-loading');
                VoiceProfiles.showMessage('error', aitwpAdmin.strings.saveError);
            });
        },

        edit: function(e) {
            var $item = $(e.target).closest('.aitwp-profile-item');
            var id = $item.data('id');
            var name = $item.find('.aitwp-profile-name').text();
            var content = $item.data('content') || '';

            // Get full content via AJAX or from data attribute
            // For now, we'll need to store full content differently
            // This is a simplified version

            $('#aitwp-profile-id').val(id);
            $('#aitwp-profile-name').val(name);
            $('#aitwp-profile-content').val(content);
            $('#aitwp-form-title').text('Edit Voice Profile');
            $('#aitwp-cancel-profile').show();

            $('html, body').animate({
                scrollTop: $('#aitwp-form-title').offset().top - 50
            }, 300);
        },

        delete: function(e) {
            if (!confirm(aitwpAdmin.strings.confirmDelete)) {
                return;
            }

            var $item = $(e.target).closest('.aitwp-profile-item');
            var id = $item.data('id');

            $item.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, {
                action: 'aitwp_delete_voice_profile',
                nonce: aitwpAdmin.nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    VoiceProfiles.updateList(response.data.profiles);
                    VoiceProfiles.showMessage('success', aitwpAdmin.strings.deleteSuccess);
                } else {
                    $item.removeClass('aitwp-loading');
                    VoiceProfiles.showMessage('error', response.data || aitwpAdmin.strings.deleteError);
                }
            }).fail(function() {
                $item.removeClass('aitwp-loading');
                VoiceProfiles.showMessage('error', aitwpAdmin.strings.deleteError);
            });
        },

        resetForm: function() {
            $('#aitwp-profile-id').val('');
            $('#aitwp-profile-name').val('');
            $('#aitwp-profile-content').val('');
            $('#aitwp-form-title').text('Add New Voice Profile');
            $('#aitwp-cancel-profile').hide();
        },

        updateList: function(profiles) {
            var html = '';

            if ($.isEmptyObject(profiles)) {
                html = '<p class="aitwp-no-items">No voice profiles yet. Create your first one below.</p>';
            } else {
                $.each(profiles, function(id, profile) {
                    var preview = profile.content ? profile.content.substring(0, 150) + '...' : '';
                    html += '<div class="aitwp-profile-item" data-id="' + profile.id + '" data-content="' + $('<div>').text(profile.content || '').html() + '">';
                    html += '<div class="aitwp-profile-header">';
                    html += '<strong class="aitwp-profile-name">' + $('<div>').text(profile.name).html() + '</strong>';
                    html += '<div class="aitwp-profile-actions">';
                    html += '<button type="button" class="button aitwp-edit-profile">Edit</button>';
                    html += '<button type="button" class="button aitwp-delete-profile">Delete</button>';
                    html += '</div></div>';
                    html += '<div class="aitwp-profile-preview">' + $('<div>').text(preview).html() + '</div>';
                    html += '</div>';
                });
            }

            $profilesList.html(html);
        },

        showMessage: function(type, message) {
            var $msg = $('<div class="aitwp-message aitwp-message-' + type + '">' + message + '</div>');
            $profileForm.before($msg);
            setTimeout(function() {
                $msg.fadeOut(function() { $(this).remove(); });
            }, 3000);
        }
    };

    /**
     * Audiences Management
     */
    var Audiences = {
        init: function() {
            $audienceForm.on('submit', this.save.bind(this));
            $audiencesList.on('click', '.aitwp-edit-audience', this.edit.bind(this));
            $audiencesList.on('click', '.aitwp-delete-audience', this.delete.bind(this));
            $('#aitwp-cancel-audience').on('click', this.resetForm.bind(this));
        },

        save: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var data = {
                action: 'aitwp_save_audience',
                nonce: aitwpAdmin.nonce,
                id: $('#aitwp-audience-id').val(),
                name: $('#aitwp-audience-name').val(),
                description: $('#aitwp-audience-description').val()
            };

            $form.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, data, function(response) {
                $form.removeClass('aitwp-loading');

                if (response.success) {
                    Audiences.updateList(response.data.audiences);
                    Audiences.resetForm();
                    Audiences.showMessage('success', aitwpAdmin.strings.saveSuccess);
                } else {
                    Audiences.showMessage('error', response.data || aitwpAdmin.strings.saveError);
                }
            }).fail(function() {
                $form.removeClass('aitwp-loading');
                Audiences.showMessage('error', aitwpAdmin.strings.saveError);
            });
        },

        edit: function(e) {
            var $item = $(e.target).closest('.aitwp-audience-item');
            var id = $item.data('id');
            var name = $item.find('.aitwp-audience-name').text();
            var description = $item.find('.aitwp-audience-description').text();

            $('#aitwp-audience-id').val(id);
            $('#aitwp-audience-name').val(name);
            $('#aitwp-audience-description').val(description);
            $('#aitwp-audience-form-title').text('Edit Audience');
            $('#aitwp-cancel-audience').show();

            $('html, body').animate({
                scrollTop: $('#aitwp-audience-form-title').offset().top - 50
            }, 300);
        },

        delete: function(e) {
            if (!confirm(aitwpAdmin.strings.confirmDelete)) {
                return;
            }

            var $item = $(e.target).closest('.aitwp-audience-item');
            var id = $item.data('id');

            $item.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, {
                action: 'aitwp_delete_audience',
                nonce: aitwpAdmin.nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    Audiences.updateList(response.data.audiences);
                    Audiences.showMessage('success', aitwpAdmin.strings.deleteSuccess);
                } else {
                    $item.removeClass('aitwp-loading');
                    Audiences.showMessage('error', response.data || aitwpAdmin.strings.deleteError);
                }
            }).fail(function() {
                $item.removeClass('aitwp-loading');
                Audiences.showMessage('error', aitwpAdmin.strings.deleteError);
            });
        },

        resetForm: function() {
            $('#aitwp-audience-id').val('');
            $('#aitwp-audience-name').val('');
            $('#aitwp-audience-description').val('');
            $('#aitwp-audience-form-title').text('Add New Audience');
            $('#aitwp-cancel-audience').hide();
        },

        updateList: function(audiences) {
            var html = '';

            if ($.isEmptyObject(audiences)) {
                html = '<p class="aitwp-no-items">No audiences defined yet. Create your first one below.</p>';
            } else {
                $.each(audiences, function(id, audience) {
                    html += '<div class="aitwp-audience-item" data-id="' + audience.id + '">';
                    html += '<div class="aitwp-audience-header">';
                    html += '<strong class="aitwp-audience-name">' + $('<div>').text(audience.name).html() + '</strong>';
                    html += '<div class="aitwp-audience-actions">';
                    html += '<button type="button" class="button aitwp-edit-audience">Edit</button>';
                    html += '<button type="button" class="button aitwp-delete-audience">Delete</button>';
                    html += '</div></div>';
                    html += '<div class="aitwp-audience-description">' + $('<div>').text(audience.description || '').html() + '</div>';
                    html += '</div>';
                });
            }

            $audiencesList.html(html);
        },

        showMessage: function(type, message) {
            var $msg = $('<div class="aitwp-message aitwp-message-' + type + '">' + message + '</div>');
            $audienceForm.before($msg);
            setTimeout(function() {
                $msg.fadeOut(function() { $(this).remove(); });
            }, 3000);
        }
    };

    // Initialize on document ready
    $(function() {
        if ($profilesList.length) {
            VoiceProfiles.init();
        }
        if ($audiencesList.length) {
            Audiences.init();
        }
    });

})(jQuery);
