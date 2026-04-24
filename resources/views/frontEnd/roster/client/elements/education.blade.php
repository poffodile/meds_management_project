<style>
    .edu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
    .edu-name h2 { margin: 0; font-size: 28px; font-weight: 700; color: #1e293b; }
    .edu-name p { margin: 5px 0 0; color: #64748b; font-size: 16px; }
    
    .edu-stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; margin-bottom: 30px; }
    .edu-stat-item label { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 600; margin-bottom: 8px; }
    .edu-stat-item .value { font-size: 15px; font-weight: 700; color: #1e293b; }
    .edu-stat-item .subject-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .subject-pill { background: #eff6ff; color: #3b82f6; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid #dbeafe; }

    .edu-sub-tabs { display: flex; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    .edu-sub-tab { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #64748b; cursor: pointer; transition: all 0.2s; }
    .edu-sub-tab:hover { background: #f1f5f9; color: #1e293b; }
    .edu-sub-tab.active { background: #fff; color: #4f46e5; border-color: #4f46e5; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1); }
    .edu-sub-tab span { color: #94a3b8; margin-left: 4px; font-weight: 400; }

    .edu-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 15px; transition: transform 0.2s, box-shadow 0.2s; }
    .edu-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .edu-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .edu-card-title { font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; }
    .edu-card-meta { font-size: 13px; color: #64748b; margin-bottom: 10px; }
    .edu-card-desc { font-size: 14px; color: #475569; line-height: 1.5; }
    
    .status-badge { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-progress { background: #eff6ff; color: #1d4ed8; }
    .status-completed { background: #f0fdf4; color: #15803d; }

    .btn-actions { background: #4f46e5; color: #fff; border: none; padding: 10px 24px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; transition: all 0.2s; }
    .btn-actions:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
    .btn-assign { background: #fff; color: #475569; border: 1px solid #e2e8f0; padding: 10px 24px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; transition: all 0.2s; }
    .btn-assign:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); }
    
    .dropdown-menu.edu-actions-dropdown { border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border-radius: 12px; padding: 8px; min-width: 200px; }
    .edu-actions-dropdown .dropdown-item { border-radius: 8px; padding: 10px 12px; font-size: 14px; color: #475569; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    .edu-actions-dropdown .dropdown-item:hover { background: #f1f5f9; color: #1e293b; }
    .edu-actions-dropdown .dropdown-item i { font-size: 18px; color: #94a3b8; }
</style>

<div class="p-4" style="background: #f8fafc; border-radius: 16px;">
    <!-- HEADER SECTION -->
    <div class="edu-header">
        <div class="edu-name">
            <h2>{{ $clientDetails['name'] }}</h2>
            <p>{{ $education_profile->school_name ?? 'No School Assigned' }} • Grade {{ $education_profile->grade ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-3">
            <div class="dropdown">
                <button class="btn-actions dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class='bx bx-plus'></i> Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right edu-actions-dropdown">
                    @if(!$education_profile)
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addEducationProfileModal"><i class='bx bx-id-card'></i> Create Profile</a>
                    @else
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addEducationTaskModal"><i class='bx bx-task'></i> Add Task/Homework</a>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logAttendanceModal"><i class='bx bx-calendar-check'></i> Log Attendance</a>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addEducationNoteModal"><i class='bx bx-note'></i> Add Note</a>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addEducationResourceModal"><i class='bx bx-file'></i> Upload Resource</a>
                    @endif
                </div>
            </div>
            <button class="btn-assign" data-toggle="modal" data-target="#assignEducationStaffModal">
                <i class='bx bx-user-plus'></i> Assign Staff
            </button>
        </div>
    </div>

    <!-- STATS ROW -->
    <div class="edu-stats-row">
        <div class="edu-stat-item">
            <label>School</label>
            <div class="value">{{ $education_profile->school_name ?? 'Not Set' }}</div>
        </div>
        <div class="edu-stat-item">
            <label>Grade</label>
            <div class="value">Grade {{ $education_profile->grade ?? 'N/A' }}</div>
        </div>
        <div class="edu-stat-item">
            <label>Academic Year</label>
            <div class="value">{{ $education_profile->academic_year ?? 'Not Set' }}</div>
        </div>
        <div class="edu-stat-item">
            <label>Subjects</label>
            <div class="subject-pills">
                @if(isset($education_profile->subjects))
                    @foreach(explode(',', $education_profile->subjects) as $subj)
                        <span class="subject-pill">{{ trim($subj) }}</span>
                    @endforeach
                @else
                    <span class="text-muted" style="font-size: 13px;">None</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- ASSIGNED STAFF SECTION -->
    <div class="edu-card" style="margin-bottom: 25px;">
        <div style="font-weight: 600; color: #334155; font-size: 14px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-group' style="font-size: 18px; color: #64748b;"></i>
            Assigned Staff
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            @forelse($assigned_staff as $assign)
                <div style="background: #f1f5f9; border-radius: 30px; padding: 6px 15px; display: flex; align-items: center; gap: 10px; border: 1px solid #e2e8f0;">
                    <div style="width: 24px; height: 24px; background: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #64748b;">
                        {{ substr($assign->staff->name ?? '?', 0, 1) }}
                    </div>
                    <span style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $assign->staff->name ?? 'Unknown' }}</span>
                    <a href="#" style="color: #94a3b8; font-size: 14px;"><i class='bx bx-trash'></i></a>
                </div>
            @empty
                <div style="color: #94a3b8; font-size: 13px; font-style: italic;">No staff assigned yet.</div>
            @endforelse
        </div>
    </div>

    <!-- SUB TABS NAVIGATION -->
    <div class="edu-sub-tabs">
        <div class="edu-sub-tab active" data-target="edu-tasks-pane">Tasks <span>({{ $education_tasks->count() }})</span></div>
        <div class="edu-sub-tab" data-target="edu-attendance-pane">Attendance <span>({{ $education_attendance->count() }})</span></div>
        <div class="edu-sub-tab" data-target="edu-notes-pane">Notes <span>({{ $education_notes->count() }})</span></div>
        <div class="edu-sub-tab" data-target="edu-resources-pane">Resources <span>({{ $education_resources->count() }})</span></div>
        <div class="edu-sub-tab" data-target="edu-timeline-pane">Timeline</div>
    </div>

    <!-- SUB TABS CONTENT -->
    <div class="edu-panes">
        <!-- TASKS PANE -->
        <div class="edu-pane" id="edu-tasks-pane">
            @forelse($education_tasks as $task)
                <div class="edu-card">
                    <div class="edu-card-header">
                        <div class="edu-card-title">
                            {{ $task->title ?? $task->subject }}
                            <span class="status-badge status-{{ $task->status == 'completed' ? 'completed' : ($task->status == 'in_progress' ? 'progress' : 'pending') }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="edu-card-meta">
                        {{ $task->subject }} • Due {{ date('M d, Y', strtotime($task->due_date)) }} • by {{ $task->staff->name ?? 'System' }}
                    </div>
                    <div class="edu-card-desc">
                        {{ $task->description }}
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class='bx bx-task' style="font-size: 64px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <p class="mt-2 text-muted" style="font-size: 14px;">No educational tasks assigned yet.</p>
                </div>
            @endforelse
        </div>

        <!-- ATTENDANCE PANE -->
        <div class="edu-pane d-none" id="edu-attendance-pane">
            @forelse($education_attendance as $att)
                <div class="edu-card py-3 px-4 d-flex justify-content-between align-items-center">
                    <div style="font-weight: 500; color: #475569;">
                        {{ date('D, M d', strtotime($att->date)) }}
                    </div>
                    <div>
                        @if($att->status == 'present')
                            <span class="status-badge status-completed" style="background: #dcfce7; color: #15803d;">Present</span>
                        @elseif($att->status == 'absent')
                            <span class="status-badge status-pending" style="background: #fee2e2; color: #b91c1c;">Absent</span>
                        @else
                            <span class="status-badge status-progress" style="background: #fef3c7; color: #b45309;">Late</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class='bx bx-calendar-check' style="font-size: 64px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <p class="text-muted" style="font-size: 14px;">No attendance records found.</p>
                </div>
            @endforelse
        </div>

        <!-- NOTES PANE -->
        <div class="edu-pane d-none" id="edu-notes-pane">
            @forelse($education_notes as $note)
                <div class="edu-card">
                    <div class="edu-card-header">
                        <div class="edu-card-title"><i class='bx bx-note text-muted'></i> Progress Note</div>
                        <span class="text-muted small">{{ $note->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="edu-card-desc">{{ $note->notes }}</div>
                    <div class="mt-2 text-muted small">Logged by: {{ $note->staff->name ?? 'System' }}</div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class='bx bx-note' style="font-size: 64px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <p class="text-muted" style="font-size: 14px;">No notes found.</p>
                </div>
            @endforelse
        </div>

        <!-- RESOURCES PANE -->
        <div class="edu-pane d-none" id="edu-resources-pane">
            @forelse($education_resources as $res)
                <div class="edu-card d-flex justify-content-between align-items-center" style="border: 1.5px solid #8b5cf6;">
                    <div>
                        <div style="font-weight: 700; color: #1e293b; font-size: 16px;">{{ $res->title }}</div>
                        <div style="color: #64748b; font-size: 13px; margin-top: 4px;">{{ $res->subject ?? 'General' }}</div>
                    </div>
                    <div>
                        @if($res->file_path)
                            <a href="{{ asset($res->file_path) }}" target="_blank" style="color: #4f46e5; font-weight: 500; font-size: 14px; text-decoration: none;">Download</a>
                        @elseif($res->link)
                            <a href="{{ $res->link }}" target="_blank" style="color: #4f46e5; font-weight: 500; font-size: 14px; text-decoration: none;">View Link</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class='bx bx-file' style="font-size: 64px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <p class="text-muted" style="font-size: 14px;">No resources available.</p>
                </div>
            @endforelse
        </div>

        <!-- TIMELINE PANE -->
        <div class="edu-pane d-none" id="edu-timeline-pane">
            <div id="roster_education_timeline_modern">
                <p class="text-center py-5"><i class="bx bx-loader-alt bx-spin"></i> Loading timeline...</p>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
@include('frontEnd.roster.client.elements.education_modals')

<script>
    $(document).ready(function() {
        // Sub-tabs switching logic
        $('.edu-sub-tab').on('click', function() {
            $('.edu-sub-tab').removeClass('active');
            $(this).addClass('active');
            
            const target = $(this).data('target');
            $('.edu-pane').addClass('d-none');
            $('#' + target).removeClass('d-none');
            
            if(target === 'edu-timeline-pane') {
                loadModernTimeline();
            }
        });

        function loadModernTimeline() {
            $.ajax({
                url: "{{ route('education.timeline', $service_user_id) }}",
                success: function(response) {
                    $('#roster_education_timeline_modern').html(response);
                }
            });
        }
    });
</script>
