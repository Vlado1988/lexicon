<?php

if(!function_exists('format_translations')) {
    function format_translations(array $translations, string $source_language, string $target_language, ?string $wrapBefore = '', ?string $wrapAfter = '', ?string $beforeTranslation = '', ?string $afterTranslation = ''): string
    {
        $targetWordsSanitized = '';

        foreach($translations as $translation) {
            $params = [
                'search_text'      => $translation,
                'source_language'  => $target_language,
                'target_language'  => $source_language,
            ];

            $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $translationUrl = '?' . $query;

            $href = htmlspecialchars($translationUrl, ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($translation, ENT_QUOTES, 'UTF-8');

            $translationLink = "<a href=\"{$href}\">{$label}</a>";

            $targetWordsSanitized .= $beforeTranslation . $translationLink . $afterTranslation;
        }

        return $wrapBefore . $targetWordsSanitized . $wrapAfter;
    }
}

if(!function_exists('generate_search_key')) {
    /**
     * Generate a normalized search key for a word.
     * - Lowercases the word
     * - Removes all punctuation and special characters
     * - Removes spaces and hyphens
     * - Keeps Unicode letters and digits
     *
     * @param string $word
     * @return string
     */
    function generate_search_key(string $word): string
    {
        // lowercase
        $searchKey = mb_strtolower($word, 'UTF-8');

        // remove all non-letter, non-digit characters (keep Unicode letters)
        $searchKey = preg_replace('/[^\p{L}\p{N}]/u', '', $searchKey);

        // remove spaces (or could also remove hyphens if you want them equivalent)
        $searchKey = str_replace(' ', '', $searchKey);

        return $searchKey;
    }
}
