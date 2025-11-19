<?php

if(!function_exists('format_translations')) {
    function format_translations(array $translations, ?string $wrapBefore = '', ?string $wrapAfter = '', ?string $beforeTranslation = '', ?string $afterTranslation = ''): string
    {
        $targetWordsSanitized = '';
        foreach($translations as $translation) {
            $targetWordsSanitized .= $beforeTranslation . $translation . $afterTranslation;
        }

        return $wrapBefore . $targetWordsSanitized . $wrapAfter;
    }
}
