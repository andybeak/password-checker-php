<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AndyBeak\PasswordChecker\PasswordChecker;

final class PasswordCheckerTest extends TestCase
{
    const EMOJI_STRING = '😀 😁 😂🤣 😃';

    /**
     * @dataProvider passwordsWithNumbersProvider
     * @param $password
     */
    public function testContainsDigitsPositive($password): void
    {
        $this->assertTrue(PasswordChecker::containsDigits($password));
    }

    /**
     * @dataProvider passwordsWithOutNumbersProvider
     * @param $password
     */
    public function testContainsDigitsNegative($password): void
    {
        $this->assertFalse(PasswordChecker::containsDigits($password));
    }

    // ------

    /**
     * @dataProvider passwordsWithSpecialCharsProvider
     * @param $password
     */
    public function testContainsSpecialCharsPositive($password): void
    {
        $this->assertTrue(PasswordChecker::containsSpecialChars($password));
    }

    /**
     * @dataProvider passwordsWithoutSpecialCharsProvider
     * @param $password
     */
    public function testContainsSpecialCharsNegative($password): void
    {
        $this->assertFalse(PasswordChecker::containsSpecialChars($password));
    }

    // ------

    /**
     * @dataProvider passwordsWithMixOfCaseProvider
     * @param $password
     */
    public function testContainsMixOfCasePositive($password): void
    {
        $this->assertTrue(PasswordChecker::containsMixOfCase($password));
    }

    /**
     * @dataProvider passwordsWithOutMixOfCaseProvider
     * @param $password
     */
    public function testContainsMixOfCaseNegative($password): void
    {
        $this->assertFalse(PasswordChecker::containsMixOfCase($password));
    }


    // ------

    /**
     * @dataProvider passphrases
     * @param $password
     */
    public function testLooksLikeAPassPhrasePositive($password): void
    {
        $this->assertTrue(PasswordChecker::looksLikeAPassPhrase($password));
    }

    /**
     * @dataProvider passwordsWithMixOfCaseProvider
     * @dataProvider passwordsWithOutMixOfCaseProvider
     * @param $password
     */
    public function testLooksLikeAPassPhraseNegative($password): void
    {
        $this->assertFalse(PasswordChecker::looksLikeAPassPhrase($password));
    }

    // ------

    public function testIntegration()
    {
        $expectedArray = [
            'passwordLength' => 13,
            'containsDigits' => true,
            'containsSpecialChars' => false,
            'containsUpperAndLower' => false,
            'couldPossiblyBeAPassphrase' => false,
            'minimumDistanceToBadPassword' => 1
        ];

        $this->assertSame(PasswordChecker::checkPassword("password12345"), $expectedArray);
    }



    // ------ Providers

    /**
     * @return array
     */
    public function passwordsWithNumbersProvider()
    {
        return [
            ['password1234'],
            ['1234'],
            ['password 1234'],
            ['password!1234'],
            ['password1234' . SELF::EMOJI_STRING]
        ];
    }

    /**
     * @return array
     */
    public function passwordsWithOutNumbersProvider()
    {
        return [
            [''],
            ['password'],
            ['!"£$%^&*('],
            [self::EMOJI_STRING]
        ];
    }

    /**
     * @return array
     */
    public function passwordsWithSpecialCharsProvider()
    {
        $password = '';
        for ($i = 0; $i <= 255; $i++) {
            $character = chr($i);
            if (false === ctype_alnum($character)) {
                $password .= $character;
                yield [$password];
            }
        }

        yield [self::EMOJI_STRING];
    }

    /**
     * @return array
     */
    public function passwordsWithoutSpecialCharsProvider()
    {
        $password = '';

        for ($i = 0; $i <= 255; $i++) {
            $character = chr($i);
            if (true === ctype_alnum($character)) {
                $password .= $character;
                yield [$password];
            }
        }
    }

    /**
     * @return array
     */
    public function passwordsWithMixOfCaseProvider()
    {
        return [
            ['Password123'],
            ['passworD'],
            ['!Password'],
        ];
    }

    /**
     * @return array
     */
    public function passwordsWithOutMixOfCaseProvider()
    {
        return [
            ['password123'],
            ['password'],
            ['!password'],
            [''],
            ['!"£']
        ];
    }

    /**
     * @return array
     */
    public function passphrases()
    {
        return [
            ["passphrases are more secure than passwords"],
            ["passphrases are more secure than passwords 1234"],
            ["passphrases are more secure than passwords!%@ 1234"]
        ];
    }
}