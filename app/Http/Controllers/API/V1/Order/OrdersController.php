<?php

namespace App\Http\Controllers\API\V1\Order;

use app\Exceptions\AuthorizePaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Order\OrderPlaceRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderStatusResource;
use App\Http\Resources\ProductCouponResource;
use App\Http\Resources\UserListingResource;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product\Product;
use App\Models\Product\ProductCoupon;
use App\Models\Product\ProductVariant;
use App\Models\State;
use App\Services\AuthorizePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data['user']                   = $request->user('sanctum');
        $data['orders']                 = OrderResource::collection(
            Order::with([
                'orderTransaction',
                'customer',
                'location'
            ])->whereCustomerId($data['user']->id)
                ->orWhere('customer_email', $data['user']->email)
                ->latest()->paginate(10)
        )->response()->getData(true);

        $data['orders_grand_total_sum'] = Order::whereCustomerId($data['user']->id)
            ->orWhere('customer_email', $data['user']->email)
            ->sum('grand_total');

        return success_response(
            'Orders fetched successfully!',
            $data
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $data['user']       = $request->user('sanctum');
        $order->load([
            'orderItems',
            'orderTransaction',
            'customer',
            'location',
            'orderItems.product',
            'orderItems.product.category',
            'orderItems.productVariant',
            'orderItems.productCategory',
            'orderItems.shippingAddress'
        ]);
        $data['order'] = (new OrderResource($order))->resolve();

        return success_response(
            'Order details fetched successfully!',
            $data
        );
    }

    public function placeOrder(OrderPlaceRequest $request)
    {
        try {
            DB::beginTransaction();
            // Validate payment data
            $requestData = $request->validated();

            // Get all data from session
            $cartItems = (array)$requestData['cart_items'];
            $deliveryType = $requestData['delivery_type'];
            $cartCustomerInfo = [
                'first_name' => $requestData['customer_first_name'],
                'last_name' => $requestData['customer_last_name'],
                'phone' => $requestData['customer_phone'],
                'email' => $requestData['customer_email'],
            ];
            $couponCode = $requestData['coupon_code'] ?? null;
            $coupon = ProductCoupon::whereCouponCode($couponCode)->first();

            //Place order & Create Order
            $orderData = [
                'order_number' => 'ORD-' . mt_rand(100000, 999999),
                'customer_id' => auth('sanctum')->id(),
                'delivery_type' => $deliveryType ?? Order::DELIVERY_TYPE_PICKUP,
                'sub_total' => 0,
                'coupon_code' => $couponCode ?? null,
                'coupon_amount' => $coupon->amount ?? 0,
                'discount_type' => $coupon->type ?? null,
                'final_discount' => 0,
                'delivery_charges' => 0,
                'grand_total' => 0,
                'customer_first_name' => $cartCustomerInfo['first_name'] ?? null,
                'customer_last_name' => $cartCustomerInfo['last_name'] ?? null,
                'customer_phone' => $cartCustomerInfo['phone'] ?? null,
                'customer_email' => $cartCustomerInfo['email'] ?? null,
                'billing_name' => $requestData['billing_name'],
                'billing_address' => $requestData['billing_address'],
                'billing_city' => $requestData['billing_city'],
                'billing_state' => $requestData['billing_state'],
                'billing_zip' => $requestData['billing_zip'],
                'location_id' => $requestData['location_id'] ?? null,
                'pickup_date' => $requestData['pickup_date'] ?? null,
                'tax_amount' => 0,
            ];

            // Get order status and set it to order
            $orderStatus = OrderStatus::whereStatusText(OrderStatus::STATUS_NEW)->first();
            if ($orderStatus) {
                $orderData['order_status_id'] = $orderStatus->id;
                $orderData['order_status'] = $orderStatus->status_text;
                $orderData['order_status_display_text'] = $orderStatus->display_text;
                $orderData['order_status_description'] = $orderStatus->description;
            }

            $order = Order::create($orderData);

            if ($order->order_status_id) {
                $order->orderStages()->create([
                    'order_status_id' => $order->order_status_id,
                    'status_text' => $order->order_status,
                    'status_display_text' => $order->order_status_display_text,
                    'comment' => $order->order_status_description,
                    'created_by' => auth('sanctum')->id(),
                    'updated_by' => auth('sanctum')->id(),
                ]);
            }

            //Create order items
            if (count($cartItems)) {
                foreach ($cartItems as $cartItem) {
                    $product = Product::find($cartItem['product_id']);
                    $productVariant = ProductVariant::find($cartItem['product_variant_id']);
                    $orderItemData = [
                        'order_id' => $order->id,
                        'product_id' => $product?->id,
                        'product_title' => $product?->title,
                        'product_sku' => $product?->sku,
                        'product_variant_id' => $cartItem['product_variant_id'],
                        'variant_name' => $productVariant?->variation?->name,
                        'product_category_id' => $product?->product_category_id,
                        'card_message' => $cartItem['card_message'] ?? null,
                        'price' => $productVariant?->price,
                        'quantity' => $cartItem['quantity'],
                        'total' => $productVariant?->price * $cartItem['quantity'],
                        'category_name' => $product?->category?->name,
                    ];
                    // Create order item
                    $newOrderItem = $order->orderItems()->create($orderItemData);

                    // Create shipping address for each item if delivery type is delivery
                    if ($deliveryType == Order::DELIVERY_TYPE_DELIVERY) {
                        $state = State::whereCode($cartItem['state_code'])->first();
                        $itemShipAddr = [
                            'name' => $cartItem['name'],
                            'phone' => $cartItem['phone'],
                            'address' => $cartItem['address'],
                            'address_1' => $cartItem['address_1'],
                            'city' => $cartItem['city'],
                            'state_id' => $state?->id,
                            'state_name' => $state?->name,
                            'state_code' => $state?->code,
                            'zip' => $cartItem['zip'],
                            'lat' => $cartItem['lat'],
                            'lng' => $cartItem['lng'],
                            'special_instructions' => $cartItem['special_instructions'],
                            'delivery_date' => $cartItem['delivery_date'],
                            'tax_rate' => $cartItem['tax_rate'],
                            'delivery_charge' => $cartItem['delivery_charge'],
                            'same_as_first_item' => $cartItem['same_as_first_item'],
                            'tax_amount' => $orderItemData['total'] * $cartItem['tax_rate'] / 100,
                        ];
                        $newOrderItem->shippingAddress()->create($itemShipAddr);
                        // delivery charges add if same_as_first_item is false
                        $orderData['delivery_charges'] += $cartItem['same_as_first_item'] ? 0 : $cartItem['delivery_charge'];
                        $orderData['tax_amount'] += $itemShipAddr['tax_amount'];
                    }

                    $orderData['sub_total'] += $orderItemData['total'];
                }
            }

            //Update order total, sub_total, final_discount, grand_total
            if ($coupon) {
                if ($coupon['type'] == ProductCoupon::FIXED) {
                    $orderData['final_discount'] = $coupon['amount'];
                } else {
                    $orderData['final_discount'] = $orderData['sub_total'] * $coupon['amount'] / 100;
                }
            }

            $orderData['grand_total'] = ($orderData['sub_total'] - $orderData['final_discount']) + $orderData['delivery_charges'] + $orderData['tax_amount'];

            $order?->update([
                'sub_total' => $orderData['sub_total'],
                'final_discount' => $orderData['final_discount'],
                'grand_total' => $orderData['grand_total'],
                'delivery_charges' => $orderData['delivery_charges'],
                'tax_amount' => $orderData['tax_amount'],
            ]);

            $paymentData = [
                'billing_name' => $requestData['billing_name'],
                'billing_address' => $requestData['billing_address'],
                'billing_city' => $requestData['billing_city'],
                'billing_state' => $requestData['billing_state'],
                'billing_zip' => $requestData['billing_zip'],
                'cardholder_name' => $requestData['cardholder_name'],
                'card_number' => $requestData['card_number'],
                'expiration' => $requestData['expiration'],
                'cvv' => $requestData['cvv'],
            ];

            // Send email to customer
            Mail::to($cartCustomerInfo['email'])->send(new OrderPlacedMail($order));

            // Send email to admin
            Mail::to(setting('email_address'))->send(new OrderPlacedMail($order, 'admin'));

            // Create payment by payment gateway & create order transaction
            [$orderTransaction, $response] = AuthorizePaymentService::processPayment($paymentData, $order);


            // If user is logged in then update user info
            $authUser = auth('sanctum')->user();
            $authUser?->update([
                'first_name' => $cartCustomerInfo['first_name'],
                'last_name' => $cartCustomerInfo['last_name'],
                'phone' => $cartCustomerInfo['phone'],
            ]);

            // coupon usage_limit decrement
            $coupon?->update([
                'usage_limit' => $coupon->usage_limit - 1,
                'total_used' => $coupon->total_used + 1,
            ]);

            DB::commit();

            $order->load([
                'orderItems',
                'orderTransaction',
                'location',
                'orderStatus',
                'orderStages',
                'orderLatestStage',
                'customer',
                'products',
            ]);

            return success_response(
                'Order placed successfully!',
                [
                    'order' => (new OrderResource($order))->resolve(),
                    'payment_response' => $response,
                ]
            );
        } catch (AuthorizePaymentException $exception) {
            //Rollback all transactions
            DB::rollBack();

            return error_response(
                'Authorize payment error!',
                [
                    'error' => $exception->getMessage() . ' ' . $exception->getTraceAsString(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ]
            );
        } catch (\Exception $exception) {
            //Rollback all transactions
            DB::rollBack();

            return error_response(
                'Something went wrong, please try again!',
                [
                    'error' => $exception->getMessage() . ' ' . $exception->getTraceAsString(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ]
            );
        }
    }

    public function orderTrack(Request $request, $orderNo = null)
    {
        $data['user']       = $request->user('sanctum') ? (new UserListingResource($request->user('sanctum')))->resolve() : null;
        $data['order_no']   = $orderNo;
        $data['order']      = null;
        if ($orderNo) {
            $order = Order::whereOrderNumber($orderNo)->first();
            if ($order) {
                $order->load([
                    'orderItems',
                    'orderTransaction',
                    'customer',
                    'location',
                    'orderItems.product',
                    'orderItems.product.category',
                    'orderItems.productVariant',
                    'orderItems.productCategory',
                    'orderItems.shippingAddress',
                    'orderStatus',
                    'orderStages',
                    'orderLatestStage',
                    'orderStatusStage'
                ]);
                $data['order'] = (new OrderResource($order))->resolve();
            }
        }
        $data['orderStatuses'] = OrderStatusResource::collection(OrderStatus::all())->resolve();

        return success_response('Order details fetched successfully!', $data);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required'
        ]);

        $couponCode = $request->get('coupon_code');

        // Get coupon by coupon code
        $coupon = ProductCoupon::whereCouponCode($couponCode)->first();

        // Check if coupon is valid
        if (!$coupon || !$coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon is invalid!'
            ]);
        }

        // Check if coupon is expired
        if ($coupon->expiry_date < now()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon is expired!'
            ]);
        }

        // Check if coupon is used
        if ($coupon->usage_limit <= $coupon->total_used) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon usage limit is exceeded!'
            ]);
        }

        // Check if coupon is valid for user if user is logged in
        $order = Order::where([
            ['coupon_code', $coupon->coupon_code],
            ['customer_id', auth('sanctum')->id()]
        ])->first();

        if (auth()->check() && $order) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon is already used by you!'
            ]);
        }

        return success_response(
            'Coupon applied successfully!',
            [
                'coupon' => (new ProductCouponResource($coupon))->resolve(),
            ]
        );
    }
}
