<?php

namespace App\Http\Requests\API\V1\Order;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderPlaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Customer information
            'customer_first_name' => 'bail|required|string',
            'customer_last_name' => 'bail|required|string',
            'customer_phone' => 'bail|required|string|min:10|max:10',
            'customer_email' => 'bail|required|string|email',
            'delivery_type' => 'bail|required|string|in:' . implode(',', Order::$deliveryTypes),
            'location_id' => 'bail|required_if:delivery_type,pickup|nullable|exists:locations,id',
            'pickup_date' => 'bail|required_if:delivery_type,pickup|nullable|date',
            'coupon_code' => 'bail|nullable|string|exists:product_coupons,coupon_code',
            // Billing information
            'billing_name' => 'bail|required|string',
            'billing_address' => 'bail|required|string',
            'billing_city' => 'bail|required|string',
            'billing_state' => 'bail|required|string',
            'billing_zip' => 'bail|required|string',
            // Payment information
            'cardholder_name' => 'bail|required|string',
            'card_number' => 'bail|required|string',
            'expiration' => 'bail|required|string',
            'cvv' => 'bail|required|string',
            // Cart items
            'cart_items' => 'bail|required|array',
            'cart_items.*.product_id' => 'bail|required|integer|exists:products,id',
            'cart_items.*.product_variant_id' => 'bail|required|integer|exists:product_variants,id',
            'cart_items.*.quantity' => 'bail|required|integer|min:1',
            'cart_items.*.card_message' => 'bail|nullable|string',
            'cart_items.*.name' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
            ],
            'cart_items.*.phone' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
                'min:10',
                'max:10',
            ],
            'cart_items.*.address' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
            ],
            'cart_items.*.address_1' => 'bail|nullable|string',
            'cart_items.*.city' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
            ],
            'cart_items.*.state_code' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
            ],
            'cart_items.*.zip' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'string',
            ],
            'cart_items.*.lat' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'numeric',
            ],
            'cart_items.*.lng' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'numeric',
            ],
            'cart_items.*.special_instructions' => 'bail|nullable|string',
            'cart_items.*.delivery_date' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'date',
            ],
            'cart_items.*.tax_rate' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'numeric',
            ],
            'cart_items.*.delivery_charge' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'numeric',
            ],
            'cart_items.*.same_as_first_item' => [
                'bail',
                'required_if:delivery_type,delivery',
                'nullable',
                'boolean',
            ],
        ];
    }


    public function attributes(): array
    {
        return [
            'location_id' => 'pickup address',
            'cart_items.*.product_id' => 'product',
            'cart_items.*.product_variant_id' => 'product variant',
            'cart_items.*.quantity' => 'quantity',
            'cart_items.*.card_message' => 'card message',
            'cart_items.*.name' => 'recipient name',
            'cart_items.*.phone' => 'recipient phone',
            'cart_items.*.address' => 'recipient address',
            'cart_items.*.address_1' => 'recipient address 1',
            'cart_items.*.city' => 'recipient city',
            'cart_items.*.state_code' => 'recipient state',
            'cart_items.*.zip' => 'recipient zip',
            'cart_items.*.lat' => 'recipient latitude',
            'cart_items.*.lng' => 'recipient longitude',
            'cart_items.*.special_instructions' => 'special instructions',
            'cart_items.*.delivery_date' => 'delivery date',
            'cart_items.*.tax_rate' => 'tax rate',
            'cart_items.*.delivery_charge' => 'delivery charge',
            'cart_items.*.same_as_first_item' => 'same as first item',
        ];
    }
}
