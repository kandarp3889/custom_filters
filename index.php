<?php
/**
 * Plugin Name: Custome Filters
 * Description: A plugin to custom filter a product page.
 * Version: 1.0
 * Author: Your Name
 */
function custom_product_filter_enqueue_scripts() { 
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-product-filter-script', plugin_dir_url(__FILE__) . 'js/custom-product-filter-script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('custom-product-filter-style', plugin_dir_url(__FILE__) . 'css/custom-product-filter-style.css');
    wp_localize_script('custom-product-filter-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'custom_product_filter_enqueue_scripts');
// Register the shortcode function
function woocommerce_category_filter_shortcode() {
    // Fetch product categories
    $categories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
    ));

    // Generate HTML output for the filter 
    $output = '<form id="category-filter">';
    $output .= '<div id="filters-container">';
    $output .= '<div class="fst_dv">CATEGORY <span class="toggle-icon fas fa-chevron-down" style="padding-top: 4px;width: 66%;"></span><div class="category-select">';
    
    // Loop through categories to generate checkboxes
    foreach ($categories as $category) {
        $output .=  '<label><input type="checkbox" name="product_cat" value="' . esc_attr($category->slug) . '"> ' . esc_html($category->name) . '</label><br/>';
    }
    
    $output .= '</div></div>';
    $output .= '<div class="sec_dv">SORT BY STATUS <span class="toggle-icon fas fa-chevron-down" style="padding-top: 4px;width: 52%;"></span>';
    $output .= '<div class="sold-select" >';
    $output .= '<label><input type="radio" name="product_status" value="yes"> Sold</label><br/>';
    $output .= '<label><input type="radio" name="product_status" value="no"> Not Sold</label>';
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '<div class="third_dv">SORT BY PRICE <span class="toggle-icon fas fa-chevron-down" style="padding-top: 4px;width: 57%;"></span>';
    $output .= '<div class="price-select" >';
    $output .= '<label><input type="radio" name="product_price" value="high"> High Price</label><br/>';
    $output .= '<label><input type="radio" name="product_price" value="low"> Low Price</label>';
    $output .= '</div>';
    $output .= '</div></div>';
    
    $output .= '</form>';
    

    // Output the generated HTML
    echo $output;
}

add_shortcode('product_category_filter', 'woocommerce_category_filter_shortcode');


function filter_products() {
    // $category_slug = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $category_slugs = isset($_POST['category']) ? array_map('sanitize_text_field', $_POST['category']) : array();
    $sold = isset($_POST['sold']) ? sanitize_text_field($_POST['sold']) : '';
    $price_order = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';

    ob_start();

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => array(
            'relation' => 'AND', // Use 'AND' relation to combine taxonomy queries
        ),
        'meta_query'     => array(
            'relation' => 'AND',
                array(
                    'key'     => 'product_type',
                    'value'   => 'buy_now',
                    'compare' => '=',
                )
            ),
        'orderby'        => 'meta_value_num',
    );

    if (!empty($category_slugs)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $category_slugs, // Use array of category slugs
            'operator' => 'IN', // Use 'IN' operator to include posts in any of the selected categories
        );
    }
        // Handle sold filter
        if ($sold === 'yes') {
            $args['meta_query'][] = array(
                'key'     => '_auction_status',
                'value'   => 'sold',
                'compare' => '=',
            );
        } elseif ($sold === 'no') {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_auction_status',
                    'value'   => 'sold',
                    'compare' => '!=',
                ),
                array(
                    'key'     => '_auction_status',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }
        if ($price_order === 'high') {
            $args['order'] = 'DESC';
            $args['meta_key'] = '_price'; // Assuming product price is stored in a meta field named '_price'
        } elseif ($price_order === 'low') {
            $args['order'] = 'ASC';
            $args['meta_key'] = '_price';
        }
    $products = new WP_Query($args);
        // echo '<pre>';
        // print_r($products);
    if ($products->have_posts()) {
        ?>
        <div class="jet-woo-products jet-woo-products--preset-1 col-row  jet-equal-cols">
            <?php while ($products->have_posts()) : $products->the_post(); ?>
                <div class="jet-woo-products__item jet-woo-builder-product jet-woo-thumb-with-effect">
                    <div class="jet-woo-products__inner-box jet-woo-item-overlay-wrap" data-url="<?php the_permalink(); ?>">
                        <div class="jet-woo-product-thumbnail">
                        
                            <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('thumbnail');?>
                            </a>
                     
                        </div>
                            <h5 class="jet-woo-product-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title()?></a>
                            </h5>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
    } else {
        echo '<p>No products found</p>';
    }

    $html = ob_get_clean();

    echo $html;
    die();
}

add_action('wp_ajax_filter_products', 'filter_products');
add_action('wp_ajax_nopriv_filter_products', 'filter_products'); // Allow non-logged-in users to access the AJAX endpoint

