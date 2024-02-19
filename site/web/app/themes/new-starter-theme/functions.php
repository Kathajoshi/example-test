<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

if (! function_exists('\Roots\bootloader')) {
    wp_die(
        __('You need to install Acorn to use this theme.', 'sage'),
        '',
        [
            'link_url' => 'https://roots.io/acorn/docs/installation/',
            'link_text' => __('Acorn Docs: Installation', 'sage'),
        ]
    );
}

\Roots\bootloader()->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });


function addMyScript() {
    wp_enqueue_style('mytheme', 'https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.css', array('blueprint'), '', 'screen, projection');
}
add_action('wp_head', 'addMyScript');


function custom_post_listing($atts) {
    extract(shortcode_atts(array(
            'totalposts'    => '-1',
            'category'      => '',
            'thumbnail'     => 'false',
            'excerpt'       => 'true',
            'orderby'       => 'post_date',
            'posttype' => '',
            'taxonomytype' => '',
            ), $atts));

    $output = '';

    global $post;
    $args = array(
        'posts_per_page' => $totalposts, 
        'orderby' => $orderby,
        'post_type' => $posttype,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomytype,
                'field' => 'slug',
                'terms' => array( $category )
            )
        ));
    $myposts = NEW WP_Query($args);

    $output .= '<div class="wrapper">';
    while($myposts->have_posts()) {
        $myposts->the_post();
        $catList = wp_get_post_terms( get_the_ID(), $taxonomytype );
        $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' );

        $output .= '<div class="card">';
            if($thumbnail == 'true') {
                $output .= '<a href="'.get_the_permalink().'"><div class="card-banner"><img class="banner-img" src="'.$url.'" alt="" /></div></a>';
            }

            $output .= '<div class="card-body">';
                foreach($catList as $cd){
                    $output .= '<p class="blog-hashtag">#'.$cd->name.'</p>';
                }
                $output .= '<a href="'.get_the_permalink().'"><h2 class="blog-title">'.get_the_title().'</h2></a>';
            if ($excerpt == 'true') {
                $output .= '<p class="blog-description">'.get_the_excerpt().'</p>';
            }
            $output .= '</div>
        </div>';
    };

    $output .= '</div>';
    wp_reset_query();
    return $output;
}
add_shortcode('product-listing', 'custom_post_listing');