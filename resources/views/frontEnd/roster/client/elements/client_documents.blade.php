 <div class="emergencyMain">
     <div class="emergencyHeader">
         <div class="emeregencyParent">
             <div class="emergencyContent">
                 <div class="gap-3 d-flex align-items-center iconConsent">
                     <i class="far fa-file-alt" style="color:#2563eb"></i>
                     <h3>Document Management</h3>
                 </div>
                 <p class="mt-1"><small>Store and manage client-related documents</small></p>
             </div>
             <div class="emergencyBtn d-flex gap-3">
                 <div>

                     <button class="borderBtn">
                         <i class="bx  bx-sparkles me-3"></i>
                         <span> Generate Care Plan</span>
                     </button>
                 </div>
                 <div>
                     <button class="bgBtn" data-target-form="docForm1" onclick="toggleDocForm()">
                         <i class='bx  bx-arrow-from-bottom-stroke'></i>
                         <span> Upload Document</span>
                     </button>
                 </div>
             </div>
         </div>
     </div>
     <div style="margin: 24px;">
         <div class="purpleBox">
             <div class="d-flex gap-3">
                 <i class="bx  bx-sparkles"></i>
                 <div>
                     <p class="mb-2"> <strong>Assessment documents detected</strong></p>
                     <p class="mb-0">Click "Generate Care Plan" to automatically create care
                         plan, medications, and risk assessments from uploaded documents
                     </p>
                 </div>
             </div>

         </div>
         <div class="mainBlueCard">
             <div class="docForm " data-form-id="docForm1" style="display: none;">
                 <div class="emergencyHeader mt-4" style="border-bottom: unset;">
                     <div class="emeregencyParent">
                         <div class="emergencyContent">
                             <div class="gap-3 d-flex align-items-center iconConsent">
                                 <i style="color: #434447;" class="fa fa-upload" aria-hidden="true"></i>
                                 <h3>Upload New Document
                                 </h3>
                             </div>
                             <div class="emergencyForm">
                                 <form id="document-form-data">
                                     <input type="hidden" name="doc_manage_id" readonly>
                                     <div class="row mt-4">
                                         <div class="col-lg-6">
                                             <div>
                                                 <label class="form-label">Document Type *</label>
                                                 <select name="document_type" id="document_type" class="form-control">
                                                     <option value="Care Plan">Care Plan</option>
                                                     <option value="Assessment">Assessment</option>
                                                     <option value="Medical Report">Medical Report</option>
                                                     <option value="Consent Form">Consent Form</option>
                                                     <option value="Advance Directive">Advance Directive</option>
                                                     <option value="Risk Assessment">Risk Assessment</option>
                                                     <option value="Photo ID">Photo ID</option>
                                                     <option value="Insurance Document">Insurance Document</option>
                                                     <option value="Medication List">Medication List</option>
                                                     <option value="Other" selected>Other</option>
                                                 </select>
                                             </div>
                                         </div>

                                         <div class="col-lg-6">
                                             <label class="form-label">
                                                 Document Name *</label>
                                             <input class="form-control" type="text" name="doc_name"
                                                 placeholder="Enter document name">

                                         </div>

                                         <div class="col-lg-12">
                                             <label class="form-label">Select File *</label>
                                             <input class="form-control" type="file" name="doc_files"
                                                 accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                             <span class="text-muted fs-12">Accepted formats: PDF, DOC, DOCX, JPG,
                                                 PNG</span>
                                         </div>
                                         <div class="col-lg-6">
                                             <label class="form-label">Expiry Date (Optional)</label>
                                             <input class="form-control" type="date" min="{{ date('Y-m-d') }}"
                                                 name="doc_expiry_date">
                                         </div>
                                         <div class="col-lg-6">
                                             <label class="form-label">Access Level
                                             </label>
                                             <select name="doc_access_level_id" id="access_level_id"
                                                 class="form-control">
                                                 @foreach ($access_level as $item)
                                                     <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                 @endforeach
                                             </select>
                                         </div>
                                         <div class="col-lg-6">
                                             <label class="form-label">Tags (comma separated)</label>
                                             <input class="form-control" type="text" name="doc_tags"
                                                 placeholder="e.g., urgent, review, annual">
                                         </div>
                                         <div class="col-lg-6">
                                             <div class="formCheck d-flex gap-3 align-items-center">
                                                 <input type="checkbox" name="is_confidential" value="1">
                                                 <label class="form-label mb-0" style="display: inline-block;">Mark as
                                                     Confidential</label>
                                             </div>


                                         </div>
                                         <div class="col-lg-12">
                                             <label class="form-label">Notes</label>
                                             <textarea class="form-control" name="doc_notes" placeholder="Additional notes about this document "></textarea>
                                         </div>
                                         <div class="col-lg-12">
                                             <div class="formFooter">

                                                 <div class="d-flex gap-3">
                                                     <button type="button" class="redBtn" id="saveDocBtn">Save
                                                         Documents</button>
                                                     <button type="button" class="borderBtn cancelBtn"
                                                         onclick="closeDocForm()">Cancel</button>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </form>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <div id="aiPlan" class="loadDocManageWrapper">
             {{-- <div class="bBorderCard mt-4">
                 <div class="d-flex justify-content-between">
                     <div class="bCardHead">
                         <div>
                             <i class="far fa-file-alt"></i>
                         </div>
                         <div>
                             <h3>AI Care Plan - 19/12/2025</h3>
                         </div>
                         <div>
                             <span class="careBadg">Care Plan</span>
                         </div>
                     </div>
                     <div class="d-flex gap-3 careIconButton">
                         <div>
                             <button class="uploadBtn"> <i class="fa fa-download" aria-hidden="true"></i>
                             </button>
                         </div>
                         <div> <button class="deleteBtn"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                         </div>
                     </div>
                 </div>
                 <div class="docPlanD">
                     <div class="uploadBy">
                         <p class="mb-3"><strong>Uploaded :</strong> <span>Dec 16, 2025</span>
                         </p>
                         <p class="mb-3"><strong>By :</strong> <span>Unknown Staff</span></p>
                     </div>
                     <p class="mb-3"><strong>Size :</strong> <span>Dec 16, 2025</span></p>
                     <p class="mb-3"><strong>Uploaded :</strong> <span> 2.71 KB</span></p>
                 </div>
                 <div class=" userMum  d-flex gap-3 mb-3">
                     <span class="title">processed_for_care_plan </span>
                     <span class="title">processed_for_care_plan </span>
                 </div>
                 <p class="para text-sm">AI-generated person-centered care plan with actionable
                     tasks and objectives Used to generate care plan 6958abcf3cdd6f7f93bc71eb on
                     1/3/2026 Used to generate care plan 6958c1fa065bde2dc29dac0f on 1/3/2026</p>
             </div>

             <div class="bBorderCard mt-4">
                 <div class="d-flex justify-content-between">
                     <div class="bCardHead">
                         <div>
                             <i class="far fa-file-alt"></i>
                         </div>
                         <div>
                             <h3>AI Care Plan - 19/12/2025</h3>
                         </div>
                         <div>
                             <span class="careBadg">Care Plan</span>
                         </div>
                     </div>
                     <div class="d-flex gap-3 careIconButton">
                         <div>
                             <button class="uploadBtn"> <i class="fa fa-download" aria-hidden="true"></i>
                             </button>
                         </div>
                         <div> <button class="deleteBtn"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                         </div>
                     </div>
                 </div>
                 <div class="docPlanD">
                     <div class="uploadBy">
                         <p class="mb-3"><strong>Uploaded :</strong> <span>Dec 16, 2025</span>
                         </p>
                         <p class="mb-3"><strong>By :</strong> <span>Unknown Staff</span></p>
                     </div>
                     <p class="mb-3"><strong>Size :</strong> <span>Dec 16, 2025</span></p>
                     <p class="mb-3"><strong>Uploaded :</strong> <span> 2.71 KB</span></p>
                 </div>
                 <div class="userMum d-flex gap-3 mb-3">
                     <span class="title">processed_for_care_plan </span>
                     <span class="title">processed_for_care_plan </span>
                 </div>
                 <p class="para text-sm">AI-generated person-centered care plan with actionable
                     tasks and objectives Used to generate care plan 6958abcf3cdd6f7f93bc71eb on
                     1/3/2026 Used to generate care plan 6958c1fa065bde2dc29dac0f on 1/3/2026</p>
             </div> --}}
         </div>
     </div>
 </div>
