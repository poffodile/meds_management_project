(function() {
    'use strict';

    var currentTemplate = null;
    var currentSubmissionId = null;
    var currentValues = {};
    var signatureCanvases = {};
    var aiFilled = false;

    // ── Open fill form for a template ──
    window.openFillForm = function(templateId) {
        currentSubmissionId = null;
        aiFilled = false;
        $.get(fbBaseUrl + '/template/' + templateId, function(res) {
            if (res.status && res.template) {
                currentTemplate = res.template;
                currentValues = {};
                renderForm();
            } else {
                fbToast('Template not found.', 'error');
            }
        }).fail(function() { fbToast('Failed to load template.', 'error'); });
    };

    // ── Open edit submission ──
    window.openEditSubmission = function(submissionId) {
        $.get(fbBaseUrl + '/submission/' + submissionId, function(res) {
            if (res.status && res.template) {
                currentTemplate = res.template;
                currentSubmissionId = res.submission.id;
                currentValues = res.submission.values_json || {};
                aiFilled = res.submission.ai_filled;
                renderForm();
            } else {
                fbToast('Submission not found.', 'error');
            }
        }).fail(function() { fbToast('Failed to load form.', 'error'); });
    };

    // ── Edit template (load into editor) ──
    window.openEditTemplate = function(templateId) {
        $.get(fbBaseUrl + '/template/' + templateId, function(res) {
            if (res.status && res.template) {
                // Switch to Create tab and load into editor
                $('.fb-tab').removeClass('active');
                $('.fb-tab[data-tab="create"]').addClass('active');
                $('.fb-tab-content').removeClass('active');
                $('#createTab').addClass('active');
                if (typeof loadTemplateIntoEditor === 'function') {
                    loadTemplateIntoEditor(res.template);
                }
            }
        }).fail(function() { fbToast('Failed to load template.', 'error'); });
    };

    // ── Render the form ──
    function renderForm() {
        var fj = currentTemplate.form_json;
        if (!fj || !fj.sections) { fbToast('Invalid template.', 'error'); return; }

        signatureCanvases = {};
        var $content = $('#fbRendererContent');
        $content.empty();

        // Title + description
        var html = '<div class="fb-form-title">' + esc(fj.formTitle || currentTemplate.title) + '</div>';
        if (fj.formDescription) html += '<div class="fb-form-desc">' + esc(fj.formDescription) + '</div>';

        // Progress bar
        html += '<div class="fb-progress-text" id="fbProgressText">0%</div>';
        html += '<div class="fb-progress-bar"><div class="fb-progress-fill" id="fbProgressFill" style="width:0%"></div></div>';

        // Client bar
        html += '<div class="fb-client-bar">';
        html += '<label style="font-size:13px;font-weight:600;color:#555;white-space:nowrap;">Client:</label>';
        html += '<select id="fbClientSelect"><option value="">— Select Client (optional) —</option>';
        for (var ci = 0; ci < fbClients.length; ci++) {
            html += '<option value="' + fbClients[ci].id + '">' + esc(fbClients[ci].name) + '</option>';
        }
        html += '</select>';
        html += '<button class="btn-ai-fill" id="btnAiFill"><i class="fa fa-magic"></i> AI Fill</button>';
        html += '</div>';

        // Sections + fields
        for (var i = 0; i < fj.sections.length; i++) {
            var sec = fj.sections[i];
            html += '<div class="fb-section">';
            html += '<div class="fb-section-title">' + esc(sec.title) + '</div>';
            var fields = sec.fields || [];
            for (var j = 0; j < fields.length; j++) {
                html += renderField(fields[j]);
            }
            html += '</div>';
        }

        // Actions
        html += '<div class="fb-form-actions">';
        html += '<button class="btn btn-save-form" id="btnSaveForm"><i class="fa fa-save"></i> Save Form</button>';
        html += '<button class="btn btn-print" onclick="printCurrentForm(false)"><i class="fa fa-print"></i> Print Filled</button>';
        html += '<button class="btn btn-print" onclick="printCurrentForm(true)"><i class="fa fa-print"></i> Print Blank</button>';
        html += '</div>';

        $content.html(html);

        // Pre-fill existing values
        if (currentValues && Object.keys(currentValues).length > 0) {
            populateValues(currentValues);
        }

        // Init signature canvases
        initSignatures();

        // Show overlay
        $('#fbRendererOverlay').addClass('active');

        // Bind events
        bindFormEvents();
        updateProgress();
    }

    // ── Render a single field ──
    function renderField(field) {
        if (field.type === 'info') {
            return '<div class="fb-field">'
                + '<div class="fb-info-content">' + esc(field.content || field.label) + '</div>'
                + '</div>';
        }

        var html = '<div class="fb-field" data-field-id="' + esc(field.id) + '">';
        html += '<label>' + esc(field.label);
        if (field.required) html += '<span class="req">*</span>';
        html += '</label>';
        if (field.hint) html += '<div class="hint">' + esc(field.hint) + '</div>';

        switch (field.type) {
            case 'text':
                html += '<input type="text" data-id="' + esc(field.id) + '" class="fb-input">';
                break;
            case 'textarea':
                html += '<textarea data-id="' + esc(field.id) + '" class="fb-input" rows="4"></textarea>';
                break;
            case 'date':
                html += '<input type="date" data-id="' + esc(field.id) + '" class="fb-input">';
                break;
            case 'number':
                html += '<input type="number" data-id="' + esc(field.id) + '" class="fb-input">';
                break;
            case 'email':
                html += '<input type="email" data-id="' + esc(field.id) + '" class="fb-input">';
                break;
            case 'tel':
                html += '<input type="tel" data-id="' + esc(field.id) + '" class="fb-input">';
                break;
            case 'select':
                html += '<select data-id="' + esc(field.id) + '" class="fb-input"><option value="">— Select —</option>';
                var opts = field.options || [];
                for (var k = 0; k < opts.length; k++) {
                    html += '<option value="' + esc(opts[k]) + '">' + esc(opts[k]) + '</option>';
                }
                html += '</select>';
                break;
            case 'checkbox':
                html += '<div class="fb-option-group" data-id="' + esc(field.id) + '" data-type="checkbox">';
                var cOpts = field.options || [];
                for (var c = 0; c < cOpts.length; c++) {
                    html += '<label class="fb-option-item"><input type="checkbox" value="' + esc(cOpts[c]) + '"> ' + esc(cOpts[c]) + '</label>';
                }
                html += '</div>';
                break;
            case 'radio':
                html += '<div class="fb-option-group" data-id="' + esc(field.id) + '" data-type="radio">';
                var rOpts = field.options || [];
                for (var r = 0; r < rOpts.length; r++) {
                    html += '<label class="fb-option-item"><input type="radio" name="radio_' + esc(field.id) + '" value="' + esc(rOpts[r]) + '"> ' + esc(rOpts[r]) + '</label>';
                }
                html += '</div>';
                break;
            case 'risk':
                html += '<div class="fb-risk-group" data-id="' + esc(field.id) + '">';
                var riskOpts = field.options || ['Low', 'Medium', 'High'];
                for (var ri = 0; ri < riskOpts.length; ri++) {
                    html += '<div class="fb-risk-btn" data-val="' + esc(riskOpts[ri]) + '">' + esc(riskOpts[ri]) + '</div>';
                }
                html += '</div>';
                break;
            case 'signature':
                html += '<div class="fb-sig-wrap" data-id="' + esc(field.id) + '">';
                html += '<canvas class="fb-sig-canvas" id="sig_' + esc(field.id) + '" width="400" height="120"></canvas>';
                html += '<button class="fb-sig-clear" data-sig="' + esc(field.id) + '">Clear Signature</button>';
                html += '</div>';
                break;
            case 'table':
                html += renderTableField(field);
                break;
        }

        html += '</div>';
        return html;
    }

    function renderTableField(field) {
        var cols = field.columns || [];
        var numRows = field.rows || 3;
        var html = '<div class="fb-table-wrap" data-id="' + esc(field.id) + '" data-type="table">';
        html += '<table class="fb-table"><thead><tr>';
        for (var c = 0; c < cols.length; c++) {
            html += '<th>' + esc(cols[c]) + '</th>';
        }
        html += '</tr></thead><tbody>';
        for (var r = 0; r < numRows; r++) {
            html += '<tr>';
            for (var c2 = 0; c2 < cols.length; c2++) {
                html += '<td><input type="text" data-row="' + r + '" data-col="' + c2 + '"></td>';
            }
            html += '</tr>';
        }
        html += '</tbody></table>';
        html += '<button class="btn-add-row" data-table="' + esc(field.id) + '"><i class="fa fa-plus"></i> Add Row</button>';
        html += '</div>';
        return html;
    }

    // ── Populate values into the form ──
    function populateValues(vals) {
        for (var id in vals) {
            if (!vals.hasOwnProperty(id)) continue;
            var val = vals[id];
            if (val === null || val === undefined) continue;

            var $field = $('[data-field-id="' + id + '"]');
            if ($field.length) $field.addClass('ai-filled');

            // Simple inputs
            var $input = $('.fb-input[data-id="' + id + '"]');
            if ($input.length) {
                $input.val(val);
                continue;
            }

            // Checkbox
            var $cbGroup = $('.fb-option-group[data-id="' + id + '"][data-type="checkbox"]');
            if ($cbGroup.length && Array.isArray(val)) {
                $cbGroup.find('input[type="checkbox"]').each(function() {
                    if (val.indexOf($(this).val()) !== -1) $(this).prop('checked', true);
                });
                continue;
            }

            // Radio
            var $radioGroup = $('.fb-option-group[data-id="' + id + '"][data-type="radio"]');
            if ($radioGroup.length) {
                $radioGroup.find('input[value="' + val + '"]').prop('checked', true);
                continue;
            }

            // Risk
            var $riskGroup = $('.fb-risk-group[data-id="' + id + '"]');
            if ($riskGroup.length) {
                $riskGroup.find('.fb-risk-btn').removeClass('selected');
                $riskGroup.find('[data-val="' + val + '"]').addClass('selected');
                continue;
            }

            // Table
            var $tableWrap = $('.fb-table-wrap[data-id="' + id + '"]');
            if ($tableWrap.length && val && val.rows) {
                populateTable($tableWrap, val);
                continue;
            }

            // Signature — handled separately (canvas)
        }
    }

    function populateTable($wrap, tableData) {
        var $tbody = $wrap.find('tbody');
        var rows = tableData.rows || [];
        var cols = $wrap.find('thead th').length;

        // Ensure enough rows
        while ($tbody.find('tr').length < rows.length) {
            var rowHtml = '<tr>';
            for (var c = 0; c < cols; c++) {
                rowHtml += '<td><input type="text" data-row="' + $tbody.find('tr').length + '" data-col="' + c + '"></td>';
            }
            rowHtml += '</tr>';
            $tbody.append(rowHtml);
        }

        for (var r = 0; r < rows.length; r++) {
            for (var c2 = 0; c2 < (rows[r] || []).length; c2++) {
                $tbody.find('input[data-row="' + r + '"][data-col="' + c2 + '"]').val(rows[r][c2] || '');
            }
        }
    }

    // ── Gather values from the form ──
    function gatherValues() {
        var vals = {};
        var fj = currentTemplate.form_json;
        if (!fj || !fj.sections) return vals;

        for (var i = 0; i < fj.sections.length; i++) {
            var fields = fj.sections[i].fields || [];
            for (var j = 0; j < fields.length; j++) {
                var f = fields[j];
                if (f.type === 'info') continue;

                if (['text', 'textarea', 'date', 'number', 'email', 'tel', 'select'].indexOf(f.type) !== -1) {
                    var $inp = $('.fb-input[data-id="' + f.id + '"]');
                    if ($inp.length) vals[f.id] = $inp.val();
                } else if (f.type === 'checkbox') {
                    var checked = [];
                    $('.fb-option-group[data-id="' + f.id + '"] input:checked').each(function() {
                        checked.push($(this).val());
                    });
                    vals[f.id] = checked;
                } else if (f.type === 'radio') {
                    var sel = $('.fb-option-group[data-id="' + f.id + '"] input:checked').val();
                    vals[f.id] = sel || '';
                } else if (f.type === 'risk') {
                    var riskVal = $('.fb-risk-group[data-id="' + f.id + '"] .fb-risk-btn.selected').attr('data-val');
                    vals[f.id] = riskVal || '';
                } else if (f.type === 'signature') {
                    var canvas = signatureCanvases[f.id];
                    if (canvas && canvas.hasDrawn) {
                        vals[f.id] = document.getElementById('sig_' + f.id).toDataURL('image/png');
                    } else {
                        vals[f.id] = currentValues[f.id] || '';
                    }
                } else if (f.type === 'table') {
                    vals[f.id] = gatherTableValues(f.id, f.columns);
                }
            }
        }
        return vals;
    }

    function gatherTableValues(fieldId, columns) {
        var $wrap = $('.fb-table-wrap[data-id="' + fieldId + '"]');
        if (!$wrap.length) return { headers: columns || [], rows: [] };
        var rows = [];
        $wrap.find('tbody tr').each(function() {
            var row = [];
            $(this).find('input').each(function() { row.push($(this).val()); });
            rows.push(row);
        });
        return { headers: columns || [], rows: rows };
    }

    // ── Signature canvases ──
    function initSignatures() {
        var fj = currentTemplate.form_json;
        if (!fj || !fj.sections) return;

        for (var i = 0; i < fj.sections.length; i++) {
            var fields = fj.sections[i].fields || [];
            for (var j = 0; j < fields.length; j++) {
                if (fields[j].type === 'signature') {
                    initSignatureCanvas(fields[j].id);
                }
            }
        }
    }

    function initSignatureCanvas(fieldId) {
        var canvas = document.getElementById('sig_' + fieldId);
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var drawing = false;
        var state = { hasDrawn: false };
        signatureCanvases[fieldId] = state;

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX, clientY;
            if (e.touches) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }
            return { x: clientX - rect.left, y: clientY - rect.top };
        }

        canvas.addEventListener('mousedown', function(e) {
            drawing = true;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });
        canvas.addEventListener('mousemove', function(e) {
            if (!drawing) return;
            var pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
            state.hasDrawn = true;
        });
        canvas.addEventListener('mouseup', function() { drawing = false; });
        canvas.addEventListener('mouseleave', function() { drawing = false; });

        // Touch support
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            drawing = true;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });
        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (!drawing) return;
            var pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
            state.hasDrawn = true;
        });
        canvas.addEventListener('touchend', function() { drawing = false; });

        // If existing signature, draw it
        if (currentValues[fieldId] && currentValues[fieldId].indexOf && currentValues[fieldId].indexOf('data:image') === 0) {
            var img = new Image();
            img.onload = function() { ctx.drawImage(img, 0, 0); };
            img.src = currentValues[fieldId];
        }
    }

    // ── Bind form events ──
    function bindFormEvents() {
        // Close renderer
        $('#btnCloseRenderer').off('click').on('click', function() {
            $('#fbRendererOverlay').removeClass('active');
        });
        // Close on overlay click
        $('#fbRendererOverlay').off('click.close').on('click.close', function(e) {
            if (e.target === this) $(this).removeClass('active');
        });

        // Risk buttons
        $(document).off('click.risk').on('click.risk', '.fb-risk-btn', function() {
            $(this).parent().find('.fb-risk-btn').removeClass('selected');
            $(this).addClass('selected');
            updateProgress();
        });

        // Signature clear
        $(document).off('click.sigclear').on('click.sigclear', '.fb-sig-clear', function() {
            var id = $(this).data('sig');
            var canvas = document.getElementById('sig_' + id);
            if (canvas) {
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (signatureCanvases[id]) signatureCanvases[id].hasDrawn = false;
            }
        });

        // Add table row
        $(document).off('click.addrow').on('click.addrow', '.btn-add-row', function() {
            var tableId = $(this).data('table');
            var $wrap = $('.fb-table-wrap[data-id="' + tableId + '"]');
            var cols = $wrap.find('thead th').length;
            var rowIdx = $wrap.find('tbody tr').length;
            var rowHtml = '<tr>';
            for (var c = 0; c < cols; c++) {
                rowHtml += '<td><input type="text" data-row="' + rowIdx + '" data-col="' + c + '"></td>';
            }
            rowHtml += '</tr>';
            $wrap.find('tbody').append(rowHtml);
        });

        // Progress tracking
        $(document).off('input.progress change.progress').on('input.progress change.progress', '.fb-input, .fb-option-group input', function() {
            updateProgress();
        });

        // AI Fill
        $('#btnAiFill').off('click').on('click', function() {
            var clientId = $('#fbClientSelect').val();
            if (!clientId) {
                fbToast('Please select a client first.', 'error');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> AI Filling...');

            $.ajax({
                url: fbBaseUrl + '/ai-fill',
                method: 'POST',
                data: { template_id: currentTemplate.id, client_id: clientId },
                success: function(res) {
                    if (res.status) {
                        aiFilled = true;
                        populateValues(res.values);
                        updateProgress();
                        fbToast('AI filled ' + res.filled_count + ' of ' + res.total_fields + ' fields.', 'success');
                    } else {
                        fbToast(res.error || 'AI fill failed.', 'error');
                    }
                },
                error: function(xhr) {
                    var msg = 'AI fill failed.';
                    if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    fbToast(msg, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-magic"></i> AI Fill');
                }
            });
        });

        // Save form
        $('#btnSaveForm').off('click').on('click', function() {
            var vals = gatherValues();
            var clientId = $('#fbClientSelect').val() || null;
            var $btn = $(this);
            $btn.prop('disabled', true);

            if (currentSubmissionId) {
                // Update existing
                $.ajax({
                    url: fbBaseUrl + '/submission/' + currentSubmissionId,
                    method: 'POST',
                    data: { values_json: JSON.stringify(vals) },
                    success: function(res) {
                        if (res.status) {
                            fbToast('Form saved.', 'success');
                        } else {
                            fbToast(res.error || 'Failed to save.', 'error');
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Failed to save form.';
                        if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                        fbToast(msg, 'error');
                    },
                    complete: function() { $btn.prop('disabled', false); }
                });
            } else {
                // Create new
                $.ajax({
                    url: fbBaseUrl + '/submission',
                    method: 'POST',
                    data: {
                        template_id: currentTemplate.id,
                        client_id: clientId,
                        values_json: JSON.stringify(vals),
                        ai_filled: aiFilled ? 1 : 0
                    },
                    success: function(res) {
                        if (res.status) {
                            currentSubmissionId = res.submission_id;
                            fbToast('Form saved.', 'success');
                        } else {
                            fbToast(res.error || 'Failed to save.', 'error');
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Failed to save form.';
                        if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                        fbToast(msg, 'error');
                    },
                    complete: function() { $btn.prop('disabled', false); }
                });
            }
        });
    }

    // ── Progress bar ──
    function updateProgress() {
        var fj = currentTemplate ? currentTemplate.form_json : null;
        if (!fj || !fj.sections) return;
        var total = 0, filled = 0;

        for (var i = 0; i < fj.sections.length; i++) {
            var fields = fj.sections[i].fields || [];
            for (var j = 0; j < fields.length; j++) {
                var f = fields[j];
                if (f.type === 'info' || f.type === 'signature') continue;
                total++;

                if (['text', 'textarea', 'date', 'number', 'email', 'tel', 'select'].indexOf(f.type) !== -1) {
                    if ($('.fb-input[data-id="' + f.id + '"]').val()) filled++;
                } else if (f.type === 'checkbox') {
                    if ($('.fb-option-group[data-id="' + f.id + '"] input:checked').length > 0) filled++;
                } else if (f.type === 'radio') {
                    if ($('.fb-option-group[data-id="' + f.id + '"] input:checked').length > 0) filled++;
                } else if (f.type === 'risk') {
                    if ($('.fb-risk-group[data-id="' + f.id + '"] .selected').length > 0) filled++;
                } else if (f.type === 'table') {
                    var hasData = false;
                    $('.fb-table-wrap[data-id="' + f.id + '"] tbody input').each(function() {
                        if ($(this).val()) hasData = true;
                    });
                    if (hasData) filled++;
                }
            }
        }

        var pct = total > 0 ? Math.round((filled / total) * 100) : 0;
        $('#fbProgressFill').css('width', pct + '%');
        $('#fbProgressText').text(pct + '% (' + filled + '/' + total + ' fields)');
    }

    // ── Print current form ──
    window.printCurrentForm = function(blank) {
        var vals = blank ? {} : gatherValues();
        var fj = currentTemplate.form_json;
        printForm(fj, fj.formTitle || currentTemplate.title, vals);
    };
})();
