<?php
/*
Plugin Name: My Book Editor
Plugin URI:  https://example.com/
Description: A custom editor for book profiles, accessible via shortcode, with custom category and level dropdowns, and author selection.
Version:     1.4.0 // Updated version for new features
Author:      Muhamad Fikri Haikal
Author URI:  https://caastedu.com/
License:     GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Global flag to indicate if the shortcode is used on the current page
global $my_book_editor_shortcode_used;
$my_book_editor_shortcode_used = false;

// Define plugin constants
if ( ! defined( 'MY_BOOK_EDITOR_VERSION' ) ) {
    define( 'MY_BOOK_EDITOR_VERSION', '1.4.0' );
}
if ( ! defined( 'MY_BOOK_EDITOR_PLUGIN_URL' ) ) {
    define( 'MY_BOOK_EDITOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'MY_BOOK_EDITOR_PLUGIN_DIR' ) ) {
    define( 'MY_BOOK_EDITOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Register Custom Post Type for Book Content
 */
function my_book_editor_register_cpt() {
    $labels = array(
        'name'          => _x( 'Books', 'Post Type General Name', 'my-book-editor' ),
        'singular_name' => _x( 'Book', 'Post Type Singular Name', 'my-book-editor' ),
        'menu_name'     => __( 'Books', 'my-book-editor' ),
        'all_items'     => __( 'All Books', 'my-book-editor' ),
        'add_new_item'  => __( 'Add New Book', 'my-book-editor' ),
        'add_new'       => __( 'Add New', 'my-book-editor' ),
        'new_item'      => __( 'New Book', 'my-book-editor' ),
        'edit_item'     => __( 'Edit Book', 'my-book-editor' ),
        'update_item'   => __( 'Update Book', 'my-book-editor' ),
        'view_item'     => __( 'View Book', 'my-book-editor' ),
        'search_items'  => __( 'Search Books', 'my-book-editor' ),
        'not_found'     => __( 'Not Found', 'my-book-editor' ),
        'not_found_in_trash' => __( 'Not found in Trash', 'my-book-editor' ),
    );
    $args = array(
        'label'               => __( 'Book', 'my-book-editor' ),
        'description'         => __( 'Content associated with books', 'my-book-editor' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'thumbnail' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-book',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'rewrite'             => array( 'slug' => 'book-profile' ),
        'query_var'           => true,
    );
    register_post_type( 'book_profile', $args );
}
add_action( 'init', 'my_book_editor_register_cpt' );

/**
 * Register custom taxonomies for Books (Category and Level)
 */
function my_book_editor_register_taxonomies() {
    // Category Taxonomy
    $category_labels = array(
        'name'              => _x( 'Book Categories', 'taxonomy general name', 'my-book-editor' ),
        'singular_name'     => _x( 'Book Category', 'taxonomy singular name', 'my-book-editor' ),
        'search_items'      => __( 'Search Book Categories', 'my-book-editor' ),
        'all_items'         => __( 'All Book Categories', 'my-book-editor' ),
        'parent_item'       => __( 'Parent Book Category', 'my-book-editor' ),
        'parent_item_colon' => __( 'Parent Book Category:', 'my-book-editor' ),
        'edit_item'         => __( 'Edit Book Category', 'my-book-editor' ),
        'update_item'       => __( 'Update Book Category', 'my-book-editor' ),
        'add_new_item'      => __( 'Add New Book Category', 'my-book-editor' ),
        'new_item_name'     => __( 'New Book Category Name', 'my-book-editor' ),
        'menu_name'         => __( 'Categories', 'my-book-editor' ),
    );
    $category_args = array(
        'hierarchical'      => true,
        'labels'            => $category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'book-category' ),
    );
    register_taxonomy( 'book_category', array( 'book_profile' ), $category_args );

    // Level Taxonomy
    $level_labels = array(
        'name'              => _x( 'Book Levels', 'taxonomy general name', 'my-book-editor' ),
        'singular_name'     => _x( 'Book Level', 'taxonomy singular name', 'my-book-editor' ),
        'search_items'      => __( 'Search Book Levels', 'my-book-editor' ),
        'all_items'         => __( 'All Book Levels', 'my-book-editor' ),
        'edit_item'         => __( 'Edit Book Level', 'my-book-editor' ),
        'update_item'       => __( 'Update Book Level', 'my-book-editor' ),
        'add_new_item'      => __( 'Add New Book Level', 'my-book-editor' ),
        'new_item_name'     => __( 'New Book Level Name', 'my-book-editor' ),
        'menu_name'         => __( 'Levels', 'my-book-editor' ),
    );
    $level_args = array(
        'hierarchical'      => false,
        'labels'            => $level_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'book-level' ),
    );
    register_taxonomy( 'book_level', array( 'book_profile' ), $level_args );
}
add_action( 'init', 'my_book_editor_register_taxonomies' );

