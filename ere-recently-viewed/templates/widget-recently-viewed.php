<?php
// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}
/**
 * @var $the_query WP_Query
 * @var $image_size
 */
$wrapper_classes = array(
    'ere-property',
    'ere-recently-viewed-properties'
);
$wrapper_class = implode(' ', $wrapper_classes);
?>
<div class="<?php echo esc_attr($wrapper_class) ?>">
    <?php while ($the_query->have_posts()): ?>
        <?php $the_query->the_post(); ?>
        <div class="property-item">
            <div class="property-inner">
                <?php ere_template_loop_property_thumbnail(array(
                    'image_size' => $image_size
                ));?>
                <div class="property-item-content">
                    <?php ere_template_loop_property_title(); ?>
                    <?php ere_template_loop_property_price(); ?>
                    <?php ere_template_loop_property_location(); ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

