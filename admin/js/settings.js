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
     * Collapsible Sections Handler
     */
    var CollapsibleSections = {
        init: function() {
            // Toggle section on header click
            $(document).on('click', '.aitwp-section-header.aitwp-collapsible', function() {
                var $section = $(this).closest('.aitwp-section');
                $section.toggleClass('aitwp-section-open');

                // Toggle icon
                var $icon = $(this).find('.aitwp-toggle-icon');
                if ($section.hasClass('aitwp-section-open')) {
                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                } else {
                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                }
            });
        },

        expandAll: function($container) {
            $container.find('.aitwp-section').addClass('aitwp-section-open');
            $container.find('.aitwp-toggle-icon')
                .removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        },

        collapseAll: function($container) {
            $container.find('.aitwp-section:not(:first-child)').removeClass('aitwp-section-open');
            $container.find('.aitwp-section:not(:first-child) .aitwp-toggle-icon')
                .removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        }
    };

    /**
     * Helper to convert array to newline-separated text
     */
    function arrayToText(arr) {
        if (!arr || !Array.isArray(arr)) return '';
        return arr.join('\n');
    }

    /**
     * Helper to get preview text (first N words)
     */
    function getPreview(text, maxWords) {
        if (!text) return '';
        var words = text.split(/\s+/).slice(0, maxWords || 30);
        return words.join(' ') + (words.length >= maxWords ? '...' : '');
    }

    /**
     * Voice Profiles Management
     */
    var VoiceProfiles = {
        // Store full profile data for editing
        profilesData: {},

        init: function() {
            $profileForm.on('submit', this.save.bind(this));
            $profilesList.on('click', '.aitwp-edit-profile', this.edit.bind(this));
            $profilesList.on('click', '.aitwp-delete-profile', this.delete.bind(this));
            $('#aitwp-cancel-profile').on('click', this.resetForm.bind(this));

            // Store initial profiles data from list items
            this.cacheProfilesData();
        },

        cacheProfilesData: function() {
            // Load profiles data via AJAX for editing
            $.get(aitwpAdmin.ajaxUrl, {
                action: 'aitwp_get_profiles_data',
                nonce: aitwpAdmin.nonce
            }, function(response) {
                if (response.success && response.data) {
                    VoiceProfiles.profilesData = response.data;
                }
            });
        },

        save: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var data = {
                action: 'aitwp_save_voice_profile',
                nonce: aitwpAdmin.nonce,
                id: $('#aitwp-profile-id').val(),
                name: $('#aitwp-profile-name').val(),

                // Voice Identity
                voice_identity: $('#aitwp-voice-identity').val(),

                // Tone & Energy
                tone_energy_level: $('#aitwp-tone-energy-level').val(),
                tone_humor_style: $('#aitwp-tone-humor-style').val(),
                tone_emotional_range: $('#aitwp-tone-emotional-range').val(),

                // Language Patterns
                lang_sentence_structure: $('#aitwp-lang-sentence-structure').val(),
                lang_vocabulary: $('#aitwp-lang-vocabulary').val(),
                lang_contractions: $('#aitwp-lang-contractions').val(),
                lang_punctuation: $('#aitwp-lang-punctuation').val(),

                // Additional Patterns
                pattern_paragraph_structure: $('#aitwp-pattern-paragraph-structure').val(),
                pattern_opening_moves: $('#aitwp-pattern-opening-moves').val(),
                pattern_closing_moves: $('#aitwp-pattern-closing-moves').val(),
                pattern_transitions: $('#aitwp-pattern-transitions').val(),
                pattern_examples_evidence: $('#aitwp-pattern-examples-evidence').val(),
                pattern_distinctive: $('#aitwp-pattern-distinctive').val(),

                // Philosophy
                content_philosophy: $('#aitwp-content-philosophy').val(),
                credibility_authority: $('#aitwp-credibility-authority').val(),
                audience_relationship: $('#aitwp-audience-relationship').val(),
                handling_disagreement: $('#aitwp-handling-disagreement').val(),

                // Platform Adaptation
                platform_twitter: $('#aitwp-platform-twitter').val(),
                platform_linkedin: $('#aitwp-platform-linkedin').val(),
                platform_facebook: $('#aitwp-platform-facebook').val(),
                platform_blog: $('#aitwp-platform-blog').val(),

                // Guardrails
                guardrails_never_words: $('#aitwp-guardrails-never-words').val(),
                guardrails_never_phrases: $('#aitwp-guardrails-never-phrases').val(),
                guardrails_never_patterns: $('#aitwp-guardrails-never-patterns').val(),
                guardrails_always_do: $('#aitwp-guardrails-always-do').val(),

                // Quick Reference
                quick_reference: $('#aitwp-quick-reference').val()
            };

            $form.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, data, function(response) {
                $form.removeClass('aitwp-loading');

                if (response.success) {
                    VoiceProfiles.profilesData = response.data.profiles;
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
            var profile = this.profilesData[id];

            if (!profile) {
                // Fallback: just set basic fields
                $('#aitwp-profile-id').val(id);
                $('#aitwp-profile-name').val($item.find('.aitwp-profile-name').text());
                $('#aitwp-form-title').text('Edit Voice Profile');
                $('#aitwp-cancel-profile').show();
                return;
            }

            // Populate all fields
            $('#aitwp-profile-id').val(id);
            $('#aitwp-profile-name').val(profile.name || '');

            // Voice Identity
            $('#aitwp-voice-identity').val(profile.voice_identity || '');

            // Tone & Energy
            if (profile.tone_energy) {
                $('#aitwp-tone-energy-level').val(profile.tone_energy.energy_level || 'medium');
                $('#aitwp-tone-humor-style').val(profile.tone_energy.humor_style || 'subtle');
                $('#aitwp-tone-emotional-range').val(profile.tone_energy.emotional_range || 'balanced');
            }

            // Language Patterns
            if (profile.language_patterns) {
                $('#aitwp-lang-sentence-structure').val(profile.language_patterns.sentence_structure || '');
                $('#aitwp-lang-vocabulary').val(profile.language_patterns.vocabulary || '');
                $('#aitwp-lang-contractions').val(profile.language_patterns.contractions || '');
                $('#aitwp-lang-punctuation').val(profile.language_patterns.punctuation || '');
            }

            // Additional Patterns
            if (profile.additional_patterns) {
                $('#aitwp-pattern-paragraph-structure').val(profile.additional_patterns.paragraph_structure || '');
                $('#aitwp-pattern-opening-moves').val(profile.additional_patterns.opening_moves || '');
                $('#aitwp-pattern-closing-moves').val(profile.additional_patterns.closing_moves || '');
                $('#aitwp-pattern-transitions').val(profile.additional_patterns.transitions || '');
                $('#aitwp-pattern-examples-evidence').val(profile.additional_patterns.examples_evidence || '');
                $('#aitwp-pattern-distinctive').val(profile.additional_patterns.distinctive || '');
            }

            // Philosophy
            $('#aitwp-content-philosophy').val(profile.content_philosophy || '');
            $('#aitwp-credibility-authority').val(profile.credibility_authority || '');
            $('#aitwp-audience-relationship').val(profile.audience_relationship || '');
            $('#aitwp-handling-disagreement').val(profile.handling_disagreement || '');

            // Platform Adaptation
            if (profile.platform_adaptation) {
                $('#aitwp-platform-twitter').val(profile.platform_adaptation.twitter || '');
                $('#aitwp-platform-linkedin').val(profile.platform_adaptation.linkedin || '');
                $('#aitwp-platform-facebook').val(profile.platform_adaptation.facebook || '');
                $('#aitwp-platform-blog').val(profile.platform_adaptation.blog || '');
            }

            // Guardrails
            if (profile.guardrails) {
                $('#aitwp-guardrails-never-words').val(arrayToText(profile.guardrails.never_words));
                $('#aitwp-guardrails-never-phrases').val(arrayToText(profile.guardrails.never_phrases));
                $('#aitwp-guardrails-never-patterns').val(arrayToText(profile.guardrails.never_patterns));
                $('#aitwp-guardrails-always-do').val(arrayToText(profile.guardrails.always_do));
            }

            // Quick Reference
            $('#aitwp-quick-reference').val(arrayToText(profile.quick_reference));

            // Update UI
            $('#aitwp-form-title').text('Edit Voice Profile');
            $('#aitwp-cancel-profile').show();

            // Expand all sections when editing
            CollapsibleSections.expandAll($profileForm);

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
                    VoiceProfiles.profilesData = response.data.profiles;
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
            // Clear all form fields
            $profileForm.find('input[type="text"], textarea').val('');
            $profileForm.find('select').each(function() {
                $(this).val($(this).find('option:first').val());
            });
            $('#aitwp-profile-id').val('');

            // Reset UI
            $('#aitwp-form-title').text('Add New Voice Profile');
            $('#aitwp-cancel-profile').hide();

            // Collapse sections
            CollapsibleSections.collapseAll($profileForm);
        },

        updateList: function(profiles) {
            var html = '';

            if ($.isEmptyObject(profiles)) {
                html = '<p class="aitwp-no-items">No voice profiles yet. Create your first one below.</p>';
            } else {
                $.each(profiles, function(id, profile) {
                    var previewText = profile.voice_identity || profile.content || '';
                    var preview = getPreview(previewText, 30);

                    html += '<div class="aitwp-profile-item" data-id="' + profile.id + '">';
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
        // Store full audience data for editing
        audiencesData: {},

        init: function() {
            $audienceForm.on('submit', this.save.bind(this));
            $audiencesList.on('click', '.aitwp-edit-audience', this.edit.bind(this));
            $audiencesList.on('click', '.aitwp-delete-audience', this.delete.bind(this));
            $('#aitwp-cancel-audience').on('click', this.resetForm.bind(this));

            // Store initial audiences data
            this.cacheAudiencesData();
        },

        cacheAudiencesData: function() {
            $.get(aitwpAdmin.ajaxUrl, {
                action: 'aitwp_get_audiences_data',
                nonce: aitwpAdmin.nonce
            }, function(response) {
                if (response.success && response.data) {
                    Audiences.audiencesData = response.data;
                }
            });
        },

        save: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var data = {
                action: 'aitwp_save_audience',
                nonce: aitwpAdmin.nonce,
                id: $('#aitwp-audience-id').val(),
                name: $('#aitwp-audience-name').val(),
                definition: $('#aitwp-audience-definition').val(),
                goals: $('#aitwp-audience-goals').val(),
                pains: $('#aitwp-audience-pains').val(),
                hopes_dreams: $('#aitwp-audience-hopes-dreams').val(),
                fears: $('#aitwp-audience-fears').val()
            };

            $form.addClass('aitwp-loading');

            $.post(aitwpAdmin.ajaxUrl, data, function(response) {
                $form.removeClass('aitwp-loading');

                if (response.success) {
                    Audiences.audiencesData = response.data.audiences;
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
            var audience = this.audiencesData[id];

            if (!audience) {
                // Fallback
                $('#aitwp-audience-id').val(id);
                $('#aitwp-audience-name').val($item.find('.aitwp-audience-name').text());
                $('#aitwp-audience-form-title').text('Edit Audience');
                $('#aitwp-cancel-audience').show();
                return;
            }

            // Populate all fields
            $('#aitwp-audience-id').val(id);
            $('#aitwp-audience-name').val(audience.name || '');
            $('#aitwp-audience-definition').val(audience.definition || audience.description || '');
            $('#aitwp-audience-goals').val(arrayToText(audience.goals));
            $('#aitwp-audience-pains').val(arrayToText(audience.pains));
            $('#aitwp-audience-hopes-dreams').val(arrayToText(audience.hopes_dreams));
            $('#aitwp-audience-fears').val(arrayToText(audience.fears));

            // Update UI
            $('#aitwp-audience-form-title').text('Edit Audience');
            $('#aitwp-cancel-audience').show();

            // Expand all sections when editing
            CollapsibleSections.expandAll($audienceForm);

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
                    Audiences.audiencesData = response.data.audiences;
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
            // Clear all form fields
            $audienceForm.find('input[type="text"], textarea').val('');
            $('#aitwp-audience-id').val('');

            // Reset UI
            $('#aitwp-audience-form-title').text('Add New Audience');
            $('#aitwp-cancel-audience').hide();

            // Collapse sections
            CollapsibleSections.collapseAll($audienceForm);
        },

        updateList: function(audiences) {
            var html = '';

            if ($.isEmptyObject(audiences)) {
                html = '<p class="aitwp-no-items">No audiences defined yet. Create your first one below.</p>';
            } else {
                $.each(audiences, function(id, audience) {
                    var previewText = audience.definition || audience.description || '';
                    var preview = getPreview(previewText, 25);

                    // Add counts
                    var goalsCount = audience.goals ? audience.goals.length : 0;
                    var painsCount = audience.pains ? audience.pains.length : 0;
                    var meta = '';
                    if (goalsCount > 0) {
                        meta += ' &bull; ' + goalsCount + ' goal' + (goalsCount !== 1 ? 's' : '');
                    }
                    if (painsCount > 0) {
                        meta += ' &bull; ' + painsCount + ' pain' + (painsCount !== 1 ? 's' : '');
                    }

                    html += '<div class="aitwp-audience-item" data-id="' + audience.id + '">';
                    html += '<div class="aitwp-audience-header">';
                    html += '<strong class="aitwp-audience-name">' + $('<div>').text(audience.name).html() + '</strong>';
                    html += '<div class="aitwp-audience-actions">';
                    html += '<button type="button" class="button aitwp-edit-audience">Edit</button>';
                    html += '<button type="button" class="button aitwp-delete-audience">Delete</button>';
                    html += '</div></div>';
                    html += '<div class="aitwp-audience-preview">' + $('<div>').text(preview).html();
                    if (meta) {
                        html += '<span class="aitwp-audience-meta">' + meta + '</span>';
                    }
                    html += '</div>';
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
        CollapsibleSections.init();

        if ($profilesList.length) {
            VoiceProfiles.init();
        }
        if ($audiencesList.length) {
            Audiences.init();
        }
    });

})(jQuery);
