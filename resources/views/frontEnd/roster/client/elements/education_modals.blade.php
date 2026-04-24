<style>
    .status-btn-group { display: flex; gap: 10px; margin-top: 10px; }
    .status-btn { flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-weight: 600; cursor: pointer; transition: all 0.2s; text-align: center; }
    .status-btn:hover { background: #f8fafc; }
    .status-btn.active[data-status="present"], .status-btn.active[data-type="Engagement"] { background: #dcfce7; color: #15803d; border-color: #86efac; }
    .status-btn.active[data-status="absent"], .status-btn.active[data-type="Concern"] { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
    .status-btn.active[data-status="late"], .status-btn.active[data-type="Progress"] { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
    
    .status-btn.active[data-type] { background: #f5f3ff; color: #7c3aed; border-color: #8b5cf6; }

    .modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 15px 20px; background: #fff; border-radius: 15px 15px 0 0; }
    .modal-title { font-weight: 600; color: #334155; font-size: 18px; }
    .modal-body { padding: 20px; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 15px 20px; display: flex; justify-content: flex-end; }
    
    .form-label { font-weight: 500; color: #1e293b; margin-bottom: 8px; font-size: 14px; }
    .form-control { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 12px; font-size: 14px; color: #475569; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    
    /* Button color updated to Teal/Blue as per image */
    .btn-assign { background: #38b2ac; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.2s; }
    .btn-assign:hover { background: #319795; transform: translateY(-1px); }

    .attachment-box { border: 2px dashed #e2e8f0; border-radius: 10px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.2s; }
    .attachment-box:hover { border-color: #38b2ac; background: #f0fff4; }
    .attachment-box i { font-size: 20px; color: #94a3b8; margin-bottom: 4px; }
    .attachment-box p { margin: 0; color: #64748b; font-size: 12px; }
</style>

<!-- Add Education Profile Modal -->
<div class="modal fade" id="addEducationProfileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Education Profile</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('education.add-profile') }}" method="post">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school_name" class="form-control" placeholder="e.g. Greenfield Academy" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Grade / Class</label>
                        <input type="text" name="grade" class="form-control" placeholder="e.g. 5" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Subjects (Comma separated)</label>
                        <input type="text" name="subjects" class="form-control" placeholder="Math, Science, English">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" value="2025-2026" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-assign w-100" type="submit">Create Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addEducationTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 15px 25px;">
                <h4 class="modal-title" style="font-weight: 500; color: #334155; font-size: 18px;">Assign New Task</h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 24px; color: #cbd5e1; opacity: 0.8;">&times;</button>
            </div>
            <form action="{{ route('education.add-task') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <input type="hidden" name="education_profile_id" value="{{ $education_profile->id ?? '' }}">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Subject</label>
                        <select name="subject" class="form-control" required style="border: 1.5px solid #8b5cf6; border-radius: 8px; padding: 12px; height: auto; appearance: auto; color: #64748b;">
                            <option value="">Select subject</option>
                            @if(isset($education_profile->subjects))
                                @foreach(explode(',', $education_profile->subjects) as $subj)
                                    <option value="{{ trim($subj) }}">{{ trim($subj) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Task Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Write description..." required style="border: 1.5px solid #8b5cf6; border-radius: 8px; padding: 12px;"></textarea>
                        <input type="hidden" name="title" value="Task">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Due Date</label>
                        <div style="position: relative;">
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; height: auto; width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 15px 25px; border-radius: 0 0 12px 12px;">
                    <button class="btn-assign" type="submit" style="background: #4db6ac; color: #fff; border: none; padding: 10px 30px; border-radius: 10px; font-weight: 700; font-size: 15px;">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Attendance Modal -->
<div class="modal fade" id="logAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Mark Attendance</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('education.add-attendance') }}" method="post">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <input type="hidden" name="education_profile_id" value="{{ $education_profile->id ?? '' }}">
                <input type="hidden" name="status" id="attendanceStatus" value="present">
                
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="form-label">Status</label>
                        <div class="status-btn-group">
                            <div class="status-btn active" data-status="present">Present</div>
                            <div class="status-btn" data-status="absent">Absent</div>
                            <div class="status-btn" data-status="late">Late</div>
                        </div>
                    </div>
                    
                    <div id="attendanceReasonBox" class="form-group d-none">
                        <label class="form-label">Reason</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-assign" type="submit">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Staff Modal -->
<div class="modal fade" id="assignEducationStaffModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 15px 25px;">
                <h4 class="modal-title" style="font-weight: 500; color: #334155; font-size: 18px;">Assign Staff Member</h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 24px; color: #cbd5e1; opacity: 0.8;">&times;</button>
            </div>
            <form action="{{ route('education.assign-staff') }}" method="post">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group mb-0">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Select Teacher / Caregiver</label>
                        <select name="staff_id" class="form-control" required style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; height: auto; appearance: auto;">
                            <option value="">Choose staff member...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 15px 25px; border-radius: 0 0 12px 12px;">
                    <button class="btn-assign" type="submit" style="background: #4db6ac; color: #fff; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 700; font-size: 15px;">Assign to Child</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addEducationNoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Note</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('education.add-note') }}" method="post">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <input type="hidden" name="education_profile_id" value="{{ $education_profile->id ?? '' }}">
                <input type="hidden" name="type" id="noteType" value="Engagement">
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Note Type</label>
                        <div class="status-btn-group">
                            <div class="status-btn active" data-type="Engagement">Engagement</div>
                            <div class="status-btn" data-type="Progress">Progress</div>
                            <div class="status-btn" data-type="Concern">Concern</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Write your note..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-assign" type="submit">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addEducationResourceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 15px 25px;">
                <h4 class="modal-title" style="font-weight: 500; color: #334155; font-size: 18px;">Upload Learning Resource</h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 24px; color: #cbd5e1; opacity: 0.8;">&times;</button>
            </div>
            <form action="{{ route('education.add-resource') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                <input type="hidden" name="education_profile_id" value="{{ $education_profile->id ?? '' }}">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Testing Data" required style="border: 1.5px solid #8b5cf6; border-radius: 8px; padding: 12px; height: auto;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Subject (optional)</label>
                        <select name="subject" class="form-control" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; height: auto; appearance: auto; color: #64748b;">
                            <option value="">Select subject</option>
                            @if(isset($education_profile->subjects))
                                @foreach(explode(',', $education_profile->subjects) as $subj)
                                    <option value="{{ trim($subj) }}">{{ trim($subj) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;">Upload File</label>
                        <div class="attachment-box" onclick="$('#resourceFile').click()" style="border: 2px dashed #e2e8f0; border-radius: 10px; padding: 25px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <div id="resourceFileUI">
                                <i class='bx bx-upload' style="font-size: 24px; color: #94a3b8; margin-bottom: 8px; display: block;"></i>
                                <p id="resourceFileName" style="margin: 0; color: #64748b; font-size: 14px;">Click to upload</p>
                            </div>
                            <input type="file" name="file" id="resourceFile" style="display: none !important;" onchange="updateFileName(this, 'resourceFileName')">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" style="font-weight: 500; color: #1e293b; margin-bottom: 12px; display: block;"><i class='bx bx-link'></i> Or add a link</label>
                        <input type="url" name="link" class="form-control" placeholder="https://..." style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; height: auto;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 15px 25px; border-radius: 0 0 12px 12px;">
                    <button class="btn-assign" type="submit" style="background: #4db6ac; color: #fff; border: none; padding: 10px 30px; border-radius: 10px; font-weight: 700; font-size: 15px;">Save Resource</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Attendance status switching
        $('#logAttendanceModal .status-btn').on('click', function() {
            $('#logAttendanceModal .status-btn').removeClass('active');
            $(this).addClass('active');
            const status = $(this).data('status');
            $('#attendanceStatus').val(status);
            
            if(status === 'absent' || status === 'late') {
                $('#attendanceReasonBox').removeClass('d-none');
            } else {
                $('#attendanceReasonBox').addClass('d-none');
            }
        });

        // Note type switching
        $('#addEducationNoteModal .status-btn').on('click', function() {
            $('#addEducationNoteModal .status-btn').removeClass('active');
            $(this).addClass('active');
            $('#noteType').val($(this).data('type'));
        });
    });

    function updateFileName(input, targetId) {
        if (input.files && input.files[0]) {
            $('#' + targetId).text(input.files[0].name).css('color', '#1e293b');
        }
    }
</script>
