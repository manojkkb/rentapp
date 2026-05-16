<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Input model for the vendor direct-order wizard (event title, customer, rental window).
 *
 * Accepts either `event_name` or `cart_name` for the title for backward-compatible request bodies.
 */
final class CreateOrder
{
    public function __construct(
        public readonly string $eventName,
        public readonly ?int $customerId,
        public readonly ?Carbon $startAt,
        public readonly ?Carbon $endAt,
    ) {}

    /**
     * Validate and build for creating an order without a cart (event, customer, booking window required).
     */
    public static function validateForDirectOrder(Request $request, int $vendorId): self
    {
        $validator = Validator::make($request->all(), self::rulesForDirectOrder($vendorId));
        $validator->after(function ($v): void {
            $data = $v->getData();
            $title = self::resolveEventTitle($data);
            if ($title === '') {
                $v->errors()->add('event_name', trans('validation.required', ['attribute' => 'event name']));
            }
        });
        $validator->validate();
        $data = $validator->validated();

        return self::fromResolvedTitle(
            self::resolveEventTitle($data),
            isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            $data['start_time'] ?? null,
            $data['end_time'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesForDirectOrder(int $vendorId): array
    {
        return [
            'customer_id' => [
                'required',
                Rule::exists('vendor_customers', 'id')->where('vendor_id', $vendorId),
            ],
            'event_name' => ['nullable', 'string', 'max:255'],
            'cart_name' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ];
    }

    /**
     * Persistable attributes for a new {@see Order} (no order number or vendor id).
     *
     * @return array<string, mixed>
     */
    public function toDirectOrderAttributes(): array
    {
        if ($this->customerId === null || $this->startAt === null || $this->endAt === null) {
            throw new \InvalidArgumentException('Direct orders require customerId, startAt, and endAt.');
        }

        return [
            'event_name' => $this->eventName,
            'customer_id' => $this->customerId,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'fulfillment_type' => 'pickup',
            'delivery_address' => null,
            'pickup_at' => null,
            'delivery_charge' => 0,
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount' => 0,
            'security_deposit' => 0,
            'security_deposit_type' => 'none',
            'security_deposit_value' => null,
            'token_amount' => 0,
            'payment_detail' => [],
            'sub_total' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => 0,
            'extra_charges_total' => 0,
            'extra_charges_lines' => [],
            'paid_amount' => 0,
            'status' => 'pending',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolveEventTitle(array $data): string
    {
        $fromEvent = trim((string) ($data['event_name'] ?? ''));
        if ($fromEvent !== '') {
            return $fromEvent;
        }

        return trim((string) ($data['cart_name'] ?? ''));
    }

    private static function fromResolvedTitle(
        string $eventTitle,
        ?int $customerId,
        mixed $start,
        mixed $end,
    ): self {
        $startAt = $start !== null && $start !== '' ? Carbon::parse($start) : null;
        $endAt = $end !== null && $end !== '' ? Carbon::parse($end) : null;

        return new self($eventTitle, $customerId, $startAt, $endAt);
    }
}
