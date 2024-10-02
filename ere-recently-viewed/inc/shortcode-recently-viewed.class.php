<?php
// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}
if (!class_exists('ERE_RV_Shortcode_Recently_Viewed')) {
    class ERE_RV_Shortcode_Recently_Viewed extends ERE_Widget {
        private static $_instance;
        public static function getInstance()
        {
            if (self::$_instance == NULL) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function init() {
            add_action( 'in_widget_form', array($this,'add_shortcode_in_widget_form'), 10, 3 );
            add_shortcode('ere_recently_viewed', array($this,'add_shortcode'));

        }

        public function add_shortcode($atts) {
            $args = array(
                'widget_id' => $atts['widget_id']
            );
            ob_start();
            the_widget('ERE_RV_Widget_Recently_Viewed', '', $args);
            return ob_get_clean();
        }

        public function add_shortcode_in_widget_form($widget, $return, $instance) {
            if (is_a($widget,'ERE_RV_Widget_Recently_Viewed')) {
                $widget_id = str_replace('ere_widget_recently_viewed-', '', $widget->id);
                if ($widget_id != "__i__") {
                    ?>
                    <p>
                        <label class="ere-recently-viewed-shortocde-title"><?php esc_html_e('Shortcode: ','ere-recently-viewed'); ?></label>
                        <label class="ere-recently-viewed-shortocde-name">[ere_recently_viewed widget_id="<?php echo $widget_id; ?>"]</label>
                    </p>
                    <?php
                }
            }
        }
    }
}