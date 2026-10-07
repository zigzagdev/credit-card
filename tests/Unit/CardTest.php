<?php

namespace LVR\CreditCard\Tests\Unit;

use LVR\CreditCard\Cards\Card;
use LVR\CreditCard\Exceptions\CreditCardCharactersException;
use LVR\CreditCard\Exceptions\CreditCardException;
use LVR\CreditCard\Factory;
use LVR\CreditCard\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CardTest extends TestCase
{
    /**
     * @dataProvider badStrings
     */
    #[DataProvider('badStrings')]
    public function test_it_expects_card_number($input)
    {
        $this->expectException(CreditCardException::class);

        Factory::makeFromNumber($input);
    }

    public static function badStrings()
    {
        return ['empty string' => [''], 'null' => [null]];
    }

    public function test_it_allows_spaces_in_card_numbers()
    {
        $this->assertInstanceOf(Card::class, Factory::makeFromNumber('4111 1111 1111 1111'));
    }

    public function test_it_checks_if_all_card_number_characters_are_numeric()
    {
        $this->expectException(CreditCardCharactersException::class);

        Factory::makeFromNumber('4111111x111111sss111');
    }

    public function test_it_returns_card_name()
    {
        $card = Factory::makeFromNumber('4111 1111 1111 1111');

        $this->assertEquals('visa', $card->name());
    }

    public function test_it_returns_card_brand()
    {
        $card = Factory::makeFromNumber('4111 1111 1111 1111');

        $this->assertEquals('Visa', $card->brand());
    }
}
