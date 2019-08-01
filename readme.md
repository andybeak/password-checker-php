# Password checker
![Travis build](https://travis-ci.com/andybeak/password-checker-php.svg?branch=master)

This package helps you to make it easier to check whether a user is using a risky password.

## Installation and usage

You can use composer to install the package.

    composer require andybeak/password-checker-php

It can be used like this:

    use AndyBeak\PasswordChecker\PasswordChecker;
    $passwordStrength = PasswordChecker::checkPassword('password');

The class returns an associative array that you can use to decide whether a password matches your password policy.  The return value for the example given is:

    array (size=6)
      'passwordLength' => int 8
      'containsDigits' => boolean false
      'containsSpecialChars' => boolean false
      'containsUpperAndLower' => boolean true
      'couldPossiblyBeAPassphrase' => boolean false
      'minimumDistanceToBadPassword' => int 1

### minimumDistanceToBadPassword

This is the shortest levenshtein distance between the supplied password and all of the dictionary passwords.

If this value is 0 then the supplied password exists as an exact string in the dictionary.  This means that it
will be possible to brute force guess the password using the supplied dictionary.

There may or may not be value in setting a minimum acceptable distance to a bad password.  It's possible that
people who are blocked from using `password1234` as their password will try `password12345`, for example.
    
See [https://www.php.net/manual/en/function.levenshtein.php](The PHP manual) for more details

### couldPossiblyBeAPassphrase

This is a very naive stab in the dark that the user could be entering a passphrase.  Most password managers
seem to avoid including space characters in their generated passwords.  Therefore if a user types in a lengthy
password (currently 20 characters) that only includes printable characters and includes at least one space then this flag will be set to true.

For example, the output for `passphrases are better than passwords` is:

    array (size=6)
      'passwordLength' => int 37
      'containsDigits' => boolean false
      'containsSpecialChars' => boolean true
      'containsUpperAndLower' => boolean true
      'couldPossiblyBeAPassphrase' => boolean true
      'minimumDistanceToBadPassword' => int 23

Note that only ASCII characters from 32 to 126 are considered printable

You might be willing to relax your policy about special characters and digits if the password supplied looks
like a passphrase.

## Password list

I include the phpbb password list from (https://wiki.skullsecurity.org/Passwords) in the package.  You can replace it with any plaintext file that uses newlines to separate passwords.

## Running the tests

Install the composer dependencies using `composer install` in the package path and then run `./vendor/bin/phpunit` to run the tests.