<?php
//short code for complete catalog page
function rent_grid_shortcode()
{
    ob_start();
    $grid_contents = (new RentMy_Config())->store_grid_contents();
    $contents = $grid_contents['0']['contents'];
    $total = count($contents);
    // [text] => Viva camera one
    // [id] => 2660
    // [url] => https://s3.us-east-2.amazonaws.com/images.rentmy.co/content-image/590/3tu7x93_1632242701_sfikhc2.jpg
    // [label] => Viva camera 
    // [link] => product/b9b3ec901eae11eb9f7c02caec14a78c/pickup-test-asset
    // [btn_text] => Shop Now
    // [type] => Product
    // [content_id] => 160729   

    // echo "<pre>";
    // print_r($contents);
    // die();
?>


    <section class="singleshop-section mt-3">
        <div class="container-fluid">
            <div class="row">
                <?php if ($total) : ?>
                    <?php foreach ($contents as $content) : ?>
                        <?php
                        /**
                         * Link Generating (4 types available)
                         * product
                         * tag
                         * category
                         * external
                         */
                        $target = "";
                        $link = "";
                        $uid = explode("/", $content['link'])[1];
                        if (strtolower($content['type']) == 'product') {
                            $link = getRentMyParmalink('rentmy.page_url.product_details') . '?uid=' . $uid;
                        }
                        if (strtolower($content['type']) == 'tag') {
                            $link = getRentMyParmalink('rentmy.page_url.products_list') . '?tags=' . $content['content_id'];
                        }
                        if (strtolower($content['type']) == 'category') {
                            $link = getRentMyParmalink('rentmy.page_url.products_list') . '?uid=' . $uid;
                        }
                        if (strtolower($content['type']) == 'external') {
                            $link = $content['link'];
                            $target = "_blank";
                        }

                        ?>
                        <div class="col-md-4 col-sm-12">
                            <div class="w-100 single-shop">
                                <div class="row align-items-center">
                                    <div class="col-md-6 col-sm-4 singleshope-imgarea">
                                        <a target="<?php echo esc_attr($target) ?>" href="<?php echo esc_attr($link) ?>">
                                            <img src="<?php _e($content['url']) ?>" class="img-fluid">
                                        </a>
                                    </div>
                                    <div class="col-md-6 col-sm-8">
                                        <a target="<?php echo esc_attr($target) ?>" href="<?php echo esc_attr($link) ?>">
                                            <h4><?php _e($content['label']) ?></h4>
                                        </a>
                                        <p><?php _e($content['text']) ?></p>
                                        <a target="<?php echo esc_attr($target) ?>" href="<?php echo esc_attr($link) ?>"><?php _e($content['btn_text']) ?> <i class="fa fa-long-arrow-right"></i> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

            </div>
        </div>
    </section>



<?php
    return ob_get_clean();
}

add_shortcode('rentmy-grid', 'rent_grid_shortcode');
