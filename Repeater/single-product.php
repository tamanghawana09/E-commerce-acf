<?php

get_header(); // Include header if needed
?>

<h1>Product Search and Filter</h1>
<div class="container">
    <form method="GET" action="<?php echo esc_url(home_url('/page-product')); ?>">
        <input type="text" name="name" placeholder="Product Name"
            value="<?php echo isset($_GET['name']) ? esc_attr($_GET['name']) : ''; ?>">
        <select name="category">
            <option value="">Select Category</option>
            <?php
                $categories = get_terms(array(
                    'taxonomy' => 'product-category',
                    'hide_empty' => false,
                ));
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->term_id) . '"' . (isset($_GET['category']) && $_GET['category'] == $category->term_id ? ' selected' : '') . '>' . esc_html($category->name) . '</option>';
                }
            ?>
        </select>
        <input type="number" name="price" placeholder="Max Price"
            value="<?php echo isset($_GET['price']) ? esc_attr($_GET['price']) : ''; ?>">
        <input type="number" name="rating" placeholder="Min Rating"
            value="<?php echo isset($_GET['rating']) ? esc_attr($_GET['rating']) : ''; ?>">
        <button type="submit">Search</button>
    </form>
</div>

<?php
// Construct the query based on the filters
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
);

// Add name search
if (isset($_GET['name']) && !empty($_GET['name'])) {
    $args['s'] = sanitize_text_field($_GET['name']);
}

// Add category filter
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'product-category',
            'field'    => 'term_id',
            'terms'    => intval($_GET['category']),
        ),
    );
}

// Add price filter
if (isset($_GET['price']) && !empty($_GET['price'])) {
    $args['meta_query'][] = array(
        'key'     => 'price',
        'value'   => intval($_GET['price']),
        'compare' => '<=',
        'type'    => 'NUMERIC',
    );
}

// Add rating filter
if (isset($_GET['rating']) && !empty($_GET['rating'])) {
    $args['meta_query'][] = array(
        'key'     => 'rating',
        'value'   => intval($_GET['rating']),
        'compare' => '>=',
        'type'    => 'NUMERIC',
    );
}

// Fetch products based on the query
$query = new WP_Query($args);
echo '<pre>';
print_r($args);
echo '</pre>';
if ($query->have_posts()) {
    echo '<div class="products">';
    while ($query->have_posts()) {
        $query->the_post();

        // Retrieve custom fields
        $product_name = get_the_title();
        $product_price = get_field('price');
        $product_rating = get_field('rating');
        $product_image_url = get_field('product_image'); // Get image URL from custom field

        echo '<div class="product">';
        echo '<h3>' . esc_html($product_name) . '</h3>';

        if ($product_image_url) {
            echo '<img src="' . esc_url($product_image_url) . '" alt="' . esc_attr($product_name) . '">';
        } else {
            echo '<p>No image available</p>';
        }

        if ($product_price) {
            echo '<p>Price: $' . esc_html($product_price) . '</p>';
        }

        if ($product_rating) {
            echo '<p>Rating: ' . esc_html($product_rating) . '/5</p>';
        }

        echo '</div>'; // End product div
    }
    echo '</div>'; // End products div
} else {
    echo '<p>No products found.</p>';
}

// Reset post data
wp_reset_postdata();

get_footer(); // Include footer if needed
?>