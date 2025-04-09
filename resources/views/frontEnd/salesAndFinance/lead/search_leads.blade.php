@include('frontEnd.salesAndFinance.jobs.layout.header')
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Search Leads</h3>
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
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!" onclick="hideShowDiv()" class="hidebtn">Show Search Filter</a>
                        </div>
                    </div>
                    <div class="searchJobForm" id="divTohide">
                        <form action="" class="p-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Lead Ref:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Lead Ref.">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Full Name:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Website:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="website">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Assigned User:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <input type="hidden" id="search_user" placeholder="Type user name">
                                                <div class="user-list"></div>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Company Name:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Type Company name">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Address:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Type your address">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Created From:</label>
                                        <div class="col-md-3 pe-0">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                        <div class="col-md-1">
                                            <span class="editInput">to</span>
                                        </div>
                                        <div class="col-md-3 ps-0">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Status:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                @foreach($statuses as $status)
                                                <option value="{{ $status->id}}">{{ $status->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Email Address:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Email address">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">City:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="City">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Source:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                @foreach($sources as $source)
                                                <option value="{{ $source->id }}">{{ $source->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Telephone:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Telephone">
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">County:</label>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="County">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Pincode">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label pe-0">Rejected Type:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Rejected Type">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Postcode:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Mobile">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="pageTitleBtn justify-content-center">
                                        <a href="#" class="profileDrop px-3">Search </a>
                                        <a href="#" class="profileDrop px-3">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="#" class="profileDrop">Delete</a>
                                    <a href="#" class="profileDrop">Assign To User</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="productDetailTable pt-3">
                        <table class="table mb-0" id="containerA">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name </th>
                                    <th>Company Name</th>
                                    <th>Email Address</th>
                                    <th>Telephone</th>
                                    <th>Mobile </th>
                                    <th>Website </th>
                                    <th>Address</th>
                                    <th>City </th>
                                    <th>County </th>
                                    <th>Postcode</th>
                                    <th>Lead Ref.</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>QU-0001</td>
                                    <td>2024-12-06</td>
                                    <td>Webnmob</td>
                                    <td>B-36 Sector 59</td>
                                    <td>1</td>
                                    <td>£220.00</td>
                                    <td>£44.00</td>
                                    <td>£264.00</td>
                                    <td>£0.00</td>
                                    <td>£264.00</td>
                                    <td>£120.00</td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">Action</a>
                                                <div class="dropdown-menu fade-up m-0" style="">
                                                    <a href="#" class="dropdown-item">Send SMS</a>
                                                    <a href="" class="dropdown-item">Preview</a>
                                                    <a href="" class="dropdown-item">Print</a>
                                                    <a href="" class="dropdown-item">Email</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End off main Table -->
            </div>
    </div>
    </div>
</section>

<script>
    $('#search_user').on('keyup', function() {
            let query = $(this).val();
            const divList = document.querySelector('.user-list');

            if (query === '') {
                divList.innerHTML = '';
            }

            // Make an AJAX call only if query length > 2
            if (query.length > 2) {
                $.ajax({
                    url: "{{ route('lead.ajax.searchUser') }}", 
                    method: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        console.log(response);
                        // $('#results').html(response);
                        divList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'container'; // Optional: Add a class to the div for styling

                        // Step 2: Create a ul (unordered list)
                        const ul = document.createElement('ul');
                        ul.id = "productList";
                        // Step 3: Loop through the data and create li (list item) for each entry
                        response.forEach(item => {
                            const li = document.createElement('li'); // Create a new li element
                            li.textContent = item.product_name; // Set the text of the li item
                            li.id = item.id;
                            li.className = "editInput";
                            ul.appendChild(li); // Append the li to the ul
                        });

                        // Step 4: Append the ul to the div
                        div.appendChild(ul);

                        // Step 5: Append the div to the parent container in the HTML
                        divList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            divList.innerHTML = '';
                            document.getElementById('search_user').value = '';
                            // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedId = event.target.id; // Get the ID of the clicked <li>
                                console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                                getProductData(selectedId);
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#results').empty(); // Clear results if the input is empty
            }
        });
</script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')