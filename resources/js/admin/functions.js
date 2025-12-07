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

window.importTranslations = function() {
    $('#loadFileForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this)[0];
        const fileInput = form.querySelector('input[name="file"]');
        const delimiter = $('input#delimiter').val();

        const file = fileInput.files[0];

        if(!file) {
            toastr.error('File not selected');
            return;
        }

        loadCSVData(file, delimiter);
    });

    $(document).on('submit', '#importForm', function(e) {
        e.preventDefault();

        const form = $('#loadFileForm')[0];
        const fileInput = form.querySelector('input[name="file"]');

        const file = fileInput.files[0];

        if(!file) {
            toastr.error('File not selected');
            return;
        }

        const jobId = 'import_' + Date.now();

        const ext = file.name.split('.').pop().toLowerCase();

        if (ext === 'csv') {
            handleCSVUpload(file, jobId);
        } else {
            toastr.error('Format not supported');
        }
    });

    function loadCSVData(file, delimiter) {
        const token = $('meta[name="csrf-token"]').attr('content');
        const loadingIcon = $('#loadingIcon');

        let formData = new FormData();
        formData.append('_token', token);
        formData.append('file', file);
        formData.append('delimiter', delimiter);

        loadingIcon.removeClass('hidden');

        $.ajax({
            method: 'POST',
            url: "/admin/import/loadCsvData",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.status === 'success') {
                    const loadedDataPreviewTable = buildLoadedDataPreviewTable(response.data.fileData, response.data.languages);
                    $('#data_preview').html(loadedDataPreviewTable);
                }
                else if(response.status === 'error') {
                    toastr.error(response.message);
                }

                loadingIcon.addClass('hidden');
            },
            error: function(xhr, status, error) {
                toastr.error(error);
            }
        });
    }

    function handleCSVUpload(file, jobId) {
        const formData = new FormData();
        const token = $('meta[name="csrf-token"]').attr('content');
        const delimiter = $('input#delimiter').val();
        const sourceWordName = $('#source_word_name').val();
        const targetWordName = $('#translations_name').val();
        const sourceLang = $('#source_lang').val();
        const targetLang = $('#target_lang').val();

        formData.append('_token', token);
        formData.append('type', 'csv');
        formData.append('jobId', jobId);
        formData.append('file', file);
        formData.append('delimiter', delimiter);
        formData.append('sourceWordName', sourceWordName);
        formData.append('targetWordName', targetWordName);
        formData.append('sourceLang', sourceLang);
        formData.append('targetLang', targetLang);

        const importFormProgressbar = $('#importProgressbar');
        const importBtn = $('#importBtn');
        // show progress bar
        importFormProgressbar.addClass('visible');
        importBtn.addClass('hidden');


        $.ajax({
            method: 'POST',
            url: '/admin/import/uploadCsv',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status === 'success') {
                    checkCSVProgress(jobId, importFormProgressbar, importBtn);
                }
                else if(response.status === 'error') {
                    toastr.error(response.message);
                    hideProgress(importFormProgressbar, importBtn);
                    importBtn.removeClass('hidden');
                }
            },
            error: function(xhr) {
                toastr.error('CSV upload error');
                hideProgress(importFormProgressbar, importBtn);
            }
        });
    }

    function checkCSVProgress(jobId, importFormProgressbar, importBtn) {
        $.get('/admin/import/status/' + jobId, function(data) {
            const percent = Math.round(data.percent);
            importFormProgressbar.find('.progress_percentage').text(percent + '%');
            importFormProgressbar.find('.progress').css('width', percent + '%');

            if(percent < 100) {
                setTimeout(() => checkCSVProgress(jobId, importFormProgressbar, importBtn), 250);
            }
            else {
                hideProgress(importFormProgressbar, importBtn);
            }
        })
    }

    function hideProgress(importFormProgressbar, importBtn) {
        setTimeout(function() {
            importFormProgressbar.removeClass('visible');
            resetProgress(importFormProgressbar);
            importBtn.removeClass('hidden');
        }, 300);
    }

    function resetProgress(importFormProgressbar) {
        importFormProgressbar.find('.progress').css('width', '0%');
        importFormProgressbar.find('.progress_percentage').text('0%');
    }

    function buildLoadedDataPreviewTable(data, languages) {
        let html = `
            <form id="importForm">
                <div class="table-wrap my-2 md:flex gap-2">
                    <div class="form-group">
                        <h4 class="text-xl font-bold">Uploading schema</h4>
                        <p>Drag & drop values from Column to set up source word and translations</p>
                        <div class="my-2">
                            <label for="source_word_name">Source Word Name</label>
                            <input type="text" name="source_word_name" id="source_word_name" class="border rounded w-full text-gray-900" readonly>
                        </div>
                        <div class="my-2">
                            <label for="translations_name">Translations Name</label>
                            <input type="text" name="translations_name" id="translations_name" class="border rounded w-full text-gray-900" readonly>
                        </div>`;

        html += buildSelectInputForLanguage(languages, 'Source Lang', 'source_lang');
        html += buildSelectInputForLanguage(languages, 'Target Lang', 'target_lang');

        html += `</div>
                <div class="import-table">
                    <div class="grid-row header">
                        <div>Column</div>
                        <div>Values</div>
                    </div>
                <div class="grid-row">`;

        $.each(data, function(key, item) {
            html += `
                <div class="column-cell" title="drag&drop this to set uploading schema" draggable="true">
                        <span>${key}</span>
                </div>
                <div class="values-cell">
                    <span>${item !== '' && Array.isArray(item) ? item.join(', ') : ''}</span>
                </div>
            `;
        });

        html += `</div>
                    </div>
                </div>
                <button class="btn btn-primary" id="importBtn">Import</button
            </form>
        `;

        return html;
    }

    function buildSelectInputForLanguage(languages, labelName, idName) {
        let langSelectInput = `
            <div class="my-2">
                <label for="${idName}">${labelName}</label>
                <select name="${idName}" id="${idName}" class="border rounded w-full text-gray-900">
                    <option value>-- Select --</option>
        `;
        $.each(languages, function(i, lang) {
            langSelectInput += `
                    <option value="${lang.id}">${lang.name}</option>
                </div>
            `;
        });
        langSelectInput += `
                </select>
            </div>
        `;

        return langSelectInput;
    }
}

window.dragAndDropFunctionality = function() {
    $(document).on('dragstart', '.column-cell', function(e) {
        const $columnName = $(this).text().trim();
        e.originalEvent.dataTransfer.setData("text/plain", $columnName);
    });

    $(document).on('dragover', '#source_word_name, #translations_name', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $(document).on('drop', '#source_word_name, #translations_name', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).val(e.originalEvent.dataTransfer.getData("text/plain"));
    });
}

window.sendExportForm = function() {
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const delimiter = ';';

        $.ajax({
            method: 'POST',
            url: "/admin/export/init",
            data: formData,
            success: function(response) {
                let exportedData = '';
                let sourceLangCode = '';
                let targetLangCode = '';
                if(response.status === 'success') {

                    if(Array.isArray(response.translations)) {
                        if(response.translations.length == 0) {
                            toastr.error('Nothing to export');
                            return false;
                        }
                        const translationsCount = response.translations.length;

                        // show progressbar
                        const progressbar = $('#exportProgressbar');
                        initProgressbar(progressbar);
                        $('#downloadBtn').remove();

                        $.each(response.translations, function(i, item) {
                            if(sourceLangCode == '') sourceLangCode = item.source_language;
                            if(targetLangCode == '') targetLangCode = item.target_language;
                            if(exportedData == '') exportedData = `${sourceLangCode};${targetLangCode}\r\n`;

                            exportedData += `${item.source_word};${item.target_word}\r\n`;

                            // count progress percentage
                            const percent = Math.round((100 / translationsCount) * (i+1));
                            updateProgressbar(progressbar, percent);
                        });

                        setTimeout(function() {
                            progressbar.removeClass('visible');
                            generateDownloadButton(exportedData, sourceLangCode, targetLangCode);
                            toastr.success('Export created successfully');
                        }, 500);
                    }
                }
                else if (response.status === 'error') {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error(error);
            }
        });
    });

    function generateDownloadButton(exportedData, sourceLangCode, targetLangCode) {
        const blob = new Blob([exportedData], { type: "text/csv;charset=utf-8;" });
        const downloadUrl = URL.createObjectURL(blob);
        const currentDate = new Date();
        const fileName = `${currentDate.toLocaleDateString('sv-SE')}_${sourceLangCode}-${targetLangCode}_export.csv`;
        const downloadBtn = $(`<a id="downloadBtn" class="btn btn-success my-2 inline-block" href="${downloadUrl}" download="${fileName}">Donwload Exported Data (.csv)</a>`);

        insertDownloadButton(downloadBtn, downloadUrl);
    }

    function initProgressbar(progressbar) {
        progressbar.addClass('visible');
        updateProgressbar(progressbar, 0);
    }

    function updateProgressbar(progressbar, percent) {
        progressbar.find('.progress_percentage').text(`${percent}%`);
        progressbar.find('.progress').css('width', percent + '%');
    }

    function insertDownloadButton(downloadBtn, downloadUrl) {
        downloadBtn.on('click', function() {
            setTimeout(() => URL.revokeObjectURL(downloadUrl), 100);
        });

        $('#downloadBtnContainer').html(downloadBtn);
    }
}
