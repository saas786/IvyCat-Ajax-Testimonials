<?php

/**
 *  Plugin Name: IvyCat AJAX Testimonials
 *  Plugin URI: http://wordpress.org/extend/plugins/ivycat-ajax-testimonials/
 *  Description: Simple plugin for adding dynamic testimonials to your site.
 *  Author: IvyCat Web Services
 *  Author URI: http://www.ivycat.com
 *  Version: 1.2.1
 *  License: GNU General Public License v2.0
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
 ------------------------------------------------------------------------
    IvyCat AJAX Testimonials, Copyright 2012 IvyCat, Inc. (admins@ivycat.com)
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

 */

if ( ! defined( 'ICTESTI_DIR' ) )
    define( 'ICTESTI_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'ICTESTI_URL' ) )
    define( 'ICTESTI_URL', plugin_dir_url( __FILE__ ) );


add_action( 'plugins_loaded', array( 'IvyCatTestimonials', 'start' ) );

class IvyCatTestimonials {
    
    public function start() {
        add_action( 'init', array( __CLASS__, 'init' ) );
        add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );
    }
    
    public function init() {
        $labels = array(
            'name'               => _x( 'Testimonials', 'post format general name', 'ivycat-ajax-testimonials' ),
            'singular_name'      => _x( 'Testimonial', 'post format singular name', 'ivycat-ajax-testimonials' ),
            'add_new'            => _x( 'Add New', 'testimonials', 'ivycat-ajax-testimonials' ),
            'add_new_item'       => __( 'Add New Testimonial', 'ivycat-ajax-testimonials' ),
            'edit_item'          => __( 'Edit Testimonial', 'ivycat-ajax-testimonials' ),
            'new_item'           => __( 'New Testimonial', 'ivycat-ajax-testimonials' ),
            'view_item'          => __( 'View Testimonial', 'ivycat-ajax-testimonials' ),
            'search_items'       => __( 'Search Testimonials', 'ivycat-ajax-testimonials' ),
            'not_found'          => __( 'No testimonials found.', 'ivycat-ajax-testimonials' ),
            'not_found_in_trash' => __( 'No testimonials found in Trash.', 'ivycat-ajax-testimonials' ),
            'all_items'          => __( 'All Testimonials', 'ivycat-ajax-testimonials' ),
            'menu_name'          => __( 'Testimonials', 'ivycat-ajax-testimonials' )
        );
        
        $args = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true, 
            'query_var'            => true,
            'register_meta_box_cb' => array( __CLASS__, 'register_testimonial_meta_boxes' ),
            'rewrite'              => true,
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_position'        => 4,
            'supports'             => array( 'title', 'editor' )
        );

        register_post_type( 'testimonials', $args );
        
        $tax_labels = array(
            'name'                       => _x( 'Testimonial Groups', 'taxonomy general name', 'ivycat-ajax-testimonials' ),
            'singular_name'              => _x( 'Testimonial Group', 'taxonomy singular name', 'ivycat-ajax-testimonials' ),
            'search_items'               => __( 'Search Testimonial Groups', 'ivycat-ajax-testimonials' ),
            'popular_items'              => __( 'Popular Testimonial Groups', 'ivycat-ajax-testimonials' ),
            'all_items'                  => __( 'All Testimonial Groups', 'ivycat-ajax-testimonials' ),
            'parent_item'                => __( 'Parent Testimonial Groups', 'ivycat-ajax-testimonials' ),
            'parent_item_colon'          => __( 'Parent Testimonial Group:', 'ivycat-ajax-testimonials' ),
            'edit_item'                  => __( 'Edit Testimonial Group', 'ivycat-ajax-testimonials' ),
            'view_item'                  => __( 'View Testimonial Group', 'ivycat-ajax-testimonials' ),
            'update_item'                => __( 'Update Testimonial Group', 'ivycat-ajax-testimonials' ),
            'add_new_item'               => __( 'Add New Testimonial Group', 'ivycat-ajax-testimonials' ),
            'new_item_name'              => __( 'New Testimonial Group Name', 'ivycat-ajax-testimonials' ),
            'separate_items_with_commas' => __( 'Separate testimonial groups with commas', 'ivycat-ajax-testimonials' ),
            'add_or_remove_items'        => __( 'Add or remove testimonial groups', 'ivycat-ajax-testimonials' ),
            'choose_from_most_used'      => __( 'Choose from most used testimonial groups', 'ivycat-ajax-testimonials' )
        );
        
        $tax_args = array(
            'hierarchical'   => true,
            'labels'         => $tax_labels,
            'rewrite'        => true,
        );
        
        register_taxonomy( 'testimonial-group', 'testimonials', $tax_args );
        
        add_action( 'wp_ajax_nopriv_get-testimonials',  array( __CLASS__, 'more_testimonials' ) );
        add_action( 'wp_ajax_get-testimonials',  array( __CLASS__, 'more_testimonials' ) );
        add_action( 'save_post' , array( __CLASS__, 'save_testimonial_metadata' ) );
        add_filter( 'post_updated_messages', array( __CLASS__, 'testimonial_update_messages' ) );
        
        add_shortcode( 'ic_do_testimonials', array( __CLASS__, 'do_testimonials' ) );
        
        wp_register_script( 'ict-ajax-scripts', ICTESTI_URL . 'assets/ivycat_testimonials_scripts.js', array( 'jquery' ) );
        wp_localize_script( 'ict-ajax-scripts', 'ICSaconn', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }
    
    public function register_widgets() {
        require_once( ICTESTI_DIR . 'lib/IvyCatTestimonialsWidget.php' );
        register_widget( 'IvyCatTestimonialsWidget' );
    }
    
    public function testimonial_update_messages( $messages ) {
        global $post;
        
        $messages['testimonials'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __( 'Testimonial updated. <a href="%s">View Testimonial</a>', 'ivycat-ajax-testimonials' ), esc_url( get_permalink( $post->ID ) ) ),
            2  => __( 'Custom field updated.', 'ivycat-ajax-testimonials' ),
            3  => __( 'Custom field deleted.', 'ivycat-ajax-testimonials' ),
            4  => __( 'Testimonial updated.', 'ivycat-ajax-testimonials' ),
            /* translators: %s: date and time of the revision */
            5  => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s', 'ivycat-ajax-testimonials' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( 'Testimonial published. <a href="%s">View Testimonial</a>', 'ivycat-ajax-testimonials' ), esc_url( get_permalink( $post->ID ) ) ),
            7  => __( 'Testimonial saved.', 'ivycat-ajax-testimonials' ),
            8  => sprintf( __( 'Testimonial submitted. <a target="_blank" href="%s">Preview Testimonial</a>', 'ivycat-ajax-testimonials' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
            9  => sprintf( __( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Testimonial</a>', 'ivycat-ajax-testimonials' ),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i', 'ivycat-ajax-testimonials' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
            10 => sprintf( __( 'Testimonial draft updated. <a target="_blank" href="%s">Preview Testimonial</a>', 'ivycat-ajax-testimonials' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
        );
        
        return $messages;
    }
    
    public function register_testimonial_meta_boxes() {
        add_meta_box(
            'Testimonialinfo-meta',
            __( 'Testimonial Data', 'ivycat-ajax-testimonials' ),
            array( __CLASS__, 'testimonial_metabox' ),
            'testimonials',
            'side',
            'high'
        );
    }
    
    public function testimonial_metabox( $post ) {
        $testimonial_order = get_post_meta( $post->ID, 'ivycat_testimonial_order', true );
        wp_nonce_field( 'save-testimonial-order_' . $post->ID, 'ivycat_testimonial_order_nonce' );
        ?>
        <p>
            <label for="test-order"><?php _e( 'Order:', 'ivycat-ajax-testimonials' ); ?></label>
            <input id="test-order" type="text" name="testimonial_order" value="<?php echo absint( $testimonial_order ); ?>" />
        </p>
        <?php
    }
    
    public function save_testimonial_metadata( $post_id ) {
        if ( ! isset( $_POST['ivycat_testimonial_order_nonce'] ) || ! wp_verify_nonce( $_POST['ivycat_testimonial_order_nonce'], 'save-testimonial-order_' . $post_id ) )
            return;
        
        update_post_meta( $post_id, 'ivycat_testimonial_order', $_POST['testimonial_order'] );
    }
    
    public function do_testimonials( $atts, $content = null ) {
        $atts = shortcode_atts( array(
            'quantity' => 3,
            'group'    => false
        ), $atts );
        extract( $atts );
        
        $testimonials = self::get_testimonials( 1, $group );
        ob_start();
        ?>
        <div id="ivycat-testimonial">
            <blockquote class="testimonial-content">
                <div class="content"><?php echo $testimonials[0]['testimonial_content'] ?></div>
                <footer>
                    <cite>
                        <?php echo $testimonials[0]['testimonial_title']; ?>
                    </cite>
                </footer>
            </blockquote>
            <input id="testimonial-dets" type="hidden" name="testimonial-dets" value="<?php echo $quantity . '|' . $group; ?>">
        </div>
        <?php
        $contents = ob_get_clean();
        
        wp_enqueue_script( 'ict-ajax-scripts' );
        
        return $contents;
    }
    
    public function more_testimonials() {
        $dets = explode( '|', $_POST['testimonial-dets'] );
        $group = ( 'All Groups' == $dets[1] ) ? false : $dets[1];
        $testimonials = self::get_testimonials( $dets[0], $group );
        echo json_encode( $testimonials );
        wp_die();
    }
    
    public function get_testimonials( $quantity , $group ) {
        $args = array(
            'post_type' => 'testimonials',
            'orderby' => 'meta_value_num',
            'meta_key' => 'ivycat_testimonial_order',
            'order' => 'DESC',
            'posts_per_page' => $quantity
        );
        
        if ( $group ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'testimonial-group',
                    'field' => 'slug',
                    'terms' => $group
                )
            );
        }
        
        $testimonials = get_posts( $args );
        $testimonial_data = array();
        if ( $testimonials ) {
            foreach( $testimonials as $row ) {
                $testimonial_data[] = array(
                    'testimonial_id' => $row->ID,
                    'testimonial_title' => $row->post_title,
                    'testimonial_content' => $row->post_content
                );
            }
        }
        
        return $testimonial_data;
    }
    
}