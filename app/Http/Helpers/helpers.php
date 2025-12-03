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
