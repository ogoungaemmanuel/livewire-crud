<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Enums;

/**
 * Common record-status enum used by generated models and BadgeColumn.
 *
 * Backed by string so it can be stored directly in a varchar/enum DB column.
 * Every case ships with a Bootstrap 5 colour token, a human label, and a
 * Bootstrap Icons class — all of which the BadgeColumn reads automatically
 * when the model's status property is cast to this enum.
 */
enum Status: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
    case Draft    = 'draft';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';
    case Suspended = 'suspended';

    // -----------------------------------------------------------------------
    // Metadata
    // -----------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Inactive  => 'Inactive',
            self::Pending   => 'Pending',
            self::Draft     => 'Draft',
            self::Approved  => 'Approved',
            self::Rejected  => 'Rejected',
            self::Archived  => 'Archived',
            self::Suspended => 'Suspended',
        };
    }

    /** Bootstrap 5 contextual colour token (used in badge / border CSS classes). */
    public function color(): string
    {
        return match ($this) {
            self::Active    => 'success',
            self::Inactive  => 'secondary',
            self::Pending   => 'warning',
            self::Draft     => 'light',
            self::Approved  => 'primary',
            self::Rejected  => 'danger',
            self::Archived  => 'dark',
            self::Suspended => 'danger',
        };
    }

    /** Bootstrap Icons class for the status indicator. */
    public function icon(): string
    {
        return match ($this) {
            self::Active    => 'bi-check-circle-fill',
            self::Inactive  => 'bi-dash-circle',
            self::Pending   => 'bi-clock',
            self::Draft     => 'bi-pencil-square',
            self::Approved  => 'bi-patch-check-fill',
            self::Rejected  => 'bi-x-circle-fill',
            self::Archived  => 'bi-archive',
            self::Suspended => 'bi-slash-circle',
        };
    }

    /** Whether records in this status are "active" for business logic purposes. */
    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Approved], true);
    }

    // -----------------------------------------------------------------------
    // Collection helpers
    // -----------------------------------------------------------------------

    /** @return array<string, string>  value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }
        return $out;
    }

    /**
     * Build the colors map expected by BadgeColumn::colors().
     *
     * @return array<string, string>  value => Bootstrap colour token
     */
    public static function colorMap(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->color();
        }
        return $out;
    }

    /**
     * Build the icons map expected by BadgeColumn::icons().
     *
     * @return array<string, string>  value => Bootstrap icon class
     */
    public static function iconMap(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->icon();
        }
        return $out;
    }

    /** @return array<string> All backing values */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
