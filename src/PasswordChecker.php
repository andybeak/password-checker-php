<?php namespace AndyBeak\PasswordChecker;

use AndyBeak\PasswordCheckerPhp\Exceptions\CannotOpenPasswordFileException;

class PasswordChecker
{
    /**
     * Flag for fopen() to indicate read only access
     */
    const READ_ONLY_ACCESS = 'r';

    /**
     * How many microseconds there are in a second
     */
    const ONE_SECOND_IN_MICROSECONDS = 1000000;

    /**
     * How long we are happy to wait for the loop
     */
    const LOOP_LIMIT_MICROSECONDS = self::ONE_SECOND_IN_MICROSECONDS / 4;

    /**
     * If a password is longer than this then we'll say it is long enough to look like it could be a passphrase
     */
    const PASSPHRASE_MINIMUM_LENGTH = 15;

    /**
     * The space character
     */
    const SPACE_CHAR = ' ';

    public static function checkPassword(string $password): array
    {
        return [
            'passwordLength' => strlen($password),
            'containsDigits' => self::containsDigits($password),
            'containsSpecialChars' => self::containsSpecialChars($password),
            'containsUpperAndLower' => self::containsMixOfCase($password),
            'couldPossiblyBeAPassphrase' => self::looksLikeAPassPhrase($password),
            'minimumDistanceToBadPassword' => self::findMinimumDistanceToBadPassword($password),
        ];
    }

    /**
     * @param string $password
     * @return bool
     */
    public static function containsDigits(string $password): bool
    {
        preg_match_all('/[0-9]/', $password, $numbers);
        return count($numbers[0]) > 0;
    }

    /**
     * @param string $password
     * @return bool
     */
    public static function containsSpecialChars(string $password): bool
    {
        return !ctype_alnum($password);
    }

    /**
     * @param string $password
     * @return bool
     */
    public static function containsMixOfCase(string $password): bool
    {

        $allLowerCase = $password === strtolower($password);

        $allUpperCase = $password === strtoupper($password);

        $allSameCase = $allLowerCase || $allUpperCase;

        return !$allSameCase;
    }

    /**
     * @param string $password
     * @return bool
     */
    public static function looksLikeAPassPhrase(string $password): bool
    {
        $longEnough = mb_strlen($password) >= self::PASSPHRASE_MINIMUM_LENGTH;

        $containsSpaceChar = mb_strpos($password, self::SPACE_CHAR) !== 0;

        $containsOnlyPrintables = ctype_print($password);

        return $longEnough && $containsSpaceChar && $containsOnlyPrintables;
    }

    /**
     * @param string $password
     * @return int
     * @throws \Exception
     */
    public static function findMinimumDistanceToBadPassword(string $password): int
    {
        try {

            $minimumLength = strlen($password);

            $fileHandle = self::getFileHandle();

            $startTime = microtime(true);

            while (!feof($fileHandle) && self::isRunLengthShorterThanLoopLimit($startTime)) {

                $badPassword = fgets($fileHandle);

                $distance = levenshtein($password, $badPassword);

                if ($distance < $minimumLength) {
                    $minimumLength = $distance;
                }
            }

            return $minimumLength;

        } catch (CannotOpenPasswordFileException $e) {

            throw new \Exception($e->getMessage());

        } finally {

            if (isset($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }

    /**
     * @param string $filename
     * @return resource
     * @throws CannotOpenPasswordFileException
     */
    public static function getFileHandle(string $filename = "password_list.txt")
    {
        $dataPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;

        $fileHandle = fopen($dataPath . $filename, self::READ_ONLY_ACCESS);

        if (false === $fileHandle) {

            throw new CannotOpenPasswordFileException("Could not open password file for reading.");

        }

        return $fileHandle;
    }

    /**
     * @param $startTimeMicroseconds
     * @return bool
     */
    public static function isRunLengthShorterThanLoopLimit($startTimeMicroseconds): bool
    {
        $runLength = microtime(true) - $startTimeMicroseconds;

        return $runLength < self::LOOP_LIMIT_MICROSECONDS;
    }
}