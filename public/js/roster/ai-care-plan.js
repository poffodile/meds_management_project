(function() {
    'use strict';

    var csrfToken = null;
    var isGenerating = false;
    var currentPlanData = null;
    var currentMeta = null;

    function esc(str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    function getCsrfToken() {
        if (csrfToken) return csrfToken;
        var meta = document.querySelector('meta[name="csrf-token"]');
        csrfToken = meta ? meta.getAttribute('content') : '';
        return csrfToken;
    }

    function getClientId() {
        var url = window.location.pathname;
        var parts = url.split('/');
        return parseInt(parts[parts.length - 1]) || 0;
    }

    function getBaseUrl() {
        var base = document.querySelector('meta[name="base-url"]');
        if (base) return base.getAttribute('content');
        return '';
    }

    window.loadCarePlans = function() {
        var clientId = getClientId();
        if (!clientId) return;

        var containers = [];
        var c1 = document.getElementById('carePlanListContainer');
        var c2 = document.getElementById('carePlanTabListContainer');
        if (c1) containers.push(c1);
        if (c2) containers.push(c2);
        if (!containers.length) return;

        for (var i = 0; i < containers.length; i++) {
            containers[i].innerHTML = '<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size:24px"></i> Loading care plans...</div>';
        }

        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/list',
            method: 'GET',
            data: { client_id: clientId },
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function(res) {
                for (var i = 0; i < containers.length; i++) {
                    if (res.status && res.plans) {
                        renderPlanList(res.plans, containers[i]);
                    } else {
                        containers[i].innerHTML = '<div class="text-center p-4 text-muted">No care plans found. Click "Generate Care Plan" to create one.</div>';
                    }
                }
            },
            error: function() {
                for (var i = 0; i < containers.length; i++) {
                    containers[i].innerHTML = '<div class="text-center p-4 text-danger">Failed to load care plans.</div>';
                }
            }
        });
    };

    function renderPlanList(plans, container) {
        if (!plans.length) {
            container.innerHTML = '<div class="text-center p-4 text-muted">No care plans yet. Click "Generate Care Plan" to create one.</div>';
            return;
        }

        var html = '';
        var activePlan = null;
        var otherPlans = [];

        for (var i = 0; i < plans.length; i++) {
            if (plans[i].plan_status === 'active') {
                activePlan = plans[i];
            } else {
                otherPlans.push(plans[i]);
            }
        }

        if (activePlan) {
            html += '<div class="activePlanCard">';
            html += '<div class="activePlanHeader">';
            html += '<div class="leftInfo">';
            html += '<span class="activeBadge">Active Plan</span>';
            html += '<span class="assessedDate">' + esc(activePlan.assessment_type) + ' • ' + esc(activePlan.care_setting) + ' care</span>';
            html += '</div>';
            html += '<button class="viewPlanBtn" onclick="viewCarePlan(' + activePlan.id + ')">View Full Plan <span>›</span></button>';
            html += '</div>';
            html += '<div class="activePlanStats">';
            html += renderStatItem('bx-radio-circle-marked', 'iconblue', 'Objectives', activePlan.objectives_count);
            html += renderStatItem('bx-checklist', 'iconpurple', 'Tasks', activePlan.tasks_count);
            html += renderStatItem('bx-pill', 'iconpink', 'Medications', activePlan.medications_count);
            html += renderStatItem('bx-alert-triangle', 'iconorange', 'Risk Factors', activePlan.risks_count);
            html += '</div>';
            html += '<div class="planMeta" style="margin-top:10px; padding-top:10px; border-top:1px solid #eee;">';
            html += '<div><strong>Created:</strong> ' + esc(activePlan.created_at) + ' by ' + esc(activePlan.created_by_name) + '</div>';
            if (activePlan.review_date) {
                html += '<div><strong>Next Review:</strong> ' + esc(activePlan.review_date) + '</div>';
            }
            html += '</div>';
            html += '</div>';
        }

        for (var j = 0; j < otherPlans.length; j++) {
            var p = otherPlans[j];
            html += '<div class="planCard">';
            html += '<div class="planTop">';
            html += '<div class="planTitle">';
            html += '<span class="heartIcon"><i class="bx bx-heart"></i></span> ';
            html += esc(p.assessment_type.charAt(0).toUpperCase() + p.assessment_type.slice(1)) + ' Care Plan';
            html += ' <span class="draftBadge">' + esc(p.plan_status) + '</span>';
            html += '</div>';
            html += '<div class="planActions">';
            html += '<button class="viewPlanBtn" onclick="viewCarePlan(' + p.id + ')"><i class="bx bx-eye"></i></button>';
            if (p.plan_status === 'draft') {
                html += '<button onclick="activateCarePlan(' + p.id + ')"><i class="bx bx-check-circle"></i></button>';
            }
            html += '<button class="danger" onclick="deleteCarePlan(' + p.id + ')"><i class="bx bx-trash"></i></button>';
            html += '</div>';
            html += '</div>';
            html += '<div class="planMeta">';
            html += '<div><strong>Setting:</strong> ' + esc(p.care_setting) + '</div>';
            html += '<div><strong>Created:</strong> ' + esc(p.created_at) + '</div>';
            html += '<div><strong>By:</strong> ' + esc(p.created_by_name) + '</div>';
            if (p.review_date) {
                html += '<div><strong>Review:</strong> ' + esc(p.review_date) + '</div>';
            }
            html += '</div>';
            html += '<div class="planFooter">';
            html += '<span><i class="bx bx-radio-circle-marked"></i> ' + p.objectives_count + ' objectives</span>';
            html += '<span><i class="bx bx-list"></i> ' + p.tasks_count + ' tasks</span>';
            html += '<span><i class="bx bx-pill"></i> ' + p.medications_count + ' medications</span>';
            html += '</div>';
            html += '</div>';
        }

        container.innerHTML = html;
    }

    function renderStatItem(icon, colorClass, label, value) {
        return '<div class="statItem">' +
            '<span class="statIcon ' + colorClass + '"><i class="bx ' + icon + '"></i></span>' +
            '<div><div class="statLabel">' + esc(label) + '</div>' +
            '<div class="statValue">' + esc(value) + '</div></div></div>';
    }

    window.openGenerateModal = function() {
        $('#generateCarePlanModal').modal('show');
    };

    window.generateCarePlan = function() {
        if (isGenerating) return;

        var clientId = getClientId();
        var assessmentType = document.getElementById('cpAssessmentType').value;
        var careSetting = document.getElementById('cpCareSetting').value;

        if (!assessmentType || !careSetting) {
            alert('Please select both assessment type and care setting.');
            return;
        }

        isGenerating = true;
        var btn = document.getElementById('generateCPBtn');
        var progress = document.getElementById('generateCPProgress');
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Generating...';
        progress.style.display = 'block';
        progress.innerHTML = '<i class="bx bx-search-alt"></i> Collecting assessment data...';

        setTimeout(function() {
            if (isGenerating) {
                progress.innerHTML = '<i class="bx bx-brain"></i> Generating care plan with AI... (this may take 15-20 seconds)';
            }
        }, 3000);

        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/generate',
            method: 'POST',
            data: {
                client_id: clientId,
                assessment_type: assessmentType,
                care_setting: careSetting
            },
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            timeout: 120000,
            success: function(res) {
                isGenerating = false;
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-sparkles"></i> Generate';
                progress.style.display = 'none';

                if (res.status && res.plan_data) {
                    currentPlanData = res.plan_data;
                    currentMeta = {
                        model: res.model,
                        tokens_input: res.tokens_input,
                        tokens_output: res.tokens_output,
                        tokens_used: res.tokens_used,
                        generation_time_ms: res.generation_time_ms,
                        assessment_snapshot: res.assessment_snapshot,
                        assessment_type: assessmentType,
                        care_setting: careSetting
                    };
                    $('#generateCarePlanModal').modal('hide');
                    setTimeout(function() {
                        showReviewModal(res.plan_data, res.tokens_used, res.generation_time_ms);
                    }, 400);
                } else {
                    alert(res.error || 'Failed to generate care plan.');
                }
            },
            error: function(xhr) {
                isGenerating = false;
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-sparkles"></i> Generate';
                progress.style.display = 'none';
                var msg = 'Failed to generate care plan.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                alert(msg);
            }
        });
    };

    function showReviewModal(planData, tokensUsed, timeMs) {
        var content = document.getElementById('reviewPlanContent');
        content.innerHTML = renderFullPlan(planData);

        var info = document.getElementById('reviewPlanInfo');
        info.innerHTML = '<span class="text-muted"><i class="bx bx-chip"></i> ' + tokensUsed + ' tokens used</span>';
        if (timeMs) {
            info.innerHTML += '<span class="text-muted" style="margin-left:15px"><i class="bx bx-time"></i> ' + (timeMs / 1000).toFixed(1) + 's</span>';
        }

        $('#reviewCarePlanModal').modal('show');
    }

    window.saveCarePlanAsDraft = function() {
        saveCarePlanWithStatus('draft');
    };

    window.saveCarePlanAsActive = function() {
        saveCarePlanWithStatus('active');
    };

    function saveCarePlanWithStatus(status) {
        if (!currentPlanData || !currentMeta) return;

        var clientId = getClientId();

        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/save',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                client_id: clientId,
                plan_data: currentPlanData,
                assessment_type: currentMeta.assessment_type,
                care_setting: currentMeta.care_setting,
                status: status,
                model: currentMeta.model,
                tokens_input: currentMeta.tokens_input,
                tokens_output: currentMeta.tokens_output,
                generation_time_ms: currentMeta.generation_time_ms,
                assessment_snapshot: currentMeta.assessment_snapshot
            }),
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function(res) {
                if (res.status) {
                    currentPlanData = null;
                    currentMeta = null;
                    closeCarePlanModal();
                } else {
                    alert(res.error || 'Failed to save care plan.');
                }
            },
            error: function(xhr) {
                var msg = 'Failed to save care plan.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                alert(msg);
            }
        });
    }

    window.viewCarePlan = function(planId) {
        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/view',
            method: 'GET',
            data: { plan_id: planId },
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function(res) {
                if (res.status && res.plan) {
                    showViewModal(res.plan);
                } else {
                    alert(res.error || 'Failed to load care plan.');
                }
            },
            error: function() {
                alert('Failed to load care plan.');
            }
        });
    };

    function showViewModal(plan) {
        var content = document.getElementById('viewPlanContent');
        var header = '<div class="assessmentDetails leave-card p-0">';
        header += '<header class="panel-heading headingCapitilize careTaskheader">';
        header += '<div class="clientHeadung">';
        header += '<div class="onlyheadingmain blueIconClr"><i class="bx bx-heart"></i> Care Plan</div>';
        header += '<p>' + esc(plan.assessment_type) + ' assessment • ' + esc(plan.care_setting) + ' care</p>';
        header += '</div>';
        header += '<div class="actions mt-0">';
        header += '<span class="roundBtntag ' + (plan.plan_status === 'active' ? 'greenShowbtn' : 'grayShowbtn') + '">' + esc(plan.plan_status) + '</span>';
        header += '</div>';
        header += '</header>';
        header += '<div class="assessmentDateAndVersion carePlanWrapper">';
        header += '<div class="activePlanStats">';
        header += '<div class="statItem"><div><div class="statLabel">Created</div><div class="statValue">' + esc(plan.created_at) + '</div></div></div>';
        header += '<div class="statItem"><div><div class="statLabel">Created By</div><div class="statValue">' + esc(plan.created_by_name) + '</div></div></div>';
        if (plan.review_date) {
            header += '<div class="statItem"><div><div class="statLabel">Next Review</div><div class="statValue">' + esc(plan.review_date) + '</div></div></div>';
        }
        header += '<div class="statItem"><div><div class="statLabel">AI Model</div><div class="statValue">' + esc(plan.ai_model) + '</div></div></div>';
        header += '</div></div></div>';

        content.innerHTML = header + renderFullPlan(plan.plan_data);

        var footer = document.getElementById('viewPlanFooter');
        footer.innerHTML = '';
        if (plan.plan_status === 'draft') {
            footer.innerHTML += '<button class="btn allBtnUseColor" onclick="activateCarePlan(' + plan.id + ')"><i class="bx bx-check-circle"></i> Activate Plan</button> ';
        }
        footer.innerHTML += '<button class="btn borderBtn" onclick="deleteCarePlan(' + plan.id + ')"><i class="bx bx-trash"></i> Delete</button> ';
        footer.innerHTML += '<button class="btn borderBtn" onclick="closeCarePlanModal()">Close</button>';

        $('#viewCarePlanModal').modal('show');
    }

    function renderFullPlan(planData) {
        var html = '<div class="careDetailsWrapper">';

        if (planData.summary) {
            html += '<div class="careSection" style="background:#f0f4ff; padding:15px; border-radius:8px; margin-bottom:15px;">';
            html += '<p style="margin:0; font-size:14px;">' + esc(planData.summary) + '</p>';
            html += '</div>';
        }

        if (planData.objectives && planData.objectives.length) {
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon blue">◎</span><h3>Care Objectives</h3></div>';
            for (var i = 0; i < planData.objectives.length; i++) {
                var obj = planData.objectives[i];
                var priorityClass = obj.priority === 'high' ? 'danger' : (obj.priority === 'medium' ? 'warning' : 'info');
                html += '<div class="objectiveCard">';
                html += '<div class="objectiveTop">';
                html += '<strong>' + esc(obj.title) + '</strong>';
                html += '<span class="statusBadge ' + priorityClass + '">' + esc(obj.priority || 'medium') + ' priority</span>';
                html += '</div>';
                html += '<p class="objectiveText">' + esc(obj.description) + '</p>';
                if (obj.success_measures) {
                    html += '<p class="metaLine"><strong>Success measures:</strong> ' + esc(obj.success_measures) + '</p>';
                }
                if (obj.target_date) {
                    html += '<p class="metaLine"><strong>Target:</strong> ' + esc(obj.target_date) + '</p>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        if (planData.care_tasks && planData.care_tasks.length) {
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon purple">≡</span><h3>Care Tasks & Interventions</h3></div>';
            for (var t = 0; t < planData.care_tasks.length; t++) {
                var task = planData.care_tasks[t];
                html += '<div class="taskCard">';
                html += '<div class="taskHeader">';
                html += '<span class="pill blue">' + esc(task.category ? task.category.replace(/_/g, ' ') : '') + '</span>';
                html += '<span class="taskTime">' + esc(task.frequency || '') + ' • ' + esc(task.duration_minutes || '') + ' mins</span>';
                html += '</div>';
                html += '<h4>' + esc(task.title) + '</h4>';
                if (task.description) {
                    html += '<p style="margin:5px 0; color:#555;">' + esc(task.description) + '</p>';
                }
                if (task.special_instructions) {
                    html += '<div class="instructionBox"><strong>Special Instructions:</strong> ' + esc(task.special_instructions) + '</div>';
                }
                if (task.assigned_role) {
                    html += '<p class="preferredTime">Assigned to: ' + esc(task.assigned_role.replace(/_/g, ' ')) + '</p>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        if (planData.risk_factors && planData.risk_factors.length) {
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon orange">⚠</span><h3>Risk Factors</h3></div>';
            for (var r = 0; r < planData.risk_factors.length; r++) {
                var risk = planData.risk_factors[r];
                var lClass = risk.likelihood === 'high' ? 'danger' : (risk.likelihood === 'medium' ? 'warning' : 'info');
                var iClass = risk.impact === 'high' ? 'danger' : (risk.impact === 'medium' ? 'warning' : 'info');
                html += '<div class="riskCard">';
                html += '<div class="riskTop">';
                html += '<strong>' + esc(risk.risk) + '</strong>';
                html += '<div class="riskBadges">';
                html += '<span class="riskBadge ' + lClass + '">Likelihood: ' + esc(risk.likelihood) + '</span>';
                html += '<span class="riskBadge ' + iClass + '">Impact: ' + esc(risk.impact) + '</span>';
                html += '</div></div>';
                if (risk.control_measures) {
                    html += '<div class="controlBox"><strong>Control Measures:</strong> ' + esc(risk.control_measures) + '</div>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        if (planData.medication_summary) {
            var med = planData.medication_summary;
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon pink"><i class="bx bx-pill"></i></span><h3>Medication Summary</h3></div>';
            html += '<div style="padding:10px 15px; background:#fdf2f8; border-radius:8px;">';
            html += '<p><strong>Total medications:</strong> ' + esc(med.total_medications) + '</p>';
            if (med.key_concerns) {
                html += '<p><strong>Key concerns:</strong> ' + esc(med.key_concerns) + '</p>';
            }
            if (med.notes) {
                html += '<p><strong>Notes:</strong> ' + esc(med.notes) + '</p>';
            }
            html += '</div></div>';
        }

        if (planData.review_schedule) {
            var rev = planData.review_schedule;
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon blue"><i class="bx bx-calendar"></i></span><h3>Review Schedule</h3></div>';
            html += '<div style="padding:10px 15px; background:#f0f9ff; border-radius:8px;">';
            if (rev.next_review_date) {
                html += '<p><strong>Next review:</strong> ' + esc(rev.next_review_date) + '</p>';
            }
            if (rev.review_frequency) {
                html += '<p><strong>Frequency:</strong> Every ' + esc(rev.review_frequency.replace(/_/g, ' ')) + '</p>';
            }
            if (rev.review_triggers && rev.review_triggers.length) {
                html += '<p><strong>Triggers for earlier review:</strong></p><ul>';
                for (var rt = 0; rt < rev.review_triggers.length; rt++) {
                    html += '<li>' + esc(rev.review_triggers[rt]) + '</li>';
                }
                html += '</ul>';
            }
            html += '</div></div>';
        }

        if (planData.consent_and_capacity) {
            var cc = planData.consent_and_capacity;
            html += '<div class="careSection">';
            html += '<div class="sectionHeader"><span class="icon blue"><i class="bx bx-shield"></i></span><h3>Consent & Capacity</h3></div>';
            html += '<div style="padding:10px 15px; background:#f0fdf4; border-radius:8px;">';
            if (cc.capacity_assessment) {
                html += '<p><strong>Capacity:</strong> ' + esc(cc.capacity_assessment) + '</p>';
            }
            html += '<p><strong>Consent given:</strong> ' + (cc.consent_given ? 'Yes' : 'No') + '</p>';
            if (cc.involvement_notes) {
                html += '<p><strong>Client involvement:</strong> ' + esc(cc.involvement_notes) + '</p>';
            }
            html += '</div></div>';
        }

        html += '</div>';
        return html;
    }

    window.deleteCarePlan = function(planId) {
        if (!confirm('Are you sure you want to delete this care plan?')) return;

        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/delete',
            method: 'POST',
            data: { plan_id: planId },
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function(res) {
                if (res.status) {
                    window.loadCarePlans();
                } else {
                    alert(res.error || 'Failed to delete care plan.');
                }
            },
            error: function() {
                alert('Failed to delete care plan.');
            }
        });
    };

    window.activateCarePlan = function(planId) {
        if (!confirm('Activate this care plan? Any existing active plan will be superseded.')) return;

        $.ajax({
            url: getBaseUrl() + '/roster/ai-care-plan/activate',
            method: 'POST',
            data: { plan_id: planId },
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function(res) {
                if (res.status) {
                    window.loadCarePlans();
                } else {
                    alert(res.error || 'Failed to activate care plan.');
                }
            },
            error: function() {
                alert('Failed to activate care plan.');
            }
        });
    };

    window.closeCarePlanModal = function() {
        window.location.href = window.location.pathname + '?tab=care-plan';
    };

    if (window.location.search.indexOf('tab=care-plan') !== -1) {
        history.replaceState(null, '', window.location.pathname);
        $(function() {
            var cpTabBtn = document.querySelector('.tab[data-tab="clientCarePlanTab"]');
            if (cpTabBtn) cpTabBtn.click();
        });
    }

})();
