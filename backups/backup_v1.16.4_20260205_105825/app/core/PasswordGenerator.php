<?php

/**
 * Password Generator Helper
 * Generate strong random passwords untuk Google Workspace accounts
 */
class PasswordGenerator
{
    /**
     * Generate password yang kuat dan memenuhi requirement Google Workspace
     * 
     * @param int $length Panjang password (min 8, recommended 12-16)
     * @param bool $includeSymbols Sertakan karakter simbol
     * @return string Password yang di-generate
     */
    public static function generate($length = 12, $includeSymbols = true)
    {
        // Minimum length untuk Google Workspace
        if ($length < 8) {
            $length = 8;
        }

        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        // Build character set
        $chars = $lowercase . $uppercase . $numbers;
        if ($includeSymbols) {
            $chars .= $symbols;
        }

        // Ensure password contains at least one of each type
        $password = '';
        
        // Add at least one lowercase
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        
        // Add at least one uppercase
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        
        // Add at least one number
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        
        // Add at least one symbol (if enabled)
        if ($includeSymbols) {
            $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        }

        // Fill the rest randomly
        $remainingLength = $length - strlen($password);
        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Shuffle to avoid predictable pattern
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Generate password untuk bulk operations (banyak user sekaligus)
     * Returns array of passwords
     * 
     * @param int $count Jumlah password yang dibutuhkan
     * @param int $length Panjang setiap password
     * @return array Array of generated passwords
     */
    public static function generateBulk($count, $length = 12)
    {
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = self::generate($length);
        }
        return $passwords;
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'message' => string, 'score' => int]
     */
    public static function validateStrength($password)
    {
        $length = strlen($password);
        $score = 0;
        $messages = [];

        // Check length
        if ($length < 8) {
            return [
                'valid' => false,
                'message' => 'Password minimal 8 karakter',
                'score' => 0
            ];
        }
        $score += min($length, 16); // Max 16 points for length

        // Check for lowercase
        if (preg_match('/[a-z]/', $password)) {
            $score += 5;
        } else {
            $messages[] = 'Harus ada huruf kecil';
        }

        // Check for uppercase
        if (preg_match('/[A-Z]/', $password)) {
            $score += 5;
        } else {
            $messages[] = 'Harus ada huruf besar';
        }

        // Check for numbers
        if (preg_match('/[0-9]/', $password)) {
            $score += 5;
        } else {
            $messages[] = 'Harus ada angka';
        }

        // Check for symbols
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 5;
        }

        $isValid = empty($messages);
        $message = $isValid ? 'Password kuat' : implode(', ', $messages);

        return [
            'valid' => $isValid,
            'message' => $message,
            'score' => min($score, 36) // Max score 36
        ];
    }

    /**
     * Generate memorable password (easier to type but still secure)
     * Format: Word-Word-Number-Symbol
     * Example: Happy-Cloud-2024-!
     * 
     * @return string Memorable password
     */
    public static function generateMemorable()
    {
        $words = [
            'Happy', 'Cloud', 'River', 'Mountain', 'Ocean', 'Forest', 'Sunset', 'Rainbow',
            'Dragon', 'Phoenix', 'Tiger', 'Eagle', 'Lion', 'Wolf', 'Dolphin', 'Panda',
            'Galaxy', 'Star', 'Moon', 'Solar', 'Comet', 'Planet', 'Nebula', 'Cosmic',
            'Dream', 'Hope', 'Faith', 'Peace', 'Unity', 'Wisdom', 'Courage', 'Honor'
        ];

        $word1 = $words[array_rand($words)];
        $word2 = $words[array_rand($words)];
        $number = random_int(1000, 9999);
        $symbols = ['!', '@', '#', '$', '%', '&', '*'];
        $symbol = $symbols[array_rand($symbols)];

        return $word1 . '-' . $word2 . '-' . $number . $symbol;
    }
}
