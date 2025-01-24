<div class="row">
    <div class="col-md-12 col-lg-12 col-xl-12 px-3">
        <div class="jobsection">
            <a href="{{ url('/leads/add') }}" class="profileDrop" >New Lead</a>
            <a href="{{ url('/leads/leads') }}" class="profileDrop" >All Leads <span>({{ $allLead }})</span></a>
            <a href="{{ url('/lead/myLeads') }}" class="profileDrop">My Leads<span>({{ $myLeads }})</span></a>
            <a href="{{ url('/leads/unassigned') }}" class="profileDrop">Unassigned<span>({{ $unAssignLead }})</span></a>
            <a href="{{ url('/lead/actioned') }}" class="profileDrop">Actioned<span>({{ $actionedLead  }})</span></a>
            <a href="{{ url('/lead/rejected') }}" class="profileDrop">Rejected<span>({{ $rejectLead }})</span></a>
            <a href="{{ url('/lead/authorization') }}" class="profileDrop">Authorization<span>({{ $authorizedLead }})</span></a>
            <a href="{{ url('/leads/converted') }}" class="profileDrop">Converted <span>({{ $convertedLead }})</span></a>
            <a href="{{ url('/leads/search') }}" class="profileDrop">Search Leads</a>
            <a href="{{ url('/leads/tasks') }}" class="profileDrop">Task</a>
        </div>
    </div>
</div>