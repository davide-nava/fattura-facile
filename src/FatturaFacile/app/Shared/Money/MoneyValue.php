<?php

namespace App\Shared\Money;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable Money Value Object to guarantee financial precision without floating point errors.
 */
final class MoneyValue implements JsonSerializable, Stringable
{
    private string $amount;

    private string $currency;

    private int $scale;

    public function __construct(string|int|float $amount, string $currency = 'CHF', int $scale = 2)
    {
        $this->currency = strtoupper(trim($currency));
        $this->scale = $scale;

        if (is_float($amount)) {
            // Convert float safely to string with high precision then round to scale
            $this->amount = bcadd(number_format($amount, $scale + 4, '.', ''), '0', $scale);
        } else {
            $cleaned = trim((string) $amount);
            if (! is_numeric($cleaned)) {
                throw new InvalidArgumentException("Invalid monetary amount: {$amount}");
            }
            $this->amount = bcadd($cleaned, '0', $scale);
        }
    }

    public static function of(string|int|float $amount, string $currency = 'CHF', int $scale = 2): self
    {
        return new self($amount, $currency, $scale);
    }

    public static function zero(string $currency = 'CHF', int $scale = 2): self
    {
        return new self('0', $currency, $scale);
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function add(self|string|int|float $other): self
    {
        $otherVal = $other instanceof self ? $other : new self($other, $this->currency, $this->scale);
        $this->assertSameCurrency($otherVal);
        $result = bcadd($this->amount, $otherVal->amount, $this->scale);

        return new self($result, $this->currency, $this->scale);
    }

    public function subtract(self|string|int|float $other): self
    {
        $otherVal = $other instanceof self ? $other : new self($other, $this->currency, $this->scale);
        $this->assertSameCurrency($otherVal);
        $result = bcsub($this->amount, $otherVal->amount, $this->scale);

        return new self($result, $this->currency, $this->scale);
    }

    public function multiply(string|int|float $multiplier, int $targetScale = 2): self
    {
        $multStr = (string) $multiplier;
        // Calculate with higher internal precision then round
        $highPrecision = bcmul($this->amount, $multStr, $targetScale + 4);
        $rounded = $this->roundHalfUp($highPrecision, $targetScale);

        return new self($rounded, $this->currency, $targetScale);
    }

    public function divide(string|int|float $divisor, int $targetScale = 2): self
    {
        $divStr = (string) $divisor;
        if (bccomp($divStr, '0', 6) === 0) {
            throw new InvalidArgumentException('Division by zero in Money calculation');
        }
        $highPrecision = bcdiv($this->amount, $divStr, $targetScale + 4);
        $rounded = $this->roundHalfUp($highPrecision, $targetScale);

        return new self($rounded, $this->currency, $targetScale);
    }

    /**
     * Round to nearest 0.05 (Swiss 5-cent cash rounding).
     */
    public function roundSwissFiveCents(): self
    {
        if ($this->currency !== 'CHF') {
            return $this;
        }

        // Formula: round(amount * 20) / 20
        $timesTwenty = bcmul($this->amount, '20', 4);
        $roundedInt = round((float) $timesTwenty);
        $rounded = bcdiv((string) $roundedInt, '20', 2);

        return new self($rounded, $this->currency, 2);
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', $this->scale) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', $this->scale) > 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', $this->scale) < 0;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && bccomp($this->amount, $other->amount, $this->scale) === 0;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, $this->scale) > 0;
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return bccomp($this->amount, $other->amount, $this->scale) < 0;
    }

    public function format(): string
    {
        return number_format((float) $this->amount, $this->scale, '.', "'").' '.$this->currency;
    }

    public function toFloat(): float
    {
        return (float) $this->amount;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format(),
        ];
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}");
        }
    }

    private function roundHalfUp(string $number, int $precision): string
    {
        if (strpos($number, '.') === false) {
            return $number.'.'.str_repeat('0', $precision);
        }

        $parts = explode('.', $number);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        if (strlen($decimalPart) <= $precision) {
            return $integerPart.'.'.str_pad($decimalPart, $precision, '0');
        }

        $checkDigit = (int) ($decimalPart[$precision] ?? 0);
        $targetDecimals = substr($decimalPart, 0, $precision);

        if ($checkDigit >= 5) {
            $increment = '0.'.str_repeat('0', max(0, $precision - 1)).'1';
            if ($precision === 0) {
                $increment = '1';
                $base = $integerPart;
            } else {
                $base = $integerPart.'.'.$targetDecimals;
            }

            return bcadd($base, $increment, $precision);
        }

        return $precision > 0 ? $integerPart.'.'.$targetDecimals : $integerPart;
    }
}
