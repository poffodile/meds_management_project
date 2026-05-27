@extends('frontEnd.layouts.master')
@section('title','Form Builder')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">

    <style>
        .fb-container {
            padding: 20px 30px;
        }

        .fb-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .fb-subtitle {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .fb-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 25px;
        }

        .fb-tab {
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .fb-tab:hover {
            color: #555;
        }

        .fb-tab.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }

        .fb-tab-content {
            display: none;
        }

        .fb-tab-content.active {
            display: block;
        }

        /* Upload zone */
        .fb-upload-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .fb-upload-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .fb-upload-desc {
            font-size: 13px;
            color: #888;
            margin-bottom: 15px;
        }

        .fb-dropzone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }

        .fb-dropzone:hover,
        .fb-dropzone.dragover {
            border-color: #3498db;
            background: #f0f7ff;
        }

        .fb-dropzone i {
            font-size: 40px;
            color: #ccc;
            margin-bottom: 10px;
        }

        .fb-dropzone.dragover i {
            color: #3498db;
        }

        .fb-dropzone p {
            margin: 5px 0;
            color: #777;
            font-size: 14px;
        }

        .fb-dropzone .fb-file-types {
            font-size: 12px;
            color: #aaa;
        }

        .fb-selected-files {
            margin-top: 12px;
            font-size: 13px;
            color: #2c3e50;
        }

        .fb-selected-files .fb-file-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 0;
        }

        .fb-selected-files .fb-file-item i.fa-file {
            color: #27ae60;
        }

        .fb-selected-files .fb-file-item .fb-file-name {
            font-weight: 600;
            flex: 1;
        }

        .fb-selected-files .fb-file-item .fb-file-remove {
            color: #e74c3c;
            cursor: pointer;
            font-size: 14px;
            background: none;
            border: none;
            padding: 2px 6px;
        }

        .fb-selected-files .fb-file-item .fb-file-status {
            font-size: 12px;
            color: #888;
        }

        .fb-selected-files .fb-file-item .fb-file-status.done {
            color: #27ae60;
        }

        .fb-selected-files .fb-file-item .fb-file-status.error {
            color: #e74c3c;
        }

        .fb-selected-files .fb-file-count {
            font-size: 12px;
            color: #888;
            margin-bottom: 6px;
        }

        .btn-generate {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-generate:hover {
            background: #2980b9;
            color: #fff;
        }

        .btn-generate:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        /* Template cards */
        .fb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
        }

        .fb-card {
            background: #fff;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s;
        }

        .fb-card:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
        }

        .fb-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
        }

        .fb-card-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
        }

        .fb-card-meta span {
            margin-right: 12px;
        }

        .fb-card-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .fb-badge-ai {
            background: #eaf4fe;
            color: #2980b9;
        }

        .fb-badge-manual {
            background: #f0f0f0;
            color: #777;
        }

        .fb-card-actions {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .fb-card-actions .btn {
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            border: 1px solid #ddd;
            background: #fff;
            color: #555;
            cursor: pointer;
        }

        .fb-card-actions .btn:hover {
            background: #f8f9fa;
        }

        .fb-card-actions .btn-fill {
            background: #27ae60;
            color: #fff;
            border-color: #27ae60;
        }

        .fb-card-actions .btn-fill:hover {
            background: #219a52;
        }

        .fb-card-actions .btn-del {
            color: #e74c3c;
            border-color: #e74c3c;
        }

        .fb-card-actions .btn-del:hover {
            background: #fef2f2;
        }

        .fb-empty {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
        }

        .fb-empty i {
            font-size: 50px;
            margin-bottom: 15px;
            display: block;
        }

        /* Saved forms list */
        .fb-filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .fb-filter-bar select {
            padding: 8px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            background: #fff;
            color: #555;
            min-width: 180px;
        }

        .fb-submission-row {
            background: #fff;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .fb-sub-body {
            flex: 1;
        }

        .fb-sub-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .fb-sub-meta {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }

        .fb-sub-meta span {
            margin-right: 15px;
        }

        .fb-sub-actions {
            display: flex;
            gap: 6px;
        }

        .fb-sub-actions .btn {
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 6px;
        }

        /* Form renderer */
        .fb-renderer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            display: none;
            overflow-y: auto;
        }

        .fb-renderer-overlay.active {
            display: block;
        }

        .fb-renderer-panel {
            background: #fff;
            max-width: 900px;
            margin: 30px auto;
            border-radius: 12px;
            padding: 30px;
            position: relative;
            min-height: 400px;
        }

        .fb-renderer-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            line-height: 1;
        }

        .fb-renderer-close:hover {
            color: #333;
        }

        .fb-form-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .fb-form-desc {
            font-size: 13px;
            color: #888;
            margin-bottom: 20px;
        }

        .fb-progress-bar {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .fb-progress-fill {
            height: 100%;
            background: #27ae60;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .fb-progress-text {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
            text-align: right;
        }

        .fb-client-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .fb-client-bar select {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .btn-ai-fill {
            background: #8e44ad;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-ai-fill:hover {
            background: #7d3c98;
            color: #fff;
        }

        .btn-ai-fill:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .fb-section {
            margin-bottom: 25px;
        }

        .fb-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #34495e;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
            margin-bottom: 15px;
        }

        .fb-field {
            margin-bottom: 16px;
        }

        .fb-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }

        .fb-field label .req {
            color: #e74c3c;
            margin-left: 3px;
        }

        .fb-field .hint {
            font-size: 11px;
            color: #aaa;
            margin-bottom: 4px;
        }

        .fb-field input[type="text"],
        .fb-field input[type="date"],
        .fb-field input[type="number"],
        .fb-field input[type="email"],
        .fb-field input[type="tel"],
        .fb-field textarea,
        .fb-field select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            background: #fff;
            color: #333;
            box-sizing: border-box;
        }

        .fb-field textarea {
            resize: vertical;
            min-height: 80px;
        }

        .fb-field input:focus,
        .fb-field textarea:focus,
        .fb-field select:focus {
            border-color: #3498db;
            outline: none;
        }

        .fb-field.ai-filled input,
        .fb-field.ai-filled textarea,
        .fb-field.ai-filled select {
            border-color: #8e44ad;
            background: #faf5ff;
        }

        /* Risk rating */
        .fb-risk-group {
            display: flex;
            gap: 8px;
        }

        .fb-risk-btn {
            padding: 8px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            background: #fff;
            transition: all 0.2s;
        }

        .fb-risk-btn.selected {
            color: #fff;
        }

        .fb-risk-btn[data-val="Low"].selected {
            background: #27ae60;
            border-color: #27ae60;
        }

        .fb-risk-btn[data-val="Medium"].selected {
            background: #f39c12;
            border-color: #f39c12;
        }

        .fb-risk-btn[data-val="High"].selected {
            background: #e74c3c;
            border-color: #e74c3c;
        }

        /* Checkbox / radio */
        .fb-option-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .fb-option-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #555;
            cursor: pointer;
        }

        /* Signature */
        .fb-sig-wrap {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .fb-sig-canvas {
            display: block;
            cursor: crosshair;
            background: #fefefe;
        }

        .fb-sig-clear {
            display: block;
            padding: 6px 12px;
            font-size: 12px;
            color: #e74c3c;
            background: #fff;
            border: none;
            border-top: 1px solid #ddd;
            cursor: pointer;
            width: 100%;
        }

        /* Table */
        .fb-table-wrap {
            overflow-x: auto;
        }

        .fb-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .fb-table th {
            background: #f8f9fa;
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-weight: 600;
            color: #555;
            text-align: left;
        }

        .fb-table td {
            padding: 4px;
            border: 1px solid #ddd;
        }

        .fb-table input {
            width: 100%;
            border: none;
            padding: 6px 8px;
            font-size: 13px;
            background: transparent;
        }

        .fb-table input:focus {
            background: #f0f7ff;
            outline: none;
        }

        .btn-add-row {
            margin-top: 6px;
            font-size: 12px;
            color: #3498db;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 4px 0;
        }

        /* Info field */
        .fb-info-content {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
            white-space: pre-wrap;
            border-left: 3px solid #3498db;
        }

        /* Form actions */
        .fb-form-actions {
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .fb-form-actions .btn {
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .btn-save-form {
            background: #27ae60;
            color: #fff;
            border-color: #27ae60;
        }

        .btn-save-form:hover {
            background: #219a52;
        }

        .btn-print {
            background: #fff;
            color: #555;
        }

        /* Editor styles */
        .fb-editor-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
        }

        .fb-editor-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .fb-editor-section-title input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .fb-editor-field-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .fb-editor-field-row input {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .fb-editor-field-row select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            min-width: 110px;
        }

        .fb-editor-field-row .btn-rm {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
        }

        .btn-add-field,
        .btn-add-section {
            font-size: 13px;
            font-weight: 600;
            color: #3498db;
            background: none;
            border: 1px dashed #3498db;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn-add-section {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 15px;
        }

        .btn-save-template {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-save-template:hover {
            background: #2980b9;
            color: #fff;
        }

        .fb-options-input {
            margin-top: 6px;
        }

        .fb-options-input label {
            font-size: 12px;
            color: #777;
        }

        .fb-options-input input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 2px;
        }

        .fb-columns-input {
            margin-top: 6px;
        }

        .fb-columns-input label {
            font-size: 12px;
            color: #777;
        }

        .fb-columns-input input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 2px;
        }

        .fb-loading {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .fb-loading i {
            font-size: 30px;
            animation: spin 1s linear infinite;
            display: block;
            margin-bottom: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .fb-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99999;
            padding: 14px 24px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: none;
        }

        .fb-toast.success {
            background: #27ae60;
        }

        .fb-toast.error {
            background: #e74c3c;
        }
    </style>

    <div class="fb-container">
        <h2 class="fb-title">Form Builder</h2>
        <p class="fb-subtitle">Upload paper forms to create digital templates, fill them manually or with AI</p>

        <div class="fb-tabs">
            <div class="fb-tab active" data-tab="templates">Templates</div>
            <div class="fb-tab" data-tab="saved">Saved Forms</div>
            <div class="fb-tab" data-tab="create">Create New</div>
        </div>

        <!-- Tab 1: Templates -->
        <div class="fb-tab-content active" id="templatesTab">
            <div class="fb-upload-section">
                <div class="fb-upload-title">Upload & Generate</div>
                <div class="fb-upload-desc">Upload a paper form (PDF or Word) to create a digital template using AI</div>
                <div class="fb-dropzone" id="fbDropzone">
                    <i class="fa fa-cloud-upload"></i>
                    <p>Drag & drop PDF or Word documents here</p>
                    <p>or click to browse (multiple files supported)</p>
                    <p class="fb-file-types">PDF or Word (.docx) — Max 10MB each</p>
                </div>
                <input type="file" id="fbFileInput" accept=".pdf,.docx,.doc" multiple style="display:none;">
                <div class="fb-selected-files" id="fbSelectedFiles" style="display:none;"></div>
                <button class="btn-generate" id="btnGenerate" disabled>
                    <i class="fa fa-magic"></i> Generate Templates
                </button>
            </div>

            <div id="fbTemplateGrid" class="fb-grid">
                <!-- Populated by JS -->
            </div>
            <div id="fbTemplateEmpty" class="fb-empty" style="display:none;">
                <i class="fa fa-file-text-o"></i>
                <p>No templates yet. Upload a document to create your first template.</p>
            </div>
        </div>

        <!-- Tab 2: Saved Forms -->
        <div class="fb-tab-content" id="savedTab">
            <div class="fb-filter-bar">
                <select id="filterClient">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <select id="filterTemplate">
                    <option value="">All Templates</option>
                </select>
            </div>
            <div id="fbSubmissionsList"></div>
            <div id="fbSubmissionsEmpty" class="fb-empty" style="display:none;">
                <i class="fa fa-folder-open-o"></i>
                <p>No saved forms yet.</p>
            </div>
        </div>

        <!-- Tab 3: Create New -->
        <div class="fb-tab-content" id="createTab">
            <div style="max-width:700px;">
                <div class="fb-field" style="margin-bottom:12px;">
                    <label>Form Title</label>
                    <input type="text" id="editorTitle" placeholder="e.g. Risk Management Plan">
                </div>
                <div class="fb-field" style="margin-bottom:20px;">
                    <label>Description</label>
                    <input type="text" id="editorDesc" placeholder="Brief description of this form">
                </div>
                <div id="editorSections"></div>
                <button class="btn-add-section" id="btnAddSection"><i class="fa fa-plus"></i> Add Section</button>
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button class="btn-save-template" id="btnSaveManualTemplate"><i class="fa fa-save"></i> Save Template</button>
                    <button class="btn-save-template" id="btnPreviewTemplate" style="background:#f39c12;"><i class="fa fa-eye"></i> Preview</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Renderer Overlay -->
    <div class="fb-renderer-overlay" id="fbRendererOverlay">
        <div class="fb-renderer-panel" id="fbRendererPanel">
            <button class="fb-renderer-close" id="btnCloseRenderer">&times;</button>
            <div id="fbRendererContent"></div>
        </div>
    </div>

    <!-- Toast -->
    <div class="fb-toast" id="fbToast"></div>

    <script>
        var fbCsrfToken = '{{ csrf_token() }}';
        var fbBaseUrl = '{{ url("/roster/form-builder") }}';
        var fbClients = @json($clients);
        var fbTemplatesData = @json($templatesJson);
        var fbFillTemplateId = {{ $fillTemplateId ?? 'null' }};

        function esc(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': fbCsrfToken
            }
        });
    </script>
    <script src="{{ url('public/js/roster/form-builder.js') }}"></script>
    <script src="{{ url('public/js/roster/form-renderer.js') }}"></script>
    <script src="{{ url('public/js/roster/form-editor.js') }}"></script>

</main>
@endsection
