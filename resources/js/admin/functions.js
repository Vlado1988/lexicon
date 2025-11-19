window.handleWordLanguageSelects = function() {
    // Checks if the source and target word language are same and handle it
    $('.select_word_language').on('change', function() {
        const wordType = $(this).data('word-type');
        const sourceWordLangEl = $('#source_word_language');
        const targetWordLangEl = $('#target_word_language');
        const sourceLang = wordType === 'source' ? $(this).val() : sourceWordLangEl.val();
        const targetLang = wordType === 'target' ? $(this).val() : targetWordLangEl.val();

        if(sourceLang === targetLang) {
            toastr.error('Source language and target language cannot be same');
            wordType === 'source' ? sourceWordLangEl.val('') : targetWordLangEl.val('');
        }
    });
}

window.switchLanguages = function() {
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

        let table = $('#translation-table').DataTable();
        table.ajax.reload();
    });
}

window.getDataOnLanguageSelect = function() {
    $('.select_word_language').on('change', function() {
        const sourceWordLangEl = $('#source_word_language');
        const targetWordLangEl = $('#target_word_language');
        const sourceLang = sourceWordLangEl.val();
        const targetLang = targetWordLangEl.val();

        if(sourceLang === '' || targetLang === '') {
            return;
        }

        if(sourceLang === targetLang) {
            toastr.error('Source language and target language cannot be same');
            return;
        }

        //source_lang_id=3&target_lang_id=1
        const createTranslationBtn = $('#createTranslationBtn');
        const params = '?source_lang_id=' + sourceLang + '&target_lang_id=' + targetLang;
        const createTranslationUrl = createTranslationBtn.attr('href') + params;
        createTranslationBtn.attr('href', createTranslationUrl);

        window.history.replaceState({}, '', window.location.href + params);

        let table = $('#translation-table').DataTable();
        table.ajax.reload();
    });
}

window.handleTargetWordsInputFunctionality = function() {
    $('#target_word_container #target_word').on('keydown', function(e) {
        if(e.key == ',' || e.key == 'Enter') {
            e.preventDefault();
            const word = ($(this).val()).trim();

            if(!word || word == ' ') {
                return;
            }

            const targetWordsListEl = $('.target_words_list');
            const targetWordsListItemsTexts = targetWordsListEl
                .find('.target_word')
                .map(function() {
                    return $(this).text().trim();
                })
                .get();

            if(!targetWordsListItemsTexts.includes(word)) {
                const targetWordListItem = buildTargetWordListItem(word);
                targetWordsListEl.children().last().before(targetWordListItem);
            }

            $(this).val('');
        }
    });

    // delete target word in target words list on x click
    $(document).on('click', '.delete_target_word', function() {
        $(this).closest('.target_word_list_item').remove();
    });

    $('.target_words_container').on('click', function() {
        $('#target_word_container #target_word').focus();
    });
}

window.handleSearchWordInputFunctionality = function() {
    let timer = null;

    $('#source_word').on('keyup', function() {
        clearInterval(timer);

        const el = this;

        timer = setTimeout(function() {
            manageSearchWordInputFunctionality.call(el, 'source');
        }, 300);
    });

    $('#source_word, #target_word').on('focusout', function() {
        setTimeout(function() {
            $('.search_results_list').hide().children().remove();
        }, 500);
    });

    $('#source_word').on('focusout', function() {
        setTimeout(function() {
            const sourceWord = $(this).val();
            const sourceLangId = $('#source_word_language').val();
            const targetLangId = $('#target_word_language').val();

            if(!sourceWord || !sourceLangId || !targetLangId) return;

            $.ajax({
                method: 'POST',
                url: '/admin/translation/get-translations-by-source-word',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    sourceWord,
                    sourceLangId,
                    targetLangId,
                },
                success: function(response) {
                    if(response.status === 'success') {

                        const existingItems = $('.target_words_list .target_word')
                            .map(function() { return $(this).text().trim(); })
                            .get();

                        // Get new words from server and trim
                        const newWordsFromServer = response?.data?.[0]?.target_word !== undefined
                            ? response.data[0].target_word.split(',').map(item => item.trim())
                            : [];

                        // Filter words which already exists
                        const wordsToAdd = newWordsFromServer.filter(word => !existingItems.includes(word));

                        // Add only new words
                        const targetWordsListEl = $('.target_words_list');
                        wordsToAdd.forEach(word => {
                            targetWordsListEl.children().last().before(buildTargetWordListItem(word));
                        });
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error(error);
                }
            });
        }.bind(this), 500);
    });

    $('#target_word').on('keyup', function() {
        clearInterval(timer);

        const el = this;

        timer = setTimeout(function() {
            manageSearchWordInputFunctionality.call(el, 'target');
        }, 300);
    });

    $(document).on('click', '.search_result_list_item', function() {
        const searchResultsListType = $(this).parent().data('for-input');

        const word = buildTargetWordListItem($(this).text());
        const searchResultsList = $(this).closest('.search_results_list');
        searchResultsList.prev().val('');

        if(searchResultsListType == 'source_word') {
            $('#source_word_container .search_word_input').val($(this).text());
        }
        else {
            $('.target_words_list').children().last().before(word);
        }

        searchResultsList.hide().children().remove();
    });
}

window.manageSearchWordInputFunctionality = function(type) {
    const sourceWord = $(this).val();
    const sourceLangId = $(`#${type}_word_language`).val() || null;

    const searchResultsList = $(this).next();
    if(sourceWord == '') {
        searchResultsList.hide().children().remove();
        return;
    }
    else {
        searchResultsList.show();
    }

    $.ajax({
        method: 'POST',
        url: "/admin/translation/get-source-word-data",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            word: sourceWord,
            sourceLangId: sourceLangId,
        },
        success: function(response) {
            if(response.status === 'success') {
                // if no results or results == 1 and results == input value
                if(response.data.length === 0 || (response.data.length === 1 && response.data[0].word == sourceWord)) {
                    searchResultsList.hide().children().remove();
                }

                const resultContent = buildSourceWordSearchResults(response.data);
                $(`#${type}_word_container .search_results_list`).html(resultContent);
            }
            else if(response.status === 'error') {
                toastr.error(data.message);
            }
        },
        error: function(xhr, status, error) {
            toastr.error(error);
        }
    });
}

window.buildSourceWordSearchResults = function(data) {
    let html = '';
    $.each(data, function(i, item) {
        html += `<li class="search_result_list_item">${item.word}</li>`;
    });

    return html;
}

window.buildTargetWordListItem = function(word) {
    return `<div class="target_word_list_item">
            <span class="target_word">${word}</span>
            <span class="delete_target_word"><i class="fa-solid fa-x"></i></span>
            <input type="hidden" name="target_words[]" value="${word}">
        </div>`;
}
