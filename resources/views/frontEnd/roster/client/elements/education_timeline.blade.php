<style>
    .edu-timeline-wrapper {
        background: #fff;
        border-radius: 16px;
        padding: 32px;
        border: 1px solid #f1f5f9;
    }
    .edu-timeline {
        position: relative;
        padding-left: 50px; /* Space for the line and dots */
    }
    .edu-timeline::before {
        content: '';
        position: absolute;
        left: 20px; /* Center of the dots */
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f1f5f9;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 32px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 44px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -50px; /* Align with padding of .edu-timeline */
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        z-index: 2;
        border: 4px solid #fff;
        font-size: 18px;
    }
    
    .timeline-body {
        display: flex;
        flex-direction: column;
    }
    .timeline-title {
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.4;
    }
    .timeline-meta {
        font-size: 13px;
        color: #94a3b8;
    }

    /* Dot Colors */
    .dot-profile { color: #0ea5e9; background: #f0f9ff; box-shadow: 0 0 0 2px #e0f2fe; }
    .dot-task { color: #6366f1; background: #eef2ff; box-shadow: 0 0 0 2px #e0e7ff; }
    .dot-attendance { color: #f59e0b; background: #fffbeb; box-shadow: 0 0 0 2px #fef3c7; }
    .dot-note { color: #64748b; background: #f8fafc; box-shadow: 0 0 0 2px #f1f5f9; }
    .dot-resource { color: #ec4899; background: #fdf2f8; box-shadow: 0 0 0 2px #fce7f3; }
</style>

<div class="edu-timeline-wrapper">
    <div class="edu-timeline">
        @forelse($timeline as $item)
            <div class="timeline-item">
                <div class="timeline-dot 
                    @if($item['type'] == 'profile') dot-profile 
                    @elseif($item['type'] == 'task' || $item['type'] == 'task_completion') dot-task 
                    @elseif($item['type'] == 'attendance') dot-attendance 
                    @elseif($item['type'] == 'note') dot-note 
                    @else dot-resource @endif">
                    
                    @if($item['type'] == 'profile') <i class='bx bx-book-open'></i>
                    @elseif($item['type'] == 'task' || $item['type'] == 'task_completion') <i class='bx bx-clipboard'></i>
                    @elseif($item['type'] == 'attendance') <i class='bx bx-calendar-check'></i>
                    @elseif($item['type'] == 'note') <i class='bx bx-note'></i>
                    @else <i class='bx bx-file'></i> @endif
                </div>
                
                <div class="timeline-body">
                    <div class="timeline-title">
                        @if($item['type'] == 'profile')
                            Education profile created for {{ $item['data']->serviceUser->name ?? 'Service User' }}
                        @elseif($item['type'] == 'task')
                            Task "{{ $item['data']->title ?? $item['data']->subject }}" assigned in {{ $item['data']->subject }}
                        @elseif($item['type'] == 'task_completion')
                            Task "{{ $item['data']->title ?? $item['data']->subject }}" completed by the child
                            @if($item['data']->rating)
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span class="text-warning small">
                                        @for($i=1; $i<=5; $i++)
                                            <i class='bx {{ $i <= $item['data']->rating ? "bxs-star" : "bx-star" }}'></i>
                                        @endfor
                                    </span>
                                    <span class="small font-weight-bold">({{ $item['data']->rating }}/5)</span>
                                </div>
                            @else
                                <div class="mt-2">
                                    <button class="btn btn-xs btn-outline-primary rate-task-btn" data-id="{{ $item['data']->id }}" data-toggle="modal" data-target="#rateTaskModal" style="padding: 2px 8px; font-size: 11px;">Rate Task</button>
                                </div>
                            @endif
                        @elseif($item['type'] == 'attendance')
                            Attendance marked as {{ $item['data']->status }} for {{ date('Y-m-d', strtotime($item['data']->date)) }}
                        @elseif($item['type'] == 'note')
                            {{ ucfirst($item['data']->type ?? 'General') }} Note: {{ $item['data']->notes }}
                        @elseif($item['type'] == 'resource')
                            New Resource "{{ $item['data']->title }}" uploaded
                        @endif
                    </div>
                    <div class="timeline-meta">
                        @if(isset($item['data']->staff))
                            {{ $item['data']->staff->name }}
                        @elseif($item['type'] == 'profile')
                            Admin
                        @else
                            System
                        @endif
                        • {{ date('M d, g:i A', strtotime($item['date'])) }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <i class='bx bx-history' style="font-size: 50px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                <p class="text-muted" style="font-size: 14px;">No educational activity found.</p>
            </div>
        @endforelse
    </div>
</div>
