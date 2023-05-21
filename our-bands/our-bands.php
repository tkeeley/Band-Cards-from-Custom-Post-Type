<?php
/*
Plugin Name: Our Bands
Description: Custom post type for bands with band cards.
Version: 1.0
Author: Cup O Code
License: GPL2
*/


// Register the Our Bands custom post type
function our_bands_register_post_type() {
    $labels = array(
        'name' => 'Our Bands',
        'singular_name' => 'Band',
        // ... add more labels if needed
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'supports' => array( 'title', 'thumbnail' ),
        'menu_icon' => 'dashicons-format-audio', // Set the menu icon to a music note
        'has_archive' => true,
        'rewrite' => array( 'slug' => 'our-bands' ),
        'hierarchical' => false,
        'show_in_rest' => true,
        'orderby' => 'title', // Order posts alphabetically by title
        'order' => 'ASC'
    );

    register_post_type( 'our-bands', $args );
}
add_action( 'init', 'our_bands_register_post_type' );

function modify_post_type_loop($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('our-bands')) {
        $query->set('orderby', 'meta_value title');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'key' => 'is_last_post',
                'value' => '1',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => 'is_last_post',
                'value' => '1',
                'compare' => '!=',
            ),
        ));
    }
}
add_action('pre_get_posts', 'modify_post_type_loop');

function display_image_crop_notice() {
    global $pagenow, $post_type;

    // Check if we are on the 'post-new.php' screen for the 'our_bands' post type
    if ($pagenow === 'post-new.php' && $post_type === 'our-bands') {
        echo '<div class="notice notice-info" style="font-weight: bold; font-size: 16px;"><p>***Please make sure all images are cropped square for the best fit***</p></div>';
    }
}
add_action('admin_notices', 'display_image_crop_notice');


// Add custom meta box for band details
function our_bands_add_meta_box() {
    add_meta_box(
        'our-bands-details',
        'Band Details',
        'our_bands_meta_box_callback',
        'our-bands',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'our_bands_add_meta_box' );

//Add custom box to always show last CTA card
function add_custom_meta_box() {
    add_meta_box(
        'last_post_meta_box',
        'Last Post',
        'render_last_post_meta_box',
        'our-bands',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_custom_meta_box');

function render_last_post_meta_box($post) {
    $is_last_post = get_post_meta($post->ID, 'is_last_post', true);
    ?>
    <label for="is_last_post">
        <input type="checkbox" id="is_last_post" name="is_last_post" value="1" <?php checked($is_last_post, '1'); ?>>
        Display this post last
    </label>
    <?php
}

function save_last_post($post_id) {
    if (array_key_exists('is_last_post', $_POST)) {
        update_post_meta($post_id, 'is_last_post', $_POST['is_last_post']);
    } else {
        delete_post_meta($post_id, 'is_last_post');
    }
}
add_action('save_post', 'save_last_post');



// Callback function for the band details meta box
function our_bands_meta_box_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'our_bands_nonce' );
    $band_genre = get_post_meta( $post->ID, 'band_genre', true );
    $band_website = get_post_meta( $post->ID, 'band_website', true );
    ?>
    <p>
        <label for="band_genre">Band Genre:</label>
        <input type="text" name="band_genre" id="band_genre" value="<?php echo esc_attr( $band_genre ); ?>">
    </p>
    <p>
        <label for="band_website">Band Website:</label>
        <input type="text" name="band_website" id="band_website" value="<?php echo esc_attr( $band_website ); ?>">
    </p>
    
    <?php
}

// Save band details meta box data
function our_bands_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['our_bands_nonce'] ) || ! wp_verify_nonce( $_POST['our_bands_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'our-bands' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['band_genre'] ) ) {
        update_post_meta( $post_id, 'band_genre', sanitize_text_field( $_POST['band_genre'] ) );
    }

    if ( isset( $_POST['band_website'] ) ) {
        update_post_meta( $post_id, 'band_website', sanitize_text_field( $_POST['band_website'] ) );
    }
}
add_action( 'save_post', 'our_bands_save_meta_box_data' );

// Shortcode to display band cards
function our_bands_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count' => -1,
    ), $atts, 'our_bands' );

    $args = array(
        'post_type' => 'our-bands',
        'posts_per_page' => $atts['count'],
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $bands = new WP_Query( $args );

    ob_start();
    if ( $bands->have_posts() ) {
        ?>
        <div class="our-bands-wrapper">
            <?php while ( $bands->have_posts() ) : $bands->the_post(); ?>
                <div class="band-card">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php echo esc_url( get_post_meta( get_the_ID(), 'band_website', true ) ); ?>" target="_blank">
                            <?php the_post_thumbnail(); ?>
                        </a>
                    <?php endif; ?>
                    <p class="band-name"><?php the_title(); ?></p>
                    <p class="band-genre"><?php echo esc_html( get_post_meta( get_the_ID(), 'band_genre', true ) ); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
    } else {
        echo 'No bands found.';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'our_bands', 'our_bands_shortcode' );


// Remove main WordPress editor box
function our_bands_remove_editor() {
    remove_post_type_support( 'our-bands', 'editor' );
}
add_action( 'init', 'our_bands_remove_editor' );

// Add CSS
function our_bands_add_styles() {
    ?>
    <style>
	.our-bands-wrapper {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding: 0 1em;
    box-sizing: border-box;
}

.band-card {
    /*border: 1px solid #ddd;*/
    padding-bottom: 10px;
    /*margin: 0.5em;*/
    /*border-radius: 10px;*/
    margin-bottom: 2.5em;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    background-color: #231155;
    box-sizing: border-box;
    line-height: inherit;
    text-align: left;
    max-width: 400px; /* Set a maximum width for the cards */
     /* Flexbox properties for responsiveness */
    flex-grow: 0;
    flex-shrink: 1;
}

.band-card:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transform: translateY(-5px);
}

.band-card > * {
    margin: 0;
}

.band-card img {
    width: 95%;
    max-width: 100%;
    height: auto;
    display: block;
    margin-bottom: 0.5em;
    object-fit: cover;
    top: -20px;
    position: relative;
    left: 20px;
    /*padding: 5px;*/
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.75);
}

.band-name {
    font-weight: 700;
    text-align: center;
    font-family: 'Avenir Next', Arial, sans-serif;
    color: #fff;
}

.band-genre {
    text-align: center;
    font-family: 'Avenir Next', Arial, sans-serif;
    font-style: italic;
    color: #fff;
}

    </style>
    <?php
}
add_action('wp_head', 'our_bands_add_styles');