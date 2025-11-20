$(document).ready(function () {
    $('.switch_language_order').on('click', function() {
        const sourceWordLangEl = $('#source_word_language');
        const targetWordLangEl = $('#target_word_language');
        const sourceLang = sourceWordLangEl.val();
        const targetLang = targetWordLangEl.val();
        let lang1 = sourceLang;
        let lang2 = targetLang;

        if(sourceLang == '' || targetLang == '') {
            toastr.error('No language selected');
            return;
        }

        sourceWordLangEl.val(lang2);
        targetWordLangEl.val(lang1);
        // const searchWord = $('#search-text').val();

        // const translationUrl = `${window.location.origin}/translation?search_text=${searchWord}&source_language=${lang2}&target_language=${lang1}`;
        // window.location.href = translationUrl;
    });
});
