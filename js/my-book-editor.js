jQuery(document).ready(function($) {
    // Media Uploader for Book Cover
    var bookCoverUploader;

    $(document).on('click', '.browse-book-cover', function(e) {
        e.preventDefault();

        if (bookCoverUploader) {
            bookCoverUploader.open();
            return;
        }

        bookCoverUploader = wp.media({
            title: 'Choose Book Cover Image',
            button: {
                text: 'Select Cover'
            },
            multiple: false
        });

        bookCoverUploader.on('select', function() {
            var attachment = bookCoverUploader.state().get('selection').first().toJSON();
            $('#book_cover_id').val(attachment.id);
            $('#book_cover_url').val(attachment.url);
            $('#book-cover-preview img').attr('src', attachment.url);
            $('#book-cover-preview').show();
        });

        bookCoverUploader.open();
    });

    // Handle "Remove Image" button click for Book Cover
    $(document).on('click', '.remove-book-cover', function(e) {
        e.preventDefault();
        $('#book_cover_id').val('');
        $('#book_cover_url').val('');
        $('#book-cover-preview img').attr('src', '');
        $('#book-cover-preview').hide();
    });

    // AJAX to load book content
    $('#load-book-content-btn').on('click', function(e) {
        e.preventDefault();
        var bookId = $('#selected_book_id').val();

        if (bookId) {
            $.ajax({
                url: myBookEditorAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'my_book_editor_get_book_content',
                    book_id: bookId,
                    nonce: myBookEditorAjax.nonce
                },
                beforeSend: function() {
                    $('#book-editor-form').find('input, select, button, textarea').prop('disabled', true);
                    $('#load-book-content-btn').text('Loading...');
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#book_post_id').val(data.book_post_id);
                        $('#new_book_name').val(data.book_name); // Fill 'Add a book' with current title
                        $('#book_subtitle').val(data.book_subtitle);

                        // Set TinyMCE content
                        if (tinymce.get('book_content')) {
                            tinymce.get('book_content').setContent(data.book_content);
                        } else {
                            $('#book_content').val(data.book_content);
                        }

                        // Book Cover
                        if (data.book_cover_id && data.book_cover_url) {
                            $('#book_cover_id').val(data.book_cover_id);
                            $('#book_cover_url').val(data.book_cover_url);
                            $('#book-cover-preview img').attr('src', data.book_cover_url);
                            $('#book-cover-preview').show();
                        } else {
                            $('#book_cover_id').val('');
                            $('#book_cover_url').val('');
                            $('#book-cover-preview img').attr('src', '');
                            $('#book-cover-preview').hide();
                        }

                        // Footer Position and Insert Link
                        $('#footer_position').val(data.footer_position);
                        $('#insert_link').val(data.insert_link);

                        // Set Book Category (multi-select)
                        $('#book_category option').prop('selected', false); // Clear previous selections
                        if (data.book_category && data.book_category.length > 0) {
                            $.each(data.book_category, function(index, value) {
                                $('#book_category option[value="' + value + '"]').prop('selected', true);
                            });
                        }

                        // Set Book Level (multi-select)
                        $('#book_level option').prop('selected', false); // Clear previous selections
                        if (data.book_level && data.book_level.length > 0) {
                            $.each(data.book_level, function(index, value) {
                                $('#book_level option[value="' + value + '"]').prop('selected', true);
                            });
                        }

                    } else {
                        alert(response.data.message || 'Error loading book content.');
                        // Reset fields on error or if no content found
                        $('#book_post_id').val('0');
                        $('#new_book_name').val('');
                        $('#book_subtitle').val('');
                        if (tinymce.get('book_content')) {
                            tinymce.get('book_content').setContent('');
                        } else {
                            $('#book_content').val('');
                        }
                        $('#book_cover_id').val('');
                        $('#book_cover_url').val('');
                        $('#book-cover-preview img').attr('src', '');
                        $('#book-cover-preview').hide();
                        $('#footer_position').val('');
                        $('#insert_link').val('');
                        $('#book_category option').prop('selected', false);
                        $('#book_level option').prop('selected', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX Error: ' + textStatus + ' - ' + errorThrown);
                    console.log(jqXHR.responseText);
                },
                complete: function() {
                    $('#book-editor-form').find('input, select, button, textarea').prop('disabled', false);
                    $('#load-book-content-btn').text('Submit');
                }
            });
        } else {
            alert(myBookEditorAjax.alert_no_book_selected);
            // Clear all fields if no book is selected
            $('#book_post_id').val('0');
            $('#new_book_name').val('');
            $('#book_subtitle').val('');
            if (tinymce.get('book_content')) {
                tinymce.get('book_content').setContent('');
            } else {
                $('#book_content').val('');
            }
            $('#book_cover_id').val('');
            $('#book_cover_url').val('');
            $('#book-cover-preview img').attr('src', '');
            $('#book-cover-preview').hide();
            $('#footer_position').val('');
            $('#insert_link').val('');
            $('#book_category option').prop('selected', false);
            $('#book_level option').prop('selected', false);
        }
    });

    // Handle form submission (for actions like delete, publish, save)
    $('#book-editor-form').on('submit', function(e) {
        // Ensure TinyMCE content is saved back to the textarea before submission
        if (typeof tinymce != 'undefined' && tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
            tinymce.activeEditor.save();
        }

        var selectedAction = $('#submit_book_action_select').val();
        if (selectedAction === 'delete') {
            if (!confirm(myBookEditorAjax.alert_confirm_delete)) {
                e.preventDefault();
            }
        }
    });
});