/**
 * Add default terms for Book Categories and Levels on plugin activation.
 */
function my_book_editor_add_default_terms() {
    $categories = array(
        'Fiction',
        'Non-fiction',
        'Language Arts',
        'STEM',
        'Tests & Assessments',
        'Arts & Design'
    );
    foreach ( $categories as $category_name ) {
        if ( ! term_exists( $category_name, 'book_category' ) ) {
            wp_insert_term( $category_name, 'book_category' );
        }
    }

    $levels = array(
        'Elementary',
        'Intermediate',
        'Advanced',
        'College'
    );
    foreach ( $levels as $level_name ) {
        if ( ! term_exists( $level_name, 'book_level' ) ) {
            wp_insert_term( $level_name, 'book_level' );
        }
    }
}
register_activation_hook( __FILE__, 'my_book_editor_add_default_terms' );


/**
 * Enqueue styles and scripts for the book editor.
 * This will only run if the shortcode is detected on the current page.
 */
function my_book_editor_enqueue_assets() {
    global $my_book_editor_shortcode_used;

    if ( $my_book_editor_shortcode_used ) {
        wp_enqueue_media(); // For media uploader

        // Enqueue TinyMCE and other editor-related scripts (only if current user can use editor)
        if ( current_user_can( 'edit_posts' ) ) {
            wp_enqueue_script('wp-tinymce');
            wp_enqueue_script('editor');
            wp_enqueue_script('wplink');
            wp_enqueue_script('wp-plupload');
            wp_enqueue_script('thickbox');
            wp_enqueue_style( 'thickbox' );
        }

        // Enqueue the plugin's specific JavaScript
        wp_enqueue_script(
            'my-book-editor-script',
            MY_BOOK_EDITOR_PLUGIN_URL . 'js/my-book-editor.js',
            array( 'jquery' ),
            MY_BOOK_EDITOR_VERSION,
            true
        );

        // Enqueue plugin's custom CSS (if any, currently minimal)
        wp_enqueue_style(
            'my-book-editor-style',
            MY_BOOK_EDITOR_PLUGIN_URL . 'css/my-book-editor.css',
            array(),
            MY_BOOK_EDITOR_VERSION
        );

        // Localize AJAX URL and nonce for JavaScript
        wp_localize_script( 'my-book-editor-script', 'myBookEditorAjax', array(
            'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
            'nonce'                   => wp_create_nonce( 'book_editor_nonce' ),
            'alert_no_book_selected'  => __( 'Please select a book first.', 'my-book-editor' ),
            'alert_no_author_selected' => __( 'Please select an author first.', 'my-book-editor' ), // New alert for author
            'alert_confirm_delete'    => __( 'Are you sure you want to delete this book? This action cannot be undone.', 'my-book-editor' ),
            'alert_content_not_found' => __( 'No content found for the selected book.', 'my-book-editor' ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'my_book_editor_enqueue_assets' );

/**
 * Handle form submission for Book Content Editor.
 */
function my_book_editor_handle_submission() {
    if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'my_book_editor_submit' ) {
        return;
    }

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_safe_redirect( add_query_arg( 'status', 'error_permissions', wp_get_referer() ) );
        exit;
    }

    // Verify nonce
    if ( ! isset( $_POST['_wpnonce_book_content'] ) || ! wp_verify_nonce( $_POST['_wpnonce_book_content'], 'book_content_submission' ) ) {
        wp_die( 'Security check failed. Please refresh the page and try again.' );
    }

    // Sanitize and retrieve form data
    $selected_book_id   = isset( $_POST['selected_book_id'] ) ? intval( $_POST['selected_book_id'] ) : 0;
    $new_book_name      = sanitize_text_field( $_POST['new_book_name'] );
    $book_subtitle      = sanitize_text_field( $_POST['book_subtitle'] );
    $book_content       = wp_kses_post( $_POST['book_content'] );
    $book_cover_id      = isset( $_POST['book_cover_id'] ) ? intval( $_POST['book_cover_id'] ) : 0;
    $footer_position    = isset( $_POST['footer_position'] ) ? sanitize_text_field( $_POST['footer_position'] ) : '';
    $insert_link        = isset( $_POST['insert_link'] ) ? esc_url_raw( $_POST['insert_link'] ) : '';

    // Get selected taxonomy terms (slugs)
    $book_category_slug = isset( $_POST['book_category'] ) ? sanitize_text_field( $_POST['book_category'] ) : '';
    $book_level_slug    = isset( $_POST['book_level'] ) ? sanitize_text_field( $_POST['book_level'] ) : '';
    $selected_author_id = isset( $_POST['selected_author_id'] ) ? intval( $_POST['selected_author_id'] ) : 0; // New: Author ID

    $submit_action      = sanitize_text_field( $_POST['submit_book_action'] );

    // Determine the book title to use for the post
    $post_title = '';
    if ( ! empty( $new_book_name ) ) {
        $post_title = $new_book_name;
    } elseif ( $selected_book_id > 0 ) {
        $existing_book = get_post( $selected_book_id );
        if ( $existing_book ) {
            $post_title = $existing_book->post_title;
        }
    }

    $existing_post_id = isset( $_POST['book_post_id'] ) ? intval( $_POST['book_post_id'] ) : 0;

    // Basic validation for new book or updating existing book if title changed
    if ( empty( $post_title ) && empty($existing_post_id) ) { // Only require title if creating new or if selected existing and its title is empty somehow
        wp_safe_redirect( add_query_arg( 'status', 'error_empty_title', wp_get_referer() ) );
        exit;
    }

    // Determine post status based on button clicked
    $post_status = 'pending';
    $redirect_status = 'error';

    if ( $submit_action === 'publish' ) {
        if ( current_user_can( 'publish_book_profiles' ) || current_user_can( 'publish_posts' ) ) {
            $post_status = 'publish';
            $redirect_status = 'book_published';
        } else {
            $post_status = 'pending';
            $redirect_status = 'book_pending_review';
        }
    } elseif ( $submit_action === 'save' ) {
        $post_status = 'draft';
        $redirect_status = 'book_saved_draft';
    } elseif ( $submit_action === 'unpublish' ) {
        $post_status = 'draft';
        $redirect_status = 'book_unpublished';
    } elseif ( $submit_action === 'archive' ) {
        // Check if 'archive' post status is registered
        if ( get_post_status_object('archive') ) {
             $post_status = 'archive';
        } else {
            // Fallback to draft if 'archive' status is not registered
            $post_status = 'draft';
        }
        $redirect_status = 'book_archived';
    } elseif ( $submit_action === 'delete' ) {
        if ( $existing_post_id ) {
            if ( current_user_can( 'delete_book_profiles' ) || current_user_can( 'delete_posts' ) ) {
                if ( wp_delete_post( $existing_post_id, true ) ) {
                    wp_safe_redirect( add_query_arg( 'status', 'book_deleted', wp_get_referer() ) );
                    exit;
                } else {
                    error_log( 'Error deleting book post: ' . $existing_post_id );
                    wp_safe_redirect( add_query_arg( 'status', 'error_book_delete', wp_get_referer() ) );
                    exit;
                }
            } else {
                wp_safe_redirect( add_query_arg( 'status', 'error_permissions', wp_get_referer() ) );
                exit;
            }
        } else {
            wp_safe_redirect( add_query_arg( 'status', 'error_no_content_to_delete', wp_get_referer() ) );
            exit;
        }
    }

    // Prepare post data
    $post_data = array(
        'post_title'    => $post_title,
        'post_content'  => $book_content,
        'post_status'   => $post_status,
        'post_type'     => 'book_profile',
        'post_author'   => get_current_user_id(),
    );

    $post_id = 0;
    if ( $existing_post_id > 0 ) {
        $post_data['ID'] = $existing_post_id;
        $result = wp_update_post( $post_data, true );
    } else {
        $result = wp_insert_post( $post_data, true );
    }

    if ( is_wp_error( $result ) ) {
        error_log( 'Error creating/updating book post: ' . $result->get_error_message() );
        wp_safe_redirect( add_query_arg( 'status', 'error_book_db', wp_get_referer() ) );
    } elseif ( $result === 0 ) {
        error_log( 'Failed to create/update book post, wp_insert_post/wp_update_post returned 0.' );
        wp_safe_redirect( add_query_arg( 'status', 'error_book_db', wp_get_referer() ) );
    } else {
        // Save book subtitle as post meta
        update_post_meta( $result, '_book_subtitle', $book_subtitle );

        // Set featured image (Book Cover)
        if ( ! empty( $book_cover_id ) ) {
            set_post_thumbnail( $result, $book_cover_id );
        } else {
            delete_post_thumbnail( $result ); // Remove if ID is 0 or empty
        }

        // Save new footer fields as post meta
        update_post_meta( $result, '_footer_position', $footer_position );
        update_post_meta( $result, '_insert_link', $insert_link );

        // Save selected Author ID as post meta for the book
        // Only update if an author is explicitly selected or if it's a new book.
        // If '0' (None) is selected, it will update to 0.
        update_post_meta( $result, '_book_author_id', $selected_author_id );


        // Set taxonomies
        if ( ! empty( $book_category_slug ) ) {
            wp_set_post_terms( $result, $book_category_slug, 'book_category' );
        } else {
            wp_set_post_terms( $result, null, 'book_category' ); // Clear if no category selected
        }

        if ( ! empty( $book_level_slug ) ) {
            wp_set_post_terms( $result, $book_level_slug, 'book_level' );
        } else {
            wp_set_post_terms( $result, null, 'book_level' ); // Clear if no level selected
        }

        wp_safe_redirect( add_query_arg( 'status', $redirect_status, wp_get_referer() ) );
    }
    exit;
}
add_action( 'admin_post_my_book_editor_submit', 'my_book_editor_handle_submission' );
add_action( 'admin_post_nopriv_my_book_editor_submit', 'my_book_editor_handle_submission' );

/**
 * AJAX handler to get book content based on book ID.
 */
function my_book_editor_get_book_content_ajax() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to view this content.', 'my-book-editor' ) ) );
    }

    check_ajax_referer( 'book_editor_nonce', 'nonce' );

    $book_id = isset( $_POST['book_id'] ) ? intval( $_POST['book_id'] ) : 0;

    if ( $book_id ) {
        $args = array(
            'p'              => $book_id,
            'post_type'      => 'book_profile',
            'post_status'    => array( 'publish', 'pending', 'draft', 'archive' ),
            'posts_per_page' => 1,
        );
        $book_query = new WP_Query( $args );

        if ( $book_query->have_posts() ) {
            $book_post = $book_query->posts[0];

            $current_categories = wp_get_post_terms( $book_post->ID, 'book_category', array( 'fields' => 'slugs' ) );
            $current_levels     = wp_get_post_terms( $book_post->ID, 'book_level', array( 'fields' => 'slugs' ) );
            $associated_author_id = get_post_meta( $book_post->ID, '_book_author_id', true );

            // Ensure book cover URL is fetched correctly
            $book_cover_id = get_post_thumbnail_id( $book_post->ID );
            $book_cover_url = $book_cover_id ? wp_get_attachment_url( $book_cover_id ) : '';


            wp_send_json_success( array(
                'book_post_id'    => $book_post->ID,
                'book_name'       => esc_html( $book_post->post_title ),
                'book_subtitle'   => esc_html( get_post_meta( $book_post->ID, '_book_subtitle', true ) ),
                'book_content'    => $book_post->post_content,
                'book_cover_id'   => $book_cover_id,
                'book_cover_url'  => $book_cover_url,
                'footer_position' => esc_html( get_post_meta( $book_post->ID, '_footer_position', true ) ),
                'insert_link'     => esc_url( get_post_meta( $book_post->ID, '_insert_link', true ) ),
                'book_categories' => !empty($current_categories) ? $current_categories[0] : '',
                'book_levels'     => !empty($current_levels) ? $current_levels[0] : '',
                'associated_author_id' => intval($associated_author_id), // New: Send associated author ID
            ) );
        } else {
            // If book ID is provided but no content found for it, clear form and allow new entry
            wp_send_json_success( array(
                'book_post_id'    => 0, // Reset to 0 for new content
                'book_name'       => '',
                'book_subtitle'   => '',
                'book_content'    => '',
                'book_cover_id'   => 0,
                'book_cover_url'  => '',
                'footer_position' => '',
                'insert_link'     => '',
                'book_categories' => '',
                'book_levels'     => '',
                'associated_author_id' => 0,
                'message'         => 'No existing content found for this book. You can create a new one.',
            ) );
        }
    } else {
        // If no book ID, implies a fresh start or "Add New" scenario
        wp_send_json_success( array(
            'book_post_id'    => 0,
            'book_name'       => '',
            'book_subtitle'   => '',
            'book_content'    => '',
            'book_cover_id'   => 0,
            'book_cover_url'  => '',
            'footer_position' => '',
            'insert_link'     => '',
            'book_categories' => '',
            'book_levels'     => '',
            'associated_author_id' => 0,
            'message'         => __( 'Invalid book ID, or no book selected.', 'my-book-editor' )
        ) );
    }
}
add_action( 'wp_ajax_my_book_editor_get_book_content', 'my_book_editor_get_book_content_ajax' );

