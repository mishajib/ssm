<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductVariant;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $products = ProductService::getProductListings($request, $perPage)->response()->getData(true);

        return success_response(
            'Products retrieved successfully.',
            [
                'products' => $products,
                'filters' => [
                    'availability' => $request->get('availability'),
                    'sortBy' => $request->get('sort_by'),
                    'priceRange' => $request->get('price_range'),
                    'perPage' => $perPage,
                    'search' => $request->get('search'),
                    'maxPriceRange' => ProductVariant::max('price'),
                ]
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'variations' => function ($query) {
                $query->active();
            },
            'category'
        ]);

        return success_response(
            'Product retrieved successfully.',
            (new ProductResource($product))->resolve()
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function productCategories()
    {
        $categories = ProductService::getAllCategories();

        return success_response(
            'Product categories retrieved successfully.',
            ProductCategoryResource::collection($categories)
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function categoryProducts(Request $request, ProductCategory $productCategory)
    {
        $perPage = $request->get('per_page', 10);

        $products = ProductService::getProductListings($request, $perPage, $productCategory->id)->response()->getData(true);

        return success_response(
            'Category products retrieved successfully.',
            [
                'category' => new ProductCategoryResource($productCategory),
                'products' => $products,
                'filters' => [
                    'availability' => $request->get('availability'),
                    'sortBy' => $request->get('sort_by'),
                    'priceRange' => $request->get('price_range'),
                    'perPage' => $perPage,
                    'search' => $request->get('search'),
                    'maxPriceRange' => ProductVariant::max('price'),
                ]
            ]
        );
    }


    public function wishlists(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $data['products'] = ProductService::getProductListings($request, $perPage, null, $user->id)->response()->getData(true);
        $data['filters'] = [
            'availability' => $request->get('availability'),
            'sortBy' => $request->get('sort_by'),
            'priceRange' => $request->get('price_range'),
            'perPage' => $perPage,
            'search' => $request->get('search'),
            'maxPriceRange' => ProductVariant::max('price'),
        ];

        return success_response(
            'Wishlists retrieved successfully.',
            $data
        );
    }

    public function favouriteToggle(Request $request, Product $product)
    {
        $product->users()->toggle($request->user()->id);

        $product->load([
            'createdBy',
            'updatedBy',
            'category',
            'variations'
        ]);

        $message = $product->users()->find($request->user()->id) ? 'Product added to wishlist.' : 'Product removed from wishlist.';

        return success_response(
            $message,
            (new ProductResource($product))->resolve()
        );
    }
}
