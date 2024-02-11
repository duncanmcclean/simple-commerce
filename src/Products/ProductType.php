<?php

namespace DuncanMcClean\SimpleCommerce\Products;

enum ProductType: string
{
    case Product = 'product';
    case Variant = 'variant';
}
