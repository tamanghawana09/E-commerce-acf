<?php
get_header(); ?>

<div class="container">
    <h1>Product Search and Filter</h1>
    <form action="" method="POST">
        <input type="text" name="name" placeholder="Product Name">
        <input type="text" name="price" placeholder="Max Price">
        <select name="category" id="product-category">
            <option value="">Select category</option>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'product-category',
                'hide_empty' => false,
            ));
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '"' . (isset($_POST['category']) && $_POST['category'] == $category->term_id ? ' selected' : '') . '>' . esc_html($category->name) . '</option>';
            }
            ?>
        </select>
        <input type="number" name="rating" placeholder="Min Rating" min="0" max="10">
        <input type="submit" value="Search">
    </form>
</div>

<?php
// Initialize variables
$price = isset($_POST['price']) ? $_POST['price'] : '';
$rating = isset($_POST['rating']) ? $_POST['rating'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';

// Initialize meta_query
$meta_query = array('relation' => 'AND');
if ($price) {
    $meta_query[] = array(
        'key' => 'price',
        'value' => $price,
        'compare' => '<=',
        'type' => 'NUMERIC'
    );
}
if ($rating) {
    $meta_query[] = array(
        'key' => 'rating',
        'value' => $rating,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// WP_Query arguments
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'meta_query' => !empty($meta_query) ? $meta_query : null,
    'tax_query' => !empty($category) ? array(
        array(
            'taxonomy' => 'product-category',
            'field' => 'term_id',
            'terms' => $category,
            'operator' => 'IN'
        )
    ) : array(), 's' => $name
);
echo '<pre>';
print_r($args);
echo '</pre>';

$query = new WP_Query($args);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
?>
<div class="product-item">
    <h2><?php echo get_the_title(); ?></h2>
    <?php
            if (have_rows('product_field')):
                while (have_rows('product_field')): the_row();
                    if (get_row_layout() == 'product_information'):
                        $name = get_sub_field('name');
                        $price = get_sub_field('price');
                        $rating = get_sub_field('rating');
            ?>
    <p>Name: <?php echo esc_html($name);?> </p>
    <p>Price: <?php echo esc_html($price); ?>
    </p>
    <p>Rating: <?php echo esc_html($rating); ?></p>
    <?php endif;
                endwhile;
            endif; ?>
</div>
<?php
    }
} else {
    echo 'No products found';
}
wp_reset_postdata();
?>

<?php get_footer(); ?>