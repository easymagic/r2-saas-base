<?php

use Presentation\View\View;
use Product\Data\ProductEntity;

return [

    "product_component" => function (ProductEntity $product) {
        View::render("shop/product/product-unit", [
            'product' => $product
        ], "");
    },

    "product_list_component" => function(array $products){
        // api/views/shop/product/product-list.php
        View::render("shop/product/product-list", [
            'products' => $products
        ], "");
    }





];