/**
 * AJAX handler to get author content based on author ID.
 * This is a new AJAX function, specifically for loading author data if needed separately.
 * For this plugin, it's mainly to populate the dropdown when a book is loaded.
 * A separate AJAX call is added for when the "Submit" button next to "Select an author" is clicked.
 */
function my_book_editor_get_author_content_ajax() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to view this content.', 'my-book-editor' ) ) );
    }

    check_ajax_referer( 'book_editor_nonce', 'nonce' );

    $author_id = isset( $_POST['author_id'] ) ? intval( $_POST['author_id'] ) : 0;

    if ( $author_id ) {
        $author_post = get_post( $author_id ); // Assuming 'author_profile' is a CPT
        if ( $author_post && $author_post->post_type === 'author_profile' ) {
            // You can fetch other author-related data here if needed for the book editor,
            // but for now, we just confirm the author selection.
            wp_send_json_success( array(
                'author_id'   => $author_post->ID,
                'author_name' => $author_post->post_title,
                'message'     => 'Author selected: ' . $author_post->post_title,
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Author not found.', 'my-book-editor' ) ) );
        }
    } else {
        wp_send_json_error( array( 'message' => __( 'No author selected.', 'my-book-editor' ) ) );
    }
}
add_action( 'wp_ajax_my_book_editor_get_author_content', 'my_book_editor_get_author_content_ajax' );


/**
 * Shortcode to display the Book Content Editor.
 */
function my_book_editor_shortcode() {
    global $my_book_editor_shortcode_used;
    $my_book_editor_shortcode_used = true;

    ob_start();

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        ?>
        <p class="text-center text-red-500">You must be logged in with sufficient permissions to access the Book Content Editor.</p>
        <?php
        return ob_get_clean();
    }

    // Initialize values for form fields - These will be populated by JS on load/selection
    $book_name_val      = '';
    $book_subtitle_val  = '';
    $book_content_val   = '';
    $book_cover_id_val  = 0;
    $book_cover_url_val = '';
    $footer_position_val= '';
    $insert_link_val    = '';
    $book_post_id_val   = 0;
    $book_category_val  = '';
    $book_level_val     = '';
    $associated_author_id_val = 0; // New: Author ID for initial load


    // Display status messages
    if ( isset( $_GET['status'] ) ) {
        $status_message = '';
        $status_class = 'bg-green-100 border border-green-400 text-green-700';

        switch ( $_GET['status'] ) {
            case 'book_published':
                $status_message = 'Book successfully published!'; break;
            case 'book_saved_draft':
                $status_message = 'Book saved as draft!'; break;
            case 'book_pending_review':
                $status_message = 'Book submitted for review!'; break;
            case 'book_unpublished':
                $status_message = 'Book successfully unpublished (set to draft)!'; break;
            case 'book_archived':
                $status_message = 'Book successfully archived!'; break;
            case 'book_deleted':
                $status_message = 'Book successfully deleted!'; break;
            case 'book_added':
                $status_message = 'New book added successfully!'; break;
            case 'error_empty_title':
                $status_message = 'Error: Book name cannot be empty. Please fill the book name field.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700'; break;
            case 'error_book_db':
                $status_message = 'An error occurred while submitting the book. Please try again.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700'; break;
            case 'error_book_delete':
                $status_message = 'An error occurred while deleting the book. Please try again.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700'; break;
            case 'error_no_content_to_delete':
                $status_message = 'No content was selected for deletion.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700'; break;
            case 'error_permissions':
                $status_message = 'You do not have sufficient permissions to perform this action.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700'; break;
            default:
                $status_message = 'An unknown status occurred.';
                $status_class = 'bg-red-100 border border-red-400 text-red-700';
        }
        echo '<div class="notice ' . esc_attr($status_class) . ' px-4 py-3 rounded-none relative mb-4 is-dismissible"><p>' . esc_html($status_message) . '</p></div>';
    }

    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $user_avatar = get_avatar_url( $current_user->ID, array( 'size' => 24 ) );

    ?>
    <div class="bg-gray-100 text-[12px] text-gray-600 font-mono flex justify-end gap-9 px-20 py-1 max-w-12xl mx-auto">
        <span>myEMAIL</span>
        <span>myCALENDAR</span>
        <span>myPROJECTS</span>
        <span>myLEARNING</span>
        <span>myWORK</span>
        <a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="text-gray-600 hover:text-black">Logout</a>
    </div>

    <main class="w-full mx-auto flex flex-col md:flex-row gap-2 px-2 py-2 min-h-[118vh]">
        <aside aria-label="Left admin navigation panel" class="w-[35px] md:w-auto border border-gray-300 rounded-none p-3 text-xs text-gray-700 font-sans bg-white flex-shrink-0 min-h-[118vh] overflow-y-auto hide-scrollbar">
            <div class="flex items-center gap-2 mb-4">
                <img alt="User avatar placeholder" class="rounded-full w-6 h-6" src="<?php echo esc_url($user_avatar); ?>" />
                <span class="truncate text-[11px] text-gray-600"><?php echo esc_html($user_email); ?></span>
                <i class="fas fa-sync-alt cursor-pointer text-gray-400 text-[12px] ml-auto"></i>
            </div>
            <nav class="space-y-0.5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Website Content Editor</h2>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/author-endpoint-v2/"><i class="fas fa-user text-gray-500"></i> Author Endpoint Page</a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/article-endpoint/"><span><i class="fas fa-file-alt text-gray-500"></i> Article Endpoint Page</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/book-endpoint/"><i class="fas fa-book-open text-gray-500"></i> Book Endpoint Page</a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/video-endpoint/"><span><i class="fab fa-youtube text-gray-500"></i> Video Endpoint Page</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <div><br/></div>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/"><i class="fas fa-th-large text-gray-500"></i> Dashboard</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/profile/"><i class="fas fa-user text-gray-500"></i> My Profile</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/enrolled-courses/"><i class="fas fa-book-open text-gray-500"></i> Enrolled Courses</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/my-bookings/"><i class="fas fa-calendar-check text-gray-500"></i> My Tutor Bookings</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/download-certificate/"><i class="fas fa-download text-gray-500"></i> Download Certificates</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/wishlist/"><i class="far fa-heart text-gray-500"></i> Wishlist</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/reviews/"><i class="far fa-star text-gray-500"></i> Reviews</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/purchase-history/"><i class="fas fa-history text-gray-500"></i> Purchase History</a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/my-account/"><span><i class="fas fa-store text-gray-500"></i> Store Dashboard</span></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/courses/"><span><i class="fas fa-book-open text-gray-500"></i> Courses</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/lessons/"><span><i class="fas fa-file-alt text-gray-500"></i> All Lessons</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/quizzes/"><span><i class="fas fa-question-circle text-gray-500"></i> Quizzes</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/meeting/"><span><i class="fab fa-youtube text-gray-500"></i> Meetings</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/tutor-booking/"><span><i class="fas fa-calendar-check text-gray-500"></i> Tutor Bookings</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/assignments/"><span><i class="fas fa-tasks text-gray-500"></i> Assignments</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 justify-between hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/question-answer/"><span><i class="fas fa-question-circle text-gray-500"></i> Question &amp; Answer</span><i class="fas fa-chevron-right text-[9px] text-gray-500"></i></a>
                <a class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px]" href="https://caastedu.com/dashboard/announcements/"><i class="fas fa-bullhorn text-gray-500"></i> Announcements</a>
            </nav>
            <div class="mt-4 flex items-center gap-2 text-[11px] text-gray-600 cursor-pointer">
                <a href="https://caastedu.com/dashboard/settings/" class="flex items-center gap-2 hover:bg-gray-50 rounded-none px-2 py-1 text-gray-700 text-[11px] w-full">
                    <i class="fas fa-cog text-gray-500"></i> Settings
                </a>
            </div>
        </aside>

        <section class="flex-1 flex flex-col gap-3">
            <div class="bg-white border border-gray-300 rounded-none p-2 min-h-[calc(118vh - 40px)]">
                <h2 class="text-xl font-semibold mb-4">Book Content Editor</h2>

                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" enctype="multipart/form-data" class="font-sans" id="book-editor-form">
                    <input type="hidden" name="action" value="my_book_editor_submit">
                    <?php wp_nonce_field('book_content_submission', '_wpnonce_book_content'); ?>
                    <input type="hidden" name="book_post_id" id="book_post_id" value="<?php echo esc_attr( $book_post_id_val ); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-end mb-3">
                        <div class="md:col-span-2">
                            <label for="selected_author_id" class="block text-sm font-medium text-gray-700">Select an author</label>
                            <select name="selected_author_id" id="selected_author_id" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select an author --</option>
                                <?php
                                // Fetch authors from 'author_profile' CPT
                                $authors = get_posts( array(
                                    'post_type'      => 'author_profile', // Assuming 'author_profile' CPT
                                    'posts_per_page' => -1,
                                    'orderby'        => 'title',
                                    'order'          => 'ASC',
                                    'post_status'    => 'publish', // Only published authors
                                ) );
                                foreach ( $authors as $author ) {
                                    $selected = selected( $associated_author_id_val, $author->ID, false );
                                    echo '<option value="' . esc_attr( $author->ID ) . '" ' . $selected . '>' . esc_html( $author->post_title ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="load-author-content-btn" class="button button-primary bg-gray-200 text-gray-800 px-4 py-2 rounded-none hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full">Submit</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-end mb-3">
                        <div class="md:col-span-2">
                            <label for="selected_book_id" class="block text-sm font-medium text-gray-700">Select a book</label>
                            <select name="selected_book_id" id="selected_book_id" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select a book --</option>
                                <?php
                                $books = get_posts( array(
                                    'post_type'      => 'book_profile',
                                    'posts_per_page' => -1,
                                    'orderby'        => 'title',
                                    'order'          => 'ASC',
                                    'post_status'    => array('publish', 'draft', 'pending', 'archive'),
                                ) );
                                foreach ( $books as $book ) {
                                    echo '<option value="' . esc_attr( $book->ID ) . '">' . esc_html( $book->post_title ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="load-book-content-btn" class="button button-primary bg-gray-200 text-gray-800 px-4 py-2 rounded-none hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full">Submit</button>
                        </div>
                    </div>

                    <div class="flex flex-col mb-3">
                        <label for="new_book_name" class="block text-sm font-medium text-gray-700">Add a book</label>
                        <input type="text" name="new_book_name" id="new_book_name" placeholder="Enter book name" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="<?php echo esc_attr($book_name_val); ?>" />
                    </div>

                    <div class="flex flex-col mb-3">
                        <label for="book_subtitle" class="block text-sm font-medium text-gray-700">Book Subtitle:</label>
                        <input type="text" name="book_subtitle" id="book_subtitle" placeholder="Book subtitle text" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="<?php echo esc_attr($book_subtitle_val); ?>" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-end mb-3">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Upload book cover image</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="text" id="book_cover_url" name="book_cover_url" class="flex-grow border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Browse book cover" readonly value="<?php echo esc_url($book_cover_url_val); ?>" />
                                <input type="hidden" id="book_cover_id" name="book_cover_id" value="<?php echo esc_attr($book_cover_id_val); ?>" />
                            </div>
                            <div id="book-cover-preview" class="mt-2 text-center" style="<?php echo empty($book_cover_url_val) ? 'display:none;' : ''; ?>">
                                <img src="<?php echo esc_url($book_cover_url_val); ?>" alt="Book Cover Preview" style="max-width: 150px; height: auto; display: inline-block; margin-bottom: 5px;" />
                                <button type="button" class="remove-book-cover text-red-500 hover:text-red-700 text-xs mt-1 rounded-none block mx-auto">Remove Image</button>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="button button-secondary browse-book-cover bg-gray-200 text-gray-800 px-4 py-2 rounded-none hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full">Browse</button>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="book_content" class="block text-sm font-medium text-gray-700">Body Content:</label>
                        <?php
                        if ( current_user_can( 'edit_posts' ) ) {
                            wp_editor( $book_content_val, 'book_content', array(
                                'textarea_name' => 'book_content',
                                'textarea_rows' => 12,
                                'teeny'         => false,
                                'media_buttons' => false,
                                'tinymce'       => array(
                                    'height' => 300,
                                    'toolbar1' => 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | forecolor backcolor',
                                    'toolbar2' => 'print preview | table | charmap emoticons | code fullscreen',
                                ),
                                'editor_class' => 'mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                            ) );
                        } else {
                            echo '<textarea name="book_content" id="book_content" rows="12" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">' . esc_textarea($book_content_val) . '</textarea>';
                        }
                        ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="book_category" class="block text-sm font-medium text-gray-700">Select a category</label>
                            <select name="book_category" id="book_category" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select a category --</option>
                                <?php
                                $categories = get_terms( array(
                                    'taxonomy'   => 'book_category',
                                    'hide_empty' => false,
                                ) );
                                foreach ( $categories as $category ) {
                                    $selected = ( $book_category_val === $category->slug ) ? 'selected' : '';
                                    echo '<option value="' . esc_attr( $category->slug ) . '" ' . $selected . '>' . esc_html( $category->name ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="book_level" class="block text-sm font-medium text-gray-700">Select a level</label>
                            <select name="book_level" id="book_level" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select a level --</option>
                                <?php
                                $levels = get_terms( array(
                                    'taxonomy'   => 'book_level',
                                    'hide_empty' => false,
                                ) );
                                foreach ( $levels as $level ) {
                                    $selected = ( $book_level_val === $level->slug ) ? 'selected' : '';
                                    echo '<option value="' . esc_attr( $level->slug ) . '" ' . $selected . '>' . esc_html( $level->name ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4">
                        <div class="md:col-span-2 flex flex-col">
                            <label for="submit_book_action_select" class="block text-sm font-medium text-gray-700">Action</label>
                            <select name="submit_book_action" id="submit_book_action_select" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="save">Save Draft</option>
                                <option value="publish">Publish</option>
                                <option value="unpublish">Unpublish</option>
                                <option value="archive">Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="submit_book_action_btn" value="submit" class="button button-primary bg-gray-200 text-gray-800 px-4 py-2 rounded-none hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full">Submit</button>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4">
                        <div class="flex flex-col">
                            <label for="footer_position" class="block text-sm font-medium text-gray-700">Footer</label>
                            <select name="footer_position" id="footer_position" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select Position --</option>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    $value = 'Featured_' . $i;
                                    echo '<option value="' . esc_attr($value) . '" ' . selected($footer_position_val, $value, false) . '>' . esc_html($value) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="insert_link" class="block text-sm font-medium text-gray-700">Insert Link</label>
                            <input type="url" name="insert_link" id="insert_link" placeholder="https://example.com" class="mt-1 block w-full border border-gray-300 rounded-none shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="<?php echo esc_url($insert_link_val); ?>" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="submit_book_action" value="save" class="button button-primary bg-gray-200 text-gray-800 px-4 py-2 rounded-none hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </section>

    </main>
    <?php
    return ob_get_clean();
}
add_shortcode( 'my_book_editor', 'my_book_editor_shortcode' );