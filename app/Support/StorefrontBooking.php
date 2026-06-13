<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class StorefrontBooking
{
    private const SESSION_PREFIX = 'storefront_booking.';

    public function __construct(
        private readonly int $vendorId,
    ) {}

    public static function forVendor(int $vendorId): self
    {
        return new self($vendorId);
    }

    public function isSet(): bool
    {
        return $this->startAt() !== null && $this->endAt() !== null;
    }

    public function startAt(): ?Carbon
    {
        $raw = session($this->sessionKey('start_at'));

        return $this->parse($raw);
    }

    public function endAt(): ?Carbon
    {
        $raw = session($this->sessionKey('end_at'));

        return $this->parse($raw);
    }

    /**
     * @param  array{start_time: string, end_time: string}  $input
     */
    public function save(array $input): void
    {
        $validated = Validator::make($input, [
            'start_time' => ['required', 'date_format:Y-m-d H:i', 'after_or_equal:now'],
            'end_time' => ['required', 'date_format:Y-m-d H:i', 'after:start_time'],
        ])->validate();

        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('Y-m-d H:i', $validated['end_time']);
        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_time' => [__('vendor.store_rental_end_after_start')],
            ]);
        }

        session([
            $this->sessionKey('start_at') => $start->format('Y-m-d H:i'),
            $this->sessionKey('end_at') => $end->format('Y-m-d H:i'),
        ]);
        session()->save();
    }

    public function clear(): void
    {
        session()->forget([
            $this->sessionKey('start_at'),
            $this->sessionKey('end_at'),
        ]);
    }

    /**
     * @return array<string, float>
     */
    public function billingDefaultsByPriceType(): array
    {
        $start = $this->startAt();
        $end = $this->endAt();
        if (! $start || ! $end) {
            return [];
        }

        return StorefrontRentalPricing::defaultBillingUnitsByPriceType($start, $end);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentation(): array
    {
        $start = $this->startAt();
        $end = $this->endAt();

        return [
            'is_set' => $this->isSet(),
            'start_at' => $start?->format('Y-m-d H:i'),
            'end_at' => $end?->format('Y-m-d H:i'),
            'start_label' => $start?->format('d M Y, h:i A'),
            'end_label' => $end?->format('d M Y, h:i A'),
            'rent_days' => ($start && $end) ? max(1, (int) ceil($start->diffInDays($end))) : null,
        ];
    }

    private function sessionKey(string $field): string
    {
        return self::SESSION_PREFIX.$this->vendorId.'.'.$field;
    }

    private function parse(mixed $raw): ?Carbon
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }
}
