<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductReviewsController extends Controller
{
    public function index(Request $request)
    {
        $data['user'] = $request->user('sanctum');
        $data['toReviews'] = OrderItemResource::collection(
            OrderItem::with([
                'product',
                'product.category',
                'productVariant',
                'productCategory',
                'shippingAddress'
            ])->whereDoesntHave('review')
                ->whereHas('order', function ($query) use ($data) {
                    $query->whereCustomerId($data['user']->id)
                        ->orWhere('customer_email', $data['user']->email);
                })
                ->latest()->paginate(10)
        )->response()->getData(true);

        return success_response(
            'For review products fetched successfully.',
            $data
        );
    }

    public function reviewed(Request $request)
    {
        $data['user'] = $request->user('sanctum');
        $data['reviewedItems'] = OrderItemResource::collection(
            OrderItem::with([
                'product',
                'product.category',
                'productVariant',
                'productCategory',
                'shippingAddress',
                'review'
            ])->whereHas('review')
                ->whereHas('order', function ($query) use ($data) {
                    $query->whereCustomerId($data['user']->id)
                        ->orWhere('customer_email', $data['user']->email);
                })
                ->latest()->paginate(10)
        )->response()->getData(true);
        return success_response(
            'Reviewed products fetched successfully.',

            $data
        );
    }

    public function reviewPage(Request $request, OrderItem $orderItem)
    {
        $orderItem->load([
            'product',
            'product.category',
            'productVariant',
            'productCategory',
            'shippingAddress'
        ]);

        $data['user'] = $request->user('sanctum');
        $data['orderItem'] = (new OrderItemResource($orderItem))->resolve();

        return success_response(
            'Review page data fetched successfully.',
            $data
        );
    }

    public function reviewStore(Request $request, OrderItem $orderItem)
    {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:100',
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|min:3|max:300',
            'is_anonymous' => 'nullable|boolean',
        ], [], [
            'is_anonymous' => 'show username',
        ]);

        $user = $request->user('sanctum');
        $data['order_id'] = $orderItem->order_id;
        $data['product_id'] = $orderItem->product_id;
        $data['product_variant_id'] = $orderItem->product_variant_id;
        $data['product_title'] = $orderItem->product_title;
        $data['user_id'] = $user->id;
        $data['variant_name'] = $orderItem->variant_name;
        $data['customer_name'] = $user->name;
        $data['customer_email'] = $user->email;


        $review = $orderItem->review()->create($data);

        forgetCache('product_reviews');;

        return success_response(
            'Review added successfully.',
            [
                'review' => $review
            ]
        );
    }

    public function reviewEditPage(Request $request, OrderItem $orderItem)
    {
        $orderItem->load([
            'product',
            'product.category',
            'productVariant',
            'productCategory',
            'shippingAddress',
            'review'
        ]);

        $data['user'] = $request->user('sanctum');
        $data['orderItem'] = (new OrderItemResource($orderItem))->resolve();
        return success_response(
            'Review edit page data fetched successfully.',
            $data
        );
    }

    public function reviewUpdate(Request $request, OrderItem $orderItem)
    {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:100',
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|min:3|max:300',
            'is_anonymous' => 'nullable|boolean',
        ], [], [
            'is_anonymous' => 'show username',
        ]);

        $user = $request->user('sanctum');
        $data['order_id'] = $orderItem->order_id;
        $data['product_id'] = $orderItem->product_id;
        $data['product_variant_id'] = $orderItem->product_variant_id;
        $data['product_title'] = $orderItem->product_title;
        $data['user_id'] = $user->id;
        $data['variant_name'] = $orderItem->variant_name;
        $data['customer_name'] = $user->name;
        $data['customer_email'] = $user->email;

        $orderItem->review()->update($data);

        forgetCache('product_reviews');

        return success_response(
            'Review updated successfully.',
            [
                'review' => $orderItem->review
            ]
        );
    }
}
