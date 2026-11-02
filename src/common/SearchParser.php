<?php
/**
 * Search Parser for Pakiti
 *
 * Provides advanced search parsing with support for:
 * - Exclusion terms using !prefix (e.g., !5.4.0)
 * - Exclusion terms using "not" keyword (e.g., not debian)
 * - Multiple terms combined with AND logic
 *
 * Usage: Include this file and use SearchParser::parseSearchTerms($search)
 */

class SearchParser
{
    /**
     * Parse search string into inclusion and exclusion terms
     * Supports syntax: !term or "not term" for exclusions
     *
     * @param string $search The search string
     * @return array Array with 'include' and 'exclude' keys containing arrays of terms
     */
    public static function parseSearchTerms($search)
    {
        $includeTerms = [];
        $excludeTerms = [];

        if ($search == null || trim($search) == '') {
            return ['include' => $includeTerms, 'exclude' => $excludeTerms];
        }

        // Tokenize the search string while preserving quoted phrases
        $tokens = [];
        $currentToken = '';
        $inQuotes = false;
        $search = trim($search);

        for ($i = 0; $i < strlen($search); $i++) {
            $char = $search[$i];

            if ($char === '"' && ($i == 0 || $search[$i-1] !== '\\')) {
                $inQuotes = !$inQuotes;
                $currentToken .= $char;
            } elseif ($char === ' ' && !$inQuotes) {
                if ($currentToken !== '') {
                    $tokens[] = $currentToken;
                    $currentToken = '';
                }
            } else {
                $currentToken .= $char;
            }
        }
        if ($currentToken !== '') {
            $tokens[] = $currentToken;
        }

        // Process tokens to identify inclusion/exclusion
        $i = 0;
        while ($i < count($tokens)) {
            $token = $tokens[$i];

            // Check for "not" keyword followed by a term or phrase
            if (strtolower($token) === 'not' && isset($tokens[$i + 1])) {
                $excludeTerms[] = $tokens[$i + 1];
                $i += 2;
                continue;
            }

            // Check for ! prefix
            if (strpos($token, '!') === 0 && strlen($token) > 1) {
                $excludeTerms[] = substr($token, 1);
                $i++;
                continue;
            }

            // Regular inclusion term
            if ($token !== '') {
                $includeTerms[] = $token;
            }
            $i++;
        }

        return ['include' => $includeTerms, 'exclude' => $excludeTerms];
    }

    /**
     * Build SQL WHERE clause conditions for search terms
     *
     * @param array $terms Result from parseSearchTerms()
     * @param object $db Database manager object (for escaping)
     * @return array Array with 'where' (SQL condition string) and 'needsOsJoin' (boolean)
     */
    public static function buildSearchConditions($terms, $db)
    {
        $includeTerms = $terms['include'];
        $excludeTerms = $terms['exclude'];

        if (empty($includeTerms) && empty($excludeTerms)) {
            return ['where' => null, 'needsOsJoin' => false];
        }

        $conditions = [];
        $needsOsJoin = true; // We always join Os for hostname/os/kernel search

        // Build inclusion conditions
        if (!empty($includeTerms)) {
            $includeConditions = [];
            foreach ($includeTerms as $term) {
                $escapedTerm = $db->escape(strtolower(trim($term)), true);
                $includeConditions[] = "(lower(Host.hostname) like '%{$escapedTerm}%'
                    or lower(Os.name) like '%{$escapedTerm}%'
                    or lower(Host.kernel) like '%{$escapedTerm}%')";
            }
            // Multiple include terms are combined with AND
            $conditions[] = '(' . implode(' and ', $includeConditions) . ')';
        }

        // Build exclusion conditions (NOT LIKE)
        if (!empty($excludeTerms)) {
            foreach ($excludeTerms as $term) {
                $escapedTerm = $db->escape(strtolower(trim($term)), true);
                $conditions[] = "(lower(Host.hostname) not like '%{$escapedTerm}%'
                    and lower(Os.name) not like '%{$escapedTerm}%'
                    and lower(Host.kernel) not like '%{$escapedTerm}%')";
            }
        }

        $whereClause = implode(' and ', $conditions);

        return ['where' => $whereClause, 'needsOsJoin' => $needsOsJoin];
    }

    /**
     * Quick test function to demonstrate parsing
     * Call from command line: php SearchParser.php test
     */
    public static function runTests()
    {
        $testCases = [
            'ubuntu' => ['include' => ['ubuntu'], 'exclude' => []],
            '!5.4.0' => ['include' => [], 'exclude' => ['5.4.0']],
            'not debian' => ['include' => [], 'exclude' => ['debian']],
            'ubuntu !5.4.0' => ['include' => ['ubuntu'], 'exclude' => ['5.4.0']],
            'web !dev not staging' => ['include' => ['web'], 'exclude' => ['dev', 'staging']],
            'kernel1 !k1 !k2' => ['include' => ['kernel1'], 'exclude' => ['k1', 'k2']],
            '  !trim  ' => ['include' => [], 'exclude' => ['trim']],
            'term1 term2 term3' => ['include' => ['term1', 'term2', 'term3'], 'exclude' => []],
        ];

        echo "SearchParser Test Cases\n";
        echo str_repeat("=", 60) . "\n\n";

        $passed = 0;
        $failed = 0;

        foreach ($testCases as $input => $expected) {
            $result = self::parseSearchTerms($input);
            $match = ($result == $expected);

            if ($match) {
                $passed++;
                echo "[PASS] Input: '$input'\n";
                echo "       Include: " . json_encode($result['include']) . "\n";
                echo "       Exclude: " . json_encode($result['exclude']) . "\n";
            } else {
                $failed++;
                echo "[FAIL] Input: '$input'\n";
                echo "       Expected - Include: " . json_encode($expected['include']) . ", Exclude: " . json_encode($expected['exclude']) . "\n";
                echo "       Got      - Include: " . json_encode($result['include']) . ", Exclude: " . json_encode($result['exclude']) . "\n";
            }
            echo "\n";
        }

        echo str_repeat("=", 60) . "\n";
        echo "Results: $passed passed, $failed failed\n";

        return ($failed == 0);
    }
}

// Run tests if called from command line
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'test') {
    SearchParser::runTests();
    exit(0);
}
