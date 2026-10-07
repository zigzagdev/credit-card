<?php

namespace LVR\CreditCard\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\Cards\Card;
use LVR\CreditCard\Tests\TestCase;
use LVR\CreditCard\Tests\Unit\Cards\AmericanExpressTest;
use LVR\CreditCard\Tests\Unit\Cards\VisaTest;

class CardCvcTest extends TestCase
{
    public function test_it_check_cvc_by_credit_card()
    {
        $this->assertTrue($this->validator('243')->fails());
        $this->assertTrue($this->validator('1234')->passes());

        $this->assertTrue($this->validator('1234', AmericanExpressTest::class)->passes());
        $this->assertFalse($this->validator('243', AmericanExpressTest::class)->passes()); // American Express supports only 4 digits

        $this->assertTrue($this->validator('243', VisaTest::class)->passes());
        $this->assertTrue($this->validator('1234', VisaTest::class)->fails()); // Visa supports only 3 digits

        // Fails with non-digit characters
        $this->assertTrue($this->validator('1e3', VisaTest::class)->fails());
        $this->assertTrue($this->validator('-12', VisaTest::class)->fails());
        $this->assertTrue($this->validator(['123'], VisaTest::class)->fails());
        $this->assertTrue($this->validator("123\n", AmericanExpressTest::class)->fails());
        $this->assertTrue($this->validator('1e30', AmericanExpressTest::class)->fails());
        $this->assertTrue($this->validator(12, VisaTest::class)->fails());

        // Fails with bad card number
        $this->assertTrue(
            Validator::make(
                ['cvc' => '123'],
                ['cvc' => ['required', new CardCvc('123-4123')]]
            )->fails()
        );
    }

    public function test_it_checks_cvc_code_length()
    {
        // Valid
        $this->assertTrue(Card::isValidCvcLength('243'));
        $this->assertTrue(Card::isValidCvcLength('5678'));
        $this->assertTrue(Card::isValidCvcLength(321));
        $this->assertTrue(Card::isValidCvcLength(1234));
        $this->assertTrue(Card::isValidCvcLength('012'));

        // Empty
        $this->assertFalse(Card::isValidCvcLength(''));

        // Non digits
        $this->assertFalse(Card::isValidCvcLength('12e'));
        $this->assertFalse(Card::isValidCvcLength('1e3'));
        $this->assertFalse(Card::isValidCvcLength('1.2'));
        $this->assertFalse(Card::isValidCvcLength('-12'));
        $this->assertFalse(Card::isValidCvcLength('+12'));
        $this->assertFalse(Card::isValidCvcLength(' 12'));
        $this->assertFalse(Card::isValidCvcLength('12 '));
        $this->assertFalse(Card::isValidCvcLength('0x1'));
        $this->assertFalse(Card::isValidCvcLength("123\n"));
        $this->assertFalse(Card::isValidCvcLength('1e30'));
        $this->assertFalse(Card::isValidCvcLength(-12));
        $this->assertFalse(Card::isValidCvcLength(1.5));
        $this->assertFalse(Card::isValidCvcLength(null));
        $this->assertFalse(Card::isValidCvcLength(['123']));

        // Less than 3 digits
        $this->assertFalse(Card::isValidCvcLength('12'));
        $this->assertFalse(Card::isValidCvcLength('1'));
        $this->assertFalse(Card::isValidCvcLength(0));
        $this->assertFalse(Card::isValidCvcLength(1));
        $this->assertFalse(Card::isValidCvcLength(12));

        // More than 4 digits
        $this->assertFalse(Card::isValidCvcLength(12345));
        $this->assertFalse(Card::isValidCvcLength(123456));
        $this->assertFalse(Card::isValidCvcLength('12345'));
        $this->assertFalse(Card::isValidCvcLength('123455'));
    }

    /**
     * @param  string|int  $cvc
     * @param  string  $testCard  Card test class providing valid numbers
     * @return mixed
     */
    protected function validator($cvc, string $testCard = AmericanExpressTest::class)
    {
        return Validator::make(
            [
                'cvc' => $cvc,
            ],
            ['cvc' => ['required', new CardCvc($testCard::validNumbers()->shuffle()->first())]]
        );
    }
}
