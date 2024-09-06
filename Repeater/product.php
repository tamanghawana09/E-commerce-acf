<?php
/*
Template Name: Product Filter
*/

get_header();
?>

<!-- Search and Filter Form -->
<form action="" method="GET" class="filter-form">
    <label for="product_name">Product Name:</label>
    <input type="text" name="product_name" id="product_name" placeholder="Enter product name"
        value="<?php echo isset($_GET['product_name']) ? esc_attr($_GET['product_name']) : ''; ?>">

    <label for="category">Category:</label>
    <select name="category" id="category">
        <option value="">All Categories</option>
        <?php
        $categories = get_terms(['taxonomy' => 'product_category', 'hide_empty' => false]);
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category->slug) . '" ' . selected($_GET['category'], $category->slug, false) . '>' . esc_html($category->name) . '</option>';
        }
        ?>
    </select>

    <label for="price">Price:</label>
    <input type="number" name="price" id="price" placeholder="Enter price"
        value="<?php echo isset($_GET['price']) ? esc_attr($_GET['price']) : ''; ?>">

    <label for="rating">Rating:</label>
    <input type="number" name="rating" id="rating" placeholder="Enter rating (1-10)"
        value="<?php echo isset($_GET['rating']) ? esc_attr($_GET['rating']) : ''; ?>" min="1" max="10">

    <button type="submit">Filter</button>
</form>

<!-- Custom WP_Query for Filtering Products -->
<?php
// Arguments for the main WP_Query
$query_args = [
    'post_type'      => 'product',
    'posts_per_page' => -1, // Fetch all matching products
];

if (!empty($_GET['category'])) {
    $query_args['tax_query'] = [
        [
            'taxonomy' => 'product_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_GET['category']),
        ]
    ];
}

// The Query
$query = new WP_Query($query_args);

if ($query->have_posts()) {
    echo '<div class="product-cards">';
    while ($query->have_posts()) {
        $query->the_post();

        // Flag to check if any row matches the filter criteria
        $product_matched = false;

        // Loop through each row in the repeater field and apply filters
        if (have_rows('product_information')) {
            while (have_rows('product_information')) {
                the_row();

                $product_name  = get_sub_field('name');
                $product_price = get_sub_field('price');
                $product_rating = get_sub_field('rating');
                $product_image = get_sub_field('product_image');

                // Apply Name Filter
                if (!empty($_GET['product_name']) && stripos($product_name, sanitize_text_field($_GET['product_name'])) === false) {
                    continue; // Skip this row if name does not match
                }

                // Apply Price Filter
                if (!empty($_GET['price']) && $product_price != sanitize_text_field($_GET['price'])) {
                    continue; // Skip this row if price does not match
                }

                // Apply Rating Filter
                if (!empty($_GET['rating']) && $product_rating != sanitize_text_field($_GET['rating'])) {
                    continue; // Skip this row if rating does not match
                }

                // If all filters match, set the flag and display the product
                $product_matched = true;
                echo '<div class="product-card">';
                echo '<div class="product-image">';
                if ($product_image) {
                    echo '<img src="' . esc_url($product_image) . '" alt="' . esc_attr($product_name) . '">';
                }
                echo '</div>';
                echo '<div class="product-details">';
                echo '<h2>' . esc_html($product_name) . '</h2>';
                echo '<p>Price: $' . esc_html($product_price) . '</p>';
                echo '<p>Rating: ' . esc_html($product_rating) . ' Stars</p>';
                echo '</div>';
                echo '</div>';
            }
        }

        // If no row matched, skip this product
        if (!$product_matched) {
            continue;
        }
    }
    echo '</div>';
} else {
    echo '<p>No products found matching your criteria.</p>';
}

// Reset Post Data
wp_reset_postdata();
?>

<?php get_footer(); ?>