<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorStorePage extends Model
{
    public const KEY_ABOUT = 'about';

    public const KEY_CONTACT = 'contact';

    public const KEY_FAQ = 'faq';

    public const KEY_RETURNS = 'returns';

    public const KEY_PRIVACY = 'privacy';

    public const KEY_TERMS = 'terms';

    public const KEYS = [
        self::KEY_ABOUT,
        self::KEY_CONTACT,
        self::KEY_FAQ,
        self::KEY_RETURNS,
        self::KEY_PRIVACY,
        self::KEY_TERMS,
    ];

    protected $fillable = [
        'vendor_id',
        'page_key',
        'title',
        'content',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * @return array<string, string|null>
     */
    public static function contentsByKey(int $vendorId): array
    {
        $contents = static::query()
            ->where('vendor_id', $vendorId)
            ->pluck('content', 'page_key')
            ->all();

        $mapped = [];
        foreach (self::KEYS as $key) {
            $mapped[$key] = isset($contents[$key]) ? (string) $contents[$key] : null;
        }

        return $mapped;
    }

    public static function contentFor(int $vendorId, string $pageKey): ?string
    {
        if (! in_array($pageKey, self::KEYS, true)) {
            return null;
        }

        $content = static::query()
            ->where('vendor_id', $vendorId)
            ->where('page_key', $pageKey)
            ->where('is_published', true)
            ->value('content');

        $trimmed = trim((string) $content);

        return $trimmed !== '' ? $trimmed : null;
    }

    public static function saveContent(int $vendorId, string $pageKey, ?string $content): void
    {
        if (! in_array($pageKey, self::KEYS, true)) {
            return;
        }

        $content = $content !== null ? trim($content) : null;
        if ($content === '') {
            $content = null;
        }

        if ($content === null) {
            static::query()
                ->where('vendor_id', $vendorId)
                ->where('page_key', $pageKey)
                ->delete();

            return;
        }

        static::query()->updateOrCreate(
            [
                'vendor_id' => $vendorId,
                'page_key' => $pageKey,
            ],
            [
                'content' => $content,
                'is_published' => true,
                'sort_order' => self::defaultSortOrder($pageKey),
            ]
        );
    }

    public static function defaultSortOrder(string $pageKey): int
    {
        return match ($pageKey) {
            self::KEY_ABOUT => 10,
            self::KEY_CONTACT => 20,
            self::KEY_FAQ => 30,
            self::KEY_RETURNS => 40,
            self::KEY_PRIVACY => 50,
            self::KEY_TERMS => 60,
            default => 0,
        };
    }
}
