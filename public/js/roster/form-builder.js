(function() {
    'use strict';

    var selectedFiles = [];

    // ── Tab switching ──
    $('.fb-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.fb-tab').removeClass('active');
        $(this).addClass('active');
        $('.fb-tab-content').removeClass('active');
        if (tab === 'templates') $('#templatesTab').addClass('active');
        else if (tab === 'saved') { $('#savedTab').addClass('active'); loadSubmissions(); }
        else if (tab === 'create') $('#createTab').addClass('active');
    });

    // ── Toast ──
    window.fbToast = function(msg, type) {
        var $t = $('#fbToast');
        $t.text(msg).removeClass('success error').addClass(type).fadeIn(300);
        setTimeout(function() { $t.fadeOut(300); }, 3000);
    };

    // ── Drag & Drop ──
    var $dz = $('#fbDropzone');
    $dz.on('click', function() { $('#fbFileInput').trigger('click'); });
    $dz.on('dragover', function(e) { e.preventDefault(); $dz.addClass('dragover'); });
    $dz.on('dragleave drop', function() { $dz.removeClass('dragover'); });
    $dz.on('drop', function(e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) addFiles(files);
    });
    $('#fbFileInput').on('change', function() {
        if (this.files.length > 0) addFiles(this.files);
    });

    function addFiles(fileList) {
        for (var i = 0; i < fileList.length; i++) {
            var file = fileList[i];
            var ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'docx', 'doc'].includes(ext)) {
                fbToast(esc(file.name) + ' — only PDF and Word documents are accepted.', 'error');
                continue;
            }
            if (file.size > 10 * 1024 * 1024) {
                fbToast(esc(file.name) + ' — file is too large (max 10MB).', 'error');
                continue;
            }
            var alreadyAdded = selectedFiles.some(function(f) { return f.name === file.name && f.size === file.size; });
            if (!alreadyAdded) {
                selectedFiles.push(file);
            }
        }
        renderFileList();
        $('#btnGenerate').prop('disabled', selectedFiles.length === 0);
    }

    function renderFileList() {
        var $container = $('#fbSelectedFiles');
        $container.empty();

        if (selectedFiles.length === 0) {
            $container.hide();
            return;
        }

        $container.show();
        $container.append('<div class="fb-file-count">' + selectedFiles.length + ' file' + (selectedFiles.length > 1 ? 's' : '') + ' selected</div>');

        for (var i = 0; i < selectedFiles.length; i++) {
            var f = selectedFiles[i];
            var html = '<div class="fb-file-item" data-index="' + i + '">'
                + '<i class="fa fa-file"></i>'
                + '<span class="fb-file-name">' + esc(f.name) + '</span>'
                + '<span class="fb-file-status" id="fbFileStatus' + i + '"></span>'
                + '<button class="fb-file-remove" data-index="' + i + '" title="Remove"><i class="fa fa-times"></i></button>'
                + '</div>';
            $container.append(html);
        }

        $container.find('.fb-file-remove').on('click', function(e) {
            e.stopPropagation();
            var idx = $(this).data('index');
            selectedFiles.splice(idx, 1);
            renderFileList();
            $('#btnGenerate').prop('disabled', selectedFiles.length === 0);
        });
    }

    // ── Generate Templates (sequential processing) ──
    $('#btnGenerate').on('click', function() {
        if (selectedFiles.length === 0) return;
        var $btn = $(this);
        $btn.prop('disabled', true);

        var total = selectedFiles.length;
        var completed = 0;
        var failed = 0;

        $btn.html('<i class="fa fa-spinner fa-spin"></i> Processing 1 of ' + total + '...');
        $('.fb-file-remove').prop('disabled', true).css('opacity', '0.3');

        function processNext(index) {
            if (index >= total) {
                var msg = completed + ' template' + (completed !== 1 ? 's' : '') + ' created';
                if (failed > 0) msg += ', ' + failed + ' failed';
                fbToast(msg, failed === 0 ? 'success' : 'error');
                $btn.prop('disabled', false).html('<i class="fa fa-magic"></i> Generate Templates');
                selectedFiles = [];
                renderFileList();
                $('#fbFileInput').val('');
                return;
            }

            $btn.html('<i class="fa fa-spinner fa-spin"></i> Processing ' + (index + 1) + ' of ' + total + '...');
            $('#fbFileStatus' + index).text('Processing...').removeClass('done error');

            var fd = new FormData();
            fd.append('file', selectedFiles[index]);

            $.ajax({
                url: fbBaseUrl + '/upload',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status) {
                        completed++;
                        $('#fbFileStatus' + index).text('Done').addClass('done');
                        fbTemplatesData.unshift(res.template);
                        renderTemplateGrid();
                    } else {
                        failed++;
                        $('#fbFileStatus' + index).text(res.error || 'Failed').addClass('error');
                    }
                },
                error: function(xhr) {
                    failed++;
                    var msg = 'Failed';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.errors) {
                            // Handle Laravel validation errors
                            var errors = xhr.responseJSON.errors;
                            var firstKey = Object.keys(errors)[0];
                            if (firstKey && errors[firstKey][0]) {
                                msg = errors[firstKey][0];
                            }
                        }
                    }
                    $('#fbFileStatus' + index).text(msg).addClass('error');
                },
                complete: function() {
                    setTimeout(function() { processNext(index + 1); }, 2000);
                }
            });
        }

        processNext(0);
    });

    // ── Render Template Grid ──
    window.renderTemplateGrid = function() {
        var $grid = $('#fbTemplateGrid');
        $grid.empty();

        if (!fbTemplatesData || fbTemplatesData.length === 0) {
            $grid.hide();
            $('#fbTemplateEmpty').show();
            return;
        }
        $('#fbTemplateEmpty').hide();
        $grid.show();

        var $filterTpl = $('#filterTemplate');
        $filterTpl.find('option:gt(0)').remove();

        for (var i = 0; i < fbTemplatesData.length; i++) {
            var t = fbTemplatesData[i];
            var badge = t.ai_generated ?
                '<span class="fb-card-badge fb-badge-ai"><i class="fa fa-magic"></i> AI Generated</span>' :
                '<span class="fb-card-badge fb-badge-manual">Manual</span>';

            var sectionCount = t.section_count || 0;
            var fieldCount = t.field_count || 0;

            var html = '<div class="fb-card" data-id="' + t.id + '">'
                + '<div class="fb-card-title">' + esc(t.title) + '</div>'
                + badge
                + '<div class="fb-card-meta">'
                + '<span>' + sectionCount + ' section' + (sectionCount !== 1 ? 's' : '') + '</span>'
                + '<span>' + fieldCount + ' field' + (fieldCount !== 1 ? 's' : '') + '</span>'
                + '</div>'
                + '<div class="fb-card-meta">' + esc(t.created_at || '') + '</div>'
                + '<div class="fb-card-actions">'
                + '<button class="btn btn-fill" onclick="openFillForm(' + t.id + ')"><i class="fa fa-pencil-square-o"></i> Fill</button>'
                + '<button class="btn" onclick="openEditTemplate(' + t.id + ')"><i class="fa fa-edit"></i> Edit</button>'
                + '<button class="btn" onclick="printBlankTemplate(' + t.id + ')"><i class="fa fa-print"></i> Print</button>'
                + '<button class="btn btn-del" onclick="deleteTemplate(' + t.id + ')"><i class="fa fa-trash"></i></button>'
                + '</div></div>';
            $grid.append(html);
            $filterTpl.append('<option value="' + t.id + '">' + esc(t.title) + '</option>');
        }
    };

    // ── Delete Template ──
    window.deleteTemplate = function(id) {
        if (!confirm('Delete this template? This cannot be undone.')) return;
        $.post(fbBaseUrl + '/template/' + id + '/delete', function(res) {
            if (res.status) {
                fbTemplatesData = fbTemplatesData.filter(function(t) { return t.id !== id; });
                renderTemplateGrid();
                fbToast('Template deleted.', 'success');
            }
        }).fail(function() { fbToast('Failed to delete template.', 'error'); });
    };

    // ── Print Blank Template ──
    window.printBlankTemplate = function(id) {
        $.get(fbBaseUrl + '/template/' + id, function(res) {
            if (res.status && res.template) {
                printForm(res.template.form_json, res.template.title, {});
            }
        });
    };

    // ── Load Submissions ──
    function loadSubmissions() {
        var clientId = $('#filterClient').val();
        var templateId = $('#filterTemplate').val();

        var url = fbBaseUrl + '/submission/list';
        if (clientId) {
            url = fbBaseUrl + '/client/' + clientId + '/submissions';
        }

        $.get(url, function(res) {
            if (res.status) {
                renderSubmissions(res.submissions || [], templateId);
            }
        }).fail(function() {
            renderSubmissions([], '');
        });
    }

    function renderSubmissions(submissions, templateFilter) {
        var $list = $('#fbSubmissionsList');
        $list.empty();

        var filtered = submissions;
        if (templateFilter) {
            filtered = submissions.filter(function(s) { return s.form_template_id == templateFilter; });
        }

        if (!filtered || filtered.length === 0) {
            $list.hide();
            $('#fbSubmissionsEmpty').show();
            return;
        }
        $('#fbSubmissionsEmpty').hide();
        $list.show();

        for (var i = 0; i < filtered.length; i++) {
            var s = filtered[i];
            var aiBadge = s.ai_filled ? '<span style="color:#8e44ad;font-weight:600;">AI Filled</span>' : '<span style="color:#999;">Manual</span>';
            var html = '<div class="fb-submission-row" data-id="' + s.id + '">'
                + '<div class="fb-sub-body">'
                + '<div class="fb-sub-title">' + esc(s.form_title) + '</div>'
                + '<div class="fb-sub-meta">'
                + '<span>By: ' + esc(s.submitted_by_name || '') + '</span>'
                + '<span>' + esc(s.created_at || '') + '</span>'
                + '<span>' + aiBadge + '</span>'
                + '</div></div>'
                + '<div class="fb-sub-actions">'
                + '<button class="btn btn-fill" onclick="openEditSubmission(' + s.id + ')"><i class="fa fa-eye"></i> View/Edit</button>'
                + '<button class="btn" onclick="printSubmission(' + s.id + ')"><i class="fa fa-print"></i></button>'
                + '<button class="btn btn-del" onclick="deleteSubmission(' + s.id + ')"><i class="fa fa-trash"></i></button>'
                + '</div></div>';
            $list.append(html);
        }
    }

    $('#filterClient, #filterTemplate').on('change', function() { loadSubmissions(); });

    // ── Delete Submission ──
    window.deleteSubmission = function(id) {
        if (!confirm('Delete this saved form?')) return;
        $.post(fbBaseUrl + '/submission/' + id + '/delete', function(res) {
            if (res.status) {
                fbToast('Form deleted.', 'success');
                loadSubmissions();
            }
        }).fail(function() { fbToast('Failed to delete.', 'error'); });
    };

    // ── Print Submission ──
    window.printSubmission = function(id) {
        $.get(fbBaseUrl + '/submission/' + id, function(res) {
            if (res.status && res.template) {
                printForm(res.template.form_json, res.submission.form_title, res.submission.values_json || {});
            }
        });
    };

    // ── Print form utility ──
    window.printForm = function(formJson, title, values) {
        var html = '<!DOCTYPE html><html><head><title>' + esc(title) + '</title>';
        html += '<style>body{font-family:Arial,sans-serif;padding:30px;font-size:13px;color:#333;}';
        html += 'h1{font-size:20px;margin-bottom:5px;} h2{font-size:16px;margin-top:25px;border-bottom:2px solid #333;padding-bottom:5px;}';
        html += '.field{margin-bottom:12px;} .label{font-weight:bold;} .value{border-bottom:1px solid #ccc;min-height:20px;padding:4px 0;}';
        html += 'table{width:100%;border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ccc;padding:6px 8px;text-align:left;}';
        html += 'th{background:#f0f0f0;font-weight:bold;} .info{background:#f8f9fa;padding:10px;border-left:3px solid #333;white-space:pre-wrap;margin:8px 0;}';
        html += '.sig-img{max-width:300px;max-height:100px;} @media print{body{padding:15px;}}</style></head><body>';
        html += '<h1>' + esc(title) + '</h1>';
        if (formJson.formDescription) html += '<p style="color:#666;">' + esc(formJson.formDescription) + '</p>';

        var sections = formJson.sections || [];
        for (var i = 0; i < sections.length; i++) {
            var sec = sections[i];
            html += '<h2>' + esc(sec.title) + '</h2>';
            var fields = sec.fields || [];
            for (var j = 0; j < fields.length; j++) {
                var f = fields[j];
                if (f.type === 'info') {
                    html += '<div class="info">' + esc(f.content || f.label) + '</div>';
                    continue;
                }
                if (f.type === 'table') {
                    html += '<div class="field"><div class="label">' + esc(f.label) + '</div>';
                    var tVal = values[f.id];
                    var cols = f.columns || [];
                    html += '<table><tr>';
                    for (var c = 0; c < cols.length; c++) html += '<th>' + esc(cols[c]) + '</th>';
                    html += '</tr>';
                    if (tVal && tVal.rows) {
                        for (var r = 0; r < tVal.rows.length; r++) {
                            html += '<tr>';
                            for (var c2 = 0; c2 < cols.length; c2++) {
                                html += '<td>' + esc((tVal.rows[r] && tVal.rows[r][c2]) || '') + '</td>';
                            }
                            html += '</tr>';
                        }
                    } else {
                        var numRows = f.rows || 3;
                        for (var r2 = 0; r2 < numRows; r2++) {
                            html += '<tr>';
                            for (var c3 = 0; c3 < cols.length; c3++) html += '<td>&nbsp;</td>';
                            html += '</tr>';
                        }
                    }
                    html += '</table></div>';
                    continue;
                }
                if (f.type === 'signature') {
                    html += '<div class="field"><div class="label">' + esc(f.label) + '</div>';
                    var sigVal = values[f.id];
                    if (sigVal && sigVal.indexOf('data:image') === 0) {
                        html += '<img class="sig-img" src="' + sigVal + '">';
                    } else {
                        html += '<div style="border:1px solid #ccc;height:60px;margin-top:4px;"></div>';
                    }
                    html += '</div>';
                    continue;
                }
                var val = values[f.id];
                var displayVal = '';
                if (Array.isArray(val)) displayVal = val.join(', ');
                else if (val != null) displayVal = String(val);
                html += '<div class="field"><div class="label">' + esc(f.label) + '</div>';
                html += '<div class="value">' + esc(displayVal) + '</div></div>';
            }
        }
        html += '</body></html>';

        var win = window.open('', '_blank');
        win.document.write(html);
        win.document.close();
        setTimeout(function() { win.print(); }, 500);
    };

    // ── Init ──
    renderTemplateGrid();

    if (fbFillTemplateId) {
        setTimeout(function() { openFillForm(fbFillTemplateId); }, 300);
    }
})();
