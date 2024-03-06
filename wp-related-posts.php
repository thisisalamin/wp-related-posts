<?php
/**
 * Plugin Name: WP Related Posts
 * Plugin URI: https://github.com/thisisalamin/wp-related-posts
 * Description: A simple plugin to show related posts.
 * Version: 1.0
 * Author: Mohamed Alamin
 * Author URI: https://github.com/thisisalamin/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-related-posts
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define the WP_Related_Posts class
class WP_Related_Posts {

    // Define the number of posts to show
    private $posts_per_page = 5;
    // Constructor function that is called when the class is instantiated
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        // Add a filter that modifies the content of posts
        add_filter( 'the_content', array( $this, 'related_posts' ) , 20);
        add_action( 'wp_enqueue_scripts', array( $this, 'style_scripts' ) );
        $this->posts_per_page = apply_filters( 'wp_related_posts_per_page', $this->posts_per_page );
    }

    // Function to enqueue styles and scripts
    public function style_scripts(){
        wp_enqueue_style( 'wp-related-posts', plugin_dir_url( __FILE__ ) . 'assets/css/wp-related-posts.css', array(), '1.0', 'all' );
    }


    // Function to get related posts
    public function get_related_posts() {
        // Get the ID of the current post
        $post_id = get_the_ID();
        // Get the categories of the current post
        $category_ids = wp_get_post_categories( $post_id );
        // Define the arguments for the WP_Query
        $args = array(
            'category__in' => $category_ids,
            'post__not_in' => array( $post_id ),
            'posts_per_page' => $this->posts_per_page,
            'orderby' => 'rand',
            'ignore_sticky_posts' => true
        );
        $related_posts = new WP_Query( $args );
        return $related_posts;
    }

    // Function to add related posts to the content of posts
    public function related_posts($content) {
        if ( is_single() ) {
            // Get the related posts
            $related_posts = $this->get_related_posts();
            // Check if there are any related posts
            if ( $related_posts->have_posts() ) {
                $content .= '<div class="wp-related-posts">';
                $content .= '<h2>' . esc_html__('Related Posts', 'wp-related-posts') . '</h2>';
                $content .= '<ul>';
                while ( $related_posts->have_posts() ) {
                    $related_posts->the_post();

                    if ( has_post_thumbnail() ) {
                        $content .= '<li><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) . esc_html(get_the_title()) . '</a></li>';
                    }else{
                        $content .= '<li><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html(get_the_title()) . '</a></li>';
                    }
                }
                $content .= '</ul>';
                $content .= '</div>';
                wp_reset_postdata();
            }
        }
        return $content;
    }
}

// Create a new instance of the WP_Related_Posts class
new WP_Related_Posts();