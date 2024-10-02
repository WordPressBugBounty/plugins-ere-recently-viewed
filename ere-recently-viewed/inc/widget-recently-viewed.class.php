<?php
// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}
if (!class_exists('ERE_RV_Widget_Recently_Viewed')) {
    class ERE_RV_Widget_Recently_Viewed extends ERE_Widget {
        public function __construct() {
            $this->widget_cssclass    = 'ere_widget ere_widget_recently_viewed';
            $this->widget_description = esc_html__( "Display recent viewed properties by a visitor as a responsive sidebar widget or in page/post using shortcode.", 'ere-recently-viewed' );
            $this->widget_id          = 'ere_widget_recently_viewed';
            $this->widget_name        = esc_html__( 'ERE Recently Viewed Properties', 'ere-recently-viewed' );
            $this->settings           = array(
                'title'           => array(
                    'type'  => 'text',
                    'std'   => esc_html__( 'Recently Viewed Properties', 'ere-recently-viewed' ),
                    'label' => esc_html__( 'Title', 'ere-recently-viewed' )
                ),
                'totalproperties'          => array(
                    'type'  => 'number',
                    'std'   => 5,
                    'label' => esc_html__( 'Number of Properties', 'ere-recently-viewed' )
                ),
                'width'          => array(
                    'type'  => 'number',
                    'std'   => 370,
                    'label' => esc_html__( 'Thumbnail Width', 'ere-recently-viewed' ),
                ),
                'height'          => array(
                    'type'  => 'number',
                    'std'   => 180,
                    'label' => esc_html__( 'Thumbnail Height', 'ere-recently-viewed' )
                ),
            );
            parent::__construct();
        }

        /**
         * Output widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget( $args, $instance ) {
            $widget_id = $args['widget_id'];
            $widget_id = str_replace('ere_widget_recently_viewed-', '', $widget_id);
            $widgetOptions = get_option($this->option_name);
            $instance = $widgetOptions[$widget_id];

            $posts_per_page = (!empty($instance['totalproperties'])) ? absint($instance['totalproperties']) : 5;
            $width = empty($instance['width']) ? '370' : apply_filters('ere_widget_recently_viewed_image_width', $instance['width']);
            $height = empty($instance['height']) ? '180' : apply_filters('ere_widget_recently_viewed_image_height', $instance['height']);
            wp_enqueue_style(ERE_PLUGIN_PREFIX . 'property');
            $property_id = get_the_ID();
            $property_ids = ERE_RV()->get_list_recently_viewed_property();
            $property_ids = array_diff($property_ids, array($property_id));
            if (empty($property_ids)) {
                return;
            }

            $query_args = array(
                'posts_per_page'      => $posts_per_page,
                'no_found_rows'       => true,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true,
                'post_type'           => 'property',
                'post__in' => array_map('absint',$property_ids),
                'orderby' => 'post__in'
            );
            $image_size = "{$width}x{$height}";
            $the_query = new WP_Query($query_args);

            if ($the_query->have_posts()) {
                $this->widget_start( $args, $instance );
                ERE_RV()->get_template('widget-recently-viewed.php',array(
                    'the_query' => $the_query,
                    'image_size' => $image_size
                ));
                $this->widget_end( $args );
            }
            wp_reset_postdata();
        }
    }
}
