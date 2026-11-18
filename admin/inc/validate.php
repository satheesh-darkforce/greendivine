<?php
/**
 * V — a small fluent validator shared by every module's save handler.
 * Every rule is a no-op once a field already has an error, so the first
 * failure per field is the one shown (no piling up "required" AND
 * "too long" on the same empty field).
 */
final class V
{
    private array $errors = [];

    public function ok(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    private function has(string $field): bool { return isset($this->errors[$field]); }

    public function required(string $field, $value, string $label): static {
        if ($this->has($field)) return $this;
        $empty = $value === null || $value === '' || (is_array($value) && count($value) === 0);
        if ($empty) $this->errors[$field] = "$label is required.";
        return $this;
    }

    public function maxlen(string $field, ?string $value, int $max, string $label): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        if (mb_strlen($value) > $max) $this->errors[$field] = "$label must be $max characters or fewer.";
        return $this;
    }

    public function pattern(string $field, ?string $value, string $regex, string $label, string $hint = ''): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        if (!preg_match($regex, $value)) $this->errors[$field] = "$label " . ($hint ?: 'is not in a valid format') . '.';
        return $this;
    }

    public function email(string $field, ?string $value, string $label): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) $this->errors[$field] = "$label must be a valid email address.";
        return $this;
    }

    /** Absolute http(s) URL — '#' and '' are both allowed as "not linked yet" placeholders. */
    public function url(string $field, ?string $value, string $label): static {
        if ($this->has($field) || $value === null || $value === '' || $value === '#') return $this;
        if (!preg_match('#^https?://#i', $value) && !str_starts_with($value, '/')) {
            $this->errors[$field] = "$label must be a full URL (https://...) or an internal path (/...).";
        }
        return $this;
    }

    public function intRange(string $field, $value, int $min, int $max, string $label): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        if (!is_numeric($value) || (int)$value != $value || (int)$value < $min || (int)$value > $max) {
            $this->errors[$field] = "$label must be a whole number between $min and $max.";
        }
        return $this;
    }

    public function oneOf(string $field, $value, array $allowed, string $label): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        // Compare as strings: PHP silently casts numeric-looking array keys (e.g. '1' => ...)
        // to real integers, so a strict in_array() against string $_POST values would
        // otherwise reject every valid numeric option.
        if (!in_array((string)$value, array_map('strval', $allowed), true)) {
            $this->errors[$field] = "$label is not a recognised value.";
        }
        return $this;
    }

    /** Slug format: lowercase letters, numbers, hyphens only. */
    public function slug(string $field, ?string $value, string $label): static {
        return $this->pattern($field, $value, '/^[a-z0-9_]+(-[a-z0-9_]+)*$/', $label, 'may only contain lowercase letters, numbers, hyphens and underscores');
    }

    /** $existing is the full list of other records' slug values (exclude the one being edited). */
    public function uniqueSlug(string $field, ?string $value, array $existing, string $label): static {
        if ($this->has($field) || $value === null || $value === '') return $this;
        if (in_array($value, $existing, true)) $this->errors[$field] = "$label \"$value\" is already in use.";
        return $this;
    }

    public function exactCount(string $field, array $value, int $count, string $label): static {
        if ($this->has($field)) return $this;
        if (count($value) !== $count) $this->errors[$field] = "$label must have exactly $count entries.";
        return $this;
    }

    public function minCount(string $field, array $value, int $min, string $label): static {
        if ($this->has($field)) return $this;
        if (count($value) < $min) $this->errors[$field] = "$label needs at least $min " . ($min === 1 ? 'entry' : 'entries') . '.';
        return $this;
    }

    /** Manually attach an error (for checks too specific for a generic rule). */
    public function fail(string $field, string $message): static {
        if (!$this->has($field)) $this->errors[$field] = $message;
        return $this;
    }
}

/** URL/filename-safe slug suggestion from a title — mirrors the JS live-suggest, used server-side as a fallback. */
function gd_slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/** Split textarea lines / comma-ish lists into a clean string[] (used for simple repeatable text lists). */
function gd_lines_to_list(?string $raw): array {
    if ($raw === null) return [];
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    return array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
}
