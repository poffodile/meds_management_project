@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Tasks</h3>
                </div>
            </div>
        </div>

        @include('frontEnd.salesAndFinance.lead.lead_buttons')
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!" id="exportCsv">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>
                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Lead Ref.</th>
                                <th>User</th>
                                <th>Task Type</th>
                                <th>Title</th>
                                <th>Contact Name</th>
                                <th>Contact Phone</th>
                                <th>Notes</th>
                                <th>Creeted By</th>
                                <th>Created On</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($lead_tasks as $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($value->create_date)->format('d/m/Y') }}  {{ \Carbon\Carbon::parse($value->create_time)->format('m:i') }}</td>
                                    <td>{{ $value->lead_ref }}</td>
                                    <td>{{ $value->name }}</td>
                                    <td>{{ $value->lead_task_type_title}}</td>
                                    <td>{{ $value->title }}</td>
                                    <td>{{ $value->contact_name }}</td>
                                    <td>{{ $value->telephone }}</td>
                                    <td>{{ $value->notes }}</td>
                                    <td>{{ $value->name }}</td>
                                    <td>{{  \Carbon\Carbon::parse($value->created_at)->format('d/m/Y h:i') }}</td>
                                    <td><a href="{{ url('/lead/lead_task_delete').'/'.$value->id }}" class="delete"><span style="color: #000;"><i data-toggle="modal" title="Close Task"  data-target="#secondModal" class="fa fa-times open-modal"></i></a> | <a href="{{ url('/leads/edit').'/'.$value->lead_id }}" ><i data-toggle="tooltip" title="View Lead" class="fa fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>
<script type="module">
    document.getElementById('exportCsv').addEventListener('click', function() {
        const table = document.getElementById('exampleOne'); // Get the table
        const selectedColumns = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]; // Specify which columns to export (e.g., 0 for Name, 2 for Phone)
        let csvContent = '';

        const now = new Date();
        const formattedDateTime = now.toISOString().replace(/[-T:]/g, '_').split('.')[0];
        const filename = `list_export_${formattedDateTime}.csv`;
        // Extract headers (only selected columns)
        const headers = Array.from(table.querySelectorAll('thead th'))
            .filter((_, index) => selectedColumns.includes(index)) // Filter headers by selected columns
            .map(th => th.textContent.trim())
            .join(',');
        csvContent += headers + '\n';

        // Extract rows (only selected columns)
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'))
                .filter((_, index) => selectedColumns.includes(index)) // Filter cells by selected columns
                .map(td => td.textContent.trim());
            csvContent += cells.join(',') + '\n';
        });

        // Create and download CSV file
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')