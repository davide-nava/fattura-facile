<?php

namespace Tests\Unit;

use App\Shared\Money\MoneyValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyValueTest extends TestCase
{
    public function test_money_addition_preserves_precision(): void
    {
        $m1 = MoneyValue::of('100.12', 'CHF');
        $m2 = MoneyValue::of('50.88', 'CHF');

        $result = $m1->add($m2);

        $this->assertEquals('151.00', $result->getAmount());
        $this->assertEquals('CHF', $result->getCurrency());
    }

    public function test_money_multiplication_with_rounding(): void
    {
        // 19.99 * 3 = 59.97
        $price = MoneyValue::of('19.99', 'CHF');
        $total = $price->multiply('3');

        $this->assertEquals('59.97', $total->getAmount());

        // Tax calculation: 100 * 0.081 (Swiss VAT 8.1%) = 8.10
        $net = MoneyValue::of('100.00', 'CHF');
        $vat = $net->multiply('0.081');

        $this->assertEquals('8.10', $vat->getAmount());
    }

    public function test_swiss_five_cents_rounding(): void
    {
        // 100.02 CHF -> 100.00 CHF
        $m1 = MoneyValue::of('100.02', 'CHF')->roundSwissFiveCents();
        $this->assertEquals('100.00', $m1->getAmount());

        // 100.03 CHF -> 100.05 CHF
        $m2 = MoneyValue::of('100.03', 'CHF')->roundSwissFiveCents();
        $this->assertEquals('100.05', $m2->getAmount());

        // 100.07 CHF -> 100.05 CHF
        $m3 = MoneyValue::of('100.07', 'CHF')->roundSwissFiveCents();
        $this->assertEquals('100.05', $m3->getAmount());

        // 100.08 CHF -> 100.10 CHF
        $m4 = MoneyValue::of('100.08', 'CHF')->roundSwissFiveCents();
        $this->assertEquals('100.10', $m4->getAmount());

        // Non-CHF returns as-is
        $eur = MoneyValue::of('100.02', 'EUR')->roundSwissFiveCents();
        $this->assertEquals('100.02', $eur->getAmount());
    }

    public function test_currency_mismatch_throws_exception(): void
    {
        $chf = MoneyValue::of('10.00', 'CHF');
        $eur = MoneyValue::of('10.00', 'EUR');

        $this->expectException(InvalidArgumentException::class);
        $chf->add($eur);
    }

    public function test_zero_division_throws_exception(): void
    {
        $money = MoneyValue::of('100.00', 'CHF');

        $this->expectException(InvalidArgumentException::class);
        $money->divide('0');
    }

    public function test_float_and_numeric_constructor(): void
    {
        $floatMoney = new MoneyValue(123.456, 'USD', 2);
        $this->assertEquals('123.45', $floatMoney->getAmount());
        $this->assertEquals('USD', $floatMoney->getCurrency());
        $this->assertEquals(2, $floatMoney->getScale());

        $zero = MoneyValue::zero('EUR', 2);
        $this->assertEquals('0.00', $zero->getAmount());
        $this->assertTrue($zero->isZero());
    }

    public function test_invalid_amount_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MoneyValue('invalid_amount');
    }

    public function test_subtraction(): void
    {
        $m1 = MoneyValue::of('100.00', 'CHF');
        $m2 = MoneyValue::of('40.50', 'CHF');

        $result = $m1->subtract($m2);
        $this->assertEquals('59.50', $result->getAmount());

        $resultScalar = $m1->subtract('30.00');
        $this->assertEquals('70.00', $resultScalar->getAmount());
    }

    public function test_addition_with_scalar(): void
    {
        $m1 = MoneyValue::of('100.00', 'CHF');
        $result = $m1->add('25.50');
        $this->assertEquals('125.50', $result->getAmount());
    }

    public function test_division(): void
    {
        $m1 = MoneyValue::of('100.00', 'CHF');
        $result = $m1->divide('4', 2);
        $this->assertEquals('25.00', $result->getAmount());
    }

    public function test_polarity_checks(): void
    {
        $positive = MoneyValue::of('10.00', 'CHF');
        $negative = MoneyValue::of('-10.00', 'CHF');
        $zero = MoneyValue::of('0.00', 'CHF');

        $this->assertTrue($positive->isPositive());
        $this->assertFalse($positive->isNegative());
        $this->assertFalse($positive->isZero());

        $this->assertTrue($negative->isNegative());
        $this->assertFalse($negative->isPositive());
        $this->assertFalse($negative->isZero());

        $this->assertTrue($zero->isZero());
        $this->assertFalse($zero->isPositive());
        $this->assertFalse($zero->isNegative());
    }

    public function test_comparisons(): void
    {
        $m1 = MoneyValue::of('100.00', 'CHF');
        $m2 = MoneyValue::of('100.00', 'CHF');
        $m3 = MoneyValue::of('50.00', 'CHF');
        $mDiffCurrency = MoneyValue::of('100.00', 'EUR');

        $this->assertTrue($m1->equals($m2));
        $this->assertFalse($m1->equals($mDiffCurrency));
        $this->assertTrue($m1->greaterThan($m3));
        $this->assertFalse($m3->greaterThan($m1));
        $this->assertTrue($m3->lessThan($m1));
        $this->assertFalse($m1->lessThan($m3));
    }

    public function test_formatting_and_string_representation(): void
    {
        $money = MoneyValue::of('1234567.89', 'CHF');

        $this->assertEquals("1'234'567.89 CHF", $money->format());
        $this->assertEquals(1234567.89, $money->toFloat());
        $this->assertEquals('1234567.89', (string) $money);

        $json = $money->jsonSerialize();
        $this->assertEquals('1234567.89', $json['amount']);
        $this->assertEquals('CHF', $json['currency']);
        $this->assertEquals("1'234'567.89 CHF", $json['formatted']);
    }

    public function test_rounding_half_up_edge_cases(): void
    {
        // Multiply to trigger rounding with precision 0 and >= 5
        $money = MoneyValue::of('10.50', 'CHF');
        $roundedZero = $money->multiply('1', 0);
        $this->assertEquals('11', $roundedZero->getAmount());

        $moneyLow = MoneyValue::of('10.40', 'CHF');
        $roundedLow = $moneyLow->multiply('1', 0);
        $this->assertEquals('10', $roundedLow->getAmount());

        // Number without decimal point
        $moneyInt = new MoneyValue('10', 'CHF', 2);
        $this->assertEquals('10.00', $moneyInt->getAmount());
    }

    public function test_round_half_up_more_branches(): void
    {
        $refMethod = new \ReflectionMethod(MoneyValue::class, 'roundHalfUp');

        $money = MoneyValue::of('10.00', 'CHF');

        // Line 187: No dot in number
        $this->assertEquals('10.00', $refMethod->invoke($money, '10', 2));

        // Line 195: strlen(decimalPart) <= precision
        $this->assertEquals('10.50', $refMethod->invoke($money, '10.5', 2));

        // Line 207: precision > 0, checkDigit >= 5
        $this->assertEquals('10.3', $refMethod->invoke($money, '10.25', 1));
    }
}
