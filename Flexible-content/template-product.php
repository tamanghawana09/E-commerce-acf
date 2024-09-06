<?php
/**
 * Template Name: Product Page
 */
get_header();
?>
<div class="container">
    <h1>Product Search and Filter</h1>
    <form action="" method="GET">
        <input type="text" name="product_name" placeholder="Product Name">
        <input type="text" name="price" placeholder="Price">
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
        <input type="number" name="rating" placeholder="Product Rating" min="0" max="10">
        <input type="submit" value="Search">
    </form>
</div>
<?php
$category = isset($_GET['category']) ? $_GET['category'] : '';

//Arguments for the main WP_Query
$query_args = [
    'post_type' => 'product',
    'posts_per_page' => -1, //Fetch all the products
];

if(!empty($_GET['category'])){
    $query_args['tax_query'] =[
        [
            'taxonomy' => 'product-category',
            'field' => 'term_id',
            'terms' => $category,
            'operator' => 'IN'
        ]
        ];
}

$query = new WP_Query($query_args);
if($query->have_posts()){
    echo '<div class="product-cards">';
    while($query->have_posts()){
        $query->the_post();

        //Flag to check if any row matches the filter criteria
        $product_matched = false;
        
        //Loop through each row in the flexible content field and apply filters
        if(have_rows('product_field')){
            while(have_rows('product_field')){
                the_row();
                if(get_row_layout('product_information')){
                    $product_name = get_sub_field('name');
                    $product_price = get_sub_field('price');
                    $product_rating = get_sub_field('rating');
                    $product_image = get_sub_field('image');

                    //Apply filter
                    if(!empty($_GET['product_name']) && stripos($product_name, sanitize_text_field($_GET['product_name'])) === false){
                        continue; 
                    }

                    if(!empty($_GET['price']) && $product_price !=sanitize_text_field($_GET['price'])){
                        continue;
                    }
                    if(!empty($_GET['rating']) && $product_rating != sanitize_text_field($_GET['rating']))
                    {
                        continue;
                    }

                    // If all filters match, set the flag and display the product

                    $product_matched = true;

                    ?>
<div class="product-card">
    <div class="product-image">
        <?php
            if($product_image){
            echo '<img src="' . esc_url($product_image) . '"alt="' .esc_attr($product_name) . '">';
            }
                                                        ?>
    </div>
    <div class="product-details">
        <h2><?php echo esc_html($product_name);?></h2>
        <p>Price: $
            <?php echo esc_html($product_price);?>
        </p>
        <p>Rating:
            <?php echo esc_html($product_rating);?>
        </p>
    </div>
</div>
<?php
}
}
}
if(!$product_matched){
    continue;
}
}
echo '</div>';
}else{
    echo '<p> No products found matching your criteria. </p>';
}
?>
<?php get_footer();?>