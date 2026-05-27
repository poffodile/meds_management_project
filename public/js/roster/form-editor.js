(function() {
    'use strict';

    var editingTemplateId = null;
    var sectionCounter = 0;

    var fieldTypes = [
        { value: 'text', label: 'Text' },
        { value: 'textarea', label: 'Textarea' },
        { value: 'date', label: 'Date' },
        { value: 'number', label: 'Number' },
        { value: 'email', label: 'Email' },
        { value: 'tel', label: 'Phone' },
        { value: 'select', label: 'Dropdown' },
        { value: 'checkbox', label: 'Checkboxes' },
        { value: 'radio', label: 'Radio' },
        { value: 'risk', label: 'Risk Rating' },
        { value: 'signature', label: 'Signature' },
        { value: 'table', label: 'Table' },
        { value: 'info', label: 'Info Text' }
    ];

    var needsOptions = ['select', 'checkbox', 'radio', 'risk'];
    var needsColumns = ['table'];

    // ── Load template into editor ──
    window.loadTemplateIntoEditor = function(template) {
        editingTemplateId = template.id;
        var fj = template.form_json;
        $('#editorTitle').val(fj.formTitle || template.title || '');
        $('#editorDesc').val(fj.formDescription || template.description || '');
        $('#editorSections').empty();
        sectionCounter = 0;

        var sections = fj.sections || [];
        for (var i = 0; i < sections.length; i++) {
            addSection(sections[i]);
        }
        if (sections.length === 0) addSection();
    };

    function addSection(data) {
        sectionCounter++;
        var secId = 'editorSec_' + sectionCounter;
        var title = (data && data.title) ? data.title : 'Section ' + sectionCounter;

        var html = '<div class="fb-editor-section" id="' + secId + '">';
        html += '<div class="fb-editor-section-title">';
        html += '<input type="text" value="' + esc(title) + '" placeholder="Section title" class="sec-title-input">';
        html += '<button class="btn-rm" onclick="removeSection(\'' + secId + '\')" title="Remove section"><i class="fa fa-times"></i></button>';
        html += '</div>';
        html += '<div class="sec-fields"></div>';
        html += '<button class="btn-add-field" onclick="addFieldToSection(\'' + secId + '\')"><i class="fa fa-plus"></i> Add Field</button>';
        html += '</div>';

        $('#editorSections').append(html);

        if (data && data.fields) {
            for (var j = 0; j < data.fields.length; j++) {
                addFieldToSection(secId, data.fields[j]);
            }
        }
    }

    window.removeSection = function(secId) {
        $('#' + secId).remove();
    };

    // ── Add field to a section ──
    var fieldCounter = 0;
    window.addFieldToSection = function(secId, data) {
        fieldCounter++;
        var fieldRowId = 'field_' + fieldCounter;
        var label = (data && data.label) ? data.label : '';
        var type = (data && data.type) ? data.type : 'text';
        var required = (data && data.required) ? true : false;

        var html = '<div class="fb-editor-field-row" id="' + fieldRowId + '">';
        html += '<input type="text" value="' + esc(label) + '" placeholder="Field label" class="field-label-input">';
        html += '<select class="field-type-select" onchange="onFieldTypeChange(\'' + fieldRowId + '\')">';
        for (var k = 0; k < fieldTypes.length; k++) {
            var sel = (fieldTypes[k].value === type) ? ' selected' : '';
            html += '<option value="' + fieldTypes[k].value + '"' + sel + '>' + fieldTypes[k].label + '</option>';
        }
        html += '</select>';
        html += '<label class="fb-option-item" style="white-space:nowrap;"><input type="checkbox" class="field-req-check"' + (required ? ' checked' : '') + '> Req</label>';
        html += '<button class="btn-rm" onclick="removeField(\'' + fieldRowId + '\')"><i class="fa fa-times"></i></button>';
        html += '</div>';

        // Options row (for select/checkbox/radio/risk)
        if (needsOptions.indexOf(type) !== -1) {
            var opts = (data && data.options) ? data.options.join(', ') : '';
            html += '<div class="fb-options-input" id="' + fieldRowId + '_opts" style="margin-left:0;margin-bottom:8px;padding:0 12px;">';
            html += '<label>Options (comma separated):</label>';
            html += '<input type="text" value="' + esc(opts) + '" class="field-options-input">';
            html += '</div>';
        }

        // Columns row (for table)
        if (needsColumns.indexOf(type) !== -1) {
            var cols = (data && data.columns) ? data.columns.join(', ') : '';
            html += '<div class="fb-columns-input" id="' + fieldRowId + '_cols" style="margin-left:0;margin-bottom:8px;padding:0 12px;">';
            html += '<label>Columns (comma separated):</label>';
            html += '<input type="text" value="' + esc(cols) + '" class="field-columns-input">';
            html += '</div>';
        }

        $('#' + secId + ' .sec-fields').append(html);
    };

    window.removeField = function(fieldRowId) {
        $('#' + fieldRowId).remove();
        $('#' + fieldRowId + '_opts').remove();
        $('#' + fieldRowId + '_cols').remove();
    };

    window.onFieldTypeChange = function(fieldRowId) {
        var type = $('#' + fieldRowId + ' .field-type-select').val();
        $('#' + fieldRowId + '_opts').remove();
        $('#' + fieldRowId + '_cols').remove();

        if (needsOptions.indexOf(type) !== -1) {
            var html = '<div class="fb-options-input" id="' + fieldRowId + '_opts" style="margin-left:0;margin-bottom:8px;padding:0 12px;">';
            html += '<label>Options (comma separated):</label>';
            html += '<input type="text" class="field-options-input">';
            html += '</div>';
            $('#' + fieldRowId).after(html);
        } else if (needsColumns.indexOf(type) !== -1) {
            var html2 = '<div class="fb-columns-input" id="' + fieldRowId + '_cols" style="margin-left:0;margin-bottom:8px;padding:0 12px;">';
            html2 += '<label>Columns (comma separated):</label>';
            html2 += '<input type="text" class="field-columns-input">';
            html2 += '</div>';
            $('#' + fieldRowId).after(html2);
        }
    };

    // ── Add Section button ──
    $('#btnAddSection').on('click', function() { addSection(); });

    // ── Gather editor data into form JSON ──
    function gatherEditorJson() {
        var title = $('#editorTitle').val().trim();
        if (!title) {
            fbToast('Please enter a form title.', 'error');
            return null;
        }

        var formJson = {
            formTitle: title,
            formDescription: $('#editorDesc').val().trim(),
            sections: []
        };

        var valid = true;
        $('#editorSections .fb-editor-section').each(function() {
            var secTitle = $(this).find('.sec-title-input').val().trim();
            if (!secTitle) secTitle = 'Untitled Section';

            var fields = [];
            $(this).find('.fb-editor-field-row').each(function() {
                var fLabel = $(this).find('.field-label-input').val().trim();
                if (!fLabel) return;

                var fType = $(this).find('.field-type-select').val();
                var fReq = $(this).find('.field-req-check').is(':checked');
                var fId = fLabel.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                if (!fId) fId = 'field_' + Math.random().toString(36).substr(2, 6);

                var field = { id: fId, label: fLabel, type: fType, required: fReq };

                var fieldRowId = $(this).attr('id');
                if (needsOptions.indexOf(fType) !== -1) {
                    var optsStr = $('#' + fieldRowId + '_opts .field-options-input').val() || '';
                    var opts = optsStr.split(',').map(function(o) { return o.trim(); }).filter(function(o) { return o; });
                    if (opts.length === 0) {
                        if (fType === 'risk') opts = ['Low', 'Medium', 'High'];
                        else { valid = false; fbToast('Field "' + fLabel + '" needs options.', 'error'); }
                    }
                    field.options = opts;
                }

                if (needsColumns.indexOf(fType) !== -1) {
                    var colsStr = $('#' + fieldRowId + '_cols .field-columns-input').val() || '';
                    var cols = colsStr.split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
                    if (cols.length === 0) { valid = false; fbToast('Table field "' + fLabel + '" needs columns.', 'error'); }
                    field.columns = cols;
                    field.rows = 3;
                }

                fields.push(field);
            });

            formJson.sections.push({ title: secTitle, fields: fields });
        });

        if (!valid) return null;
        if (formJson.sections.length === 0) {
            fbToast('Add at least one section.', 'error');
            return null;
        }

        return formJson;
    }

    // ── Save manual template ──
    $('#btnSaveManualTemplate').on('click', function() {
        var formJson = gatherEditorJson();
        if (!formJson) return;
        var $btn = $(this);
        $btn.prop('disabled', true);

        var data = {
            title: formJson.formTitle,
            description: formJson.formDescription,
            form_json: JSON.stringify(formJson)
        };

        if (editingTemplateId) {
            $.ajax({
                url: fbBaseUrl + '/template/' + editingTemplateId,
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status) {
                        fbToast('Template updated.', 'success');
                        // Update in local data
                        for (var i = 0; i < fbTemplatesData.length; i++) {
                            if (fbTemplatesData[i].id === editingTemplateId) {
                                fbTemplatesData[i].title = formJson.formTitle;
                                var fc = 0;
                                for (var s = 0; s < formJson.sections.length; s++) fc += formJson.sections[s].fields.length;
                                fbTemplatesData[i].section_count = formJson.sections.length;
                                fbTemplatesData[i].field_count = fc;
                                break;
                            }
                        }
                        renderTemplateGrid();
                    } else {
                        fbToast(res.error || 'Failed to update template.', 'error');
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to save.';
                    if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    fbToast(msg, 'error');
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        } else {
            $.ajax({
                url: fbBaseUrl + '/template',
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status && res.template) {
                        var fc = 0;
                        for (var s = 0; s < formJson.sections.length; s++) fc += formJson.sections[s].fields.length;
                        fbTemplatesData.unshift({
                            id: res.template.id,
                            title: formJson.formTitle,
                            description: formJson.formDescription,
                            ai_generated: false,
                            section_count: formJson.sections.length,
                            field_count: fc,
                            created_at: res.template.created_at || ''
                        });
                        renderTemplateGrid();
                        fbToast('Template created.', 'success');
                        clearEditor();
                    } else {
                        fbToast(res.error || 'Failed to create template.', 'error');
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to save.';
                    if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    fbToast(msg, 'error');
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        }
    });

    // ── Preview ──
    $('#btnPreviewTemplate').on('click', function() {
        var formJson = gatherEditorJson();
        if (!formJson) return;

        // Temporarily set as current template and render
        var prevTemplate = null;
        if (typeof openFillForm !== 'undefined') {
            // Create a fake template object for preview
            var fakeTemplate = {
                id: editingTemplateId || 0,
                title: formJson.formTitle,
                form_json: formJson
            };
            // Use the renderer directly
            window._previewTemplate = fakeTemplate;
            window._previewMode = true;

            // Render inline
            var $content = $('#fbRendererContent');
            $content.empty();
            // Build minimal preview
            var html = '<div class="fb-form-title">' + esc(formJson.formTitle) + '</div>';
            if (formJson.formDescription) html += '<div class="fb-form-desc">' + esc(formJson.formDescription) + '</div>';
            html += '<div style="padding:10px 0;color:#8e44ad;font-weight:600;font-size:13px;"><i class="fa fa-eye"></i> Preview Mode — fields are not fillable</div>';

            for (var i = 0; i < formJson.sections.length; i++) {
                var sec = formJson.sections[i];
                html += '<div class="fb-section"><div class="fb-section-title">' + esc(sec.title) + '</div>';
                for (var j = 0; j < sec.fields.length; j++) {
                    var f = sec.fields[j];
                    html += '<div class="fb-field"><label>' + esc(f.label);
                    if (f.required) html += '<span class="req">*</span>';
                    html += '</label>';
                    html += '<div style="color:#999;font-size:12px;font-style:italic;">Type: ' + esc(f.type);
                    if (f.options) html += ' | Options: ' + esc(f.options.join(', '));
                    if (f.columns) html += ' | Columns: ' + esc(f.columns.join(', '));
                    html += '</div></div>';
                }
                html += '</div>';
            }
            html += '<div class="fb-form-actions"><button class="btn btn-print" onclick="$(\'#fbRendererOverlay\').removeClass(\'active\')">Close Preview</button></div>';
            $content.html(html);
            $('#fbRendererOverlay').addClass('active');
        }
    });

    function clearEditor() {
        editingTemplateId = null;
        $('#editorTitle').val('');
        $('#editorDesc').val('');
        $('#editorSections').empty();
        sectionCounter = 0;
    }

    // ── Init editor with one empty section ──
    addSection();
})();
