//   Data Table Js
// $(document).ready(function() {
//   $('#exampleOne').DataTable( {
//       columnDefs: [ {
//           orderable: false,
//           className: 'select-checkbox',
//           targets:   0
//       } ],
//       select: {
//           style:    'os',
//           selector: 'td:first-child'
//       },
//       order: [[ 1, 'asc' ]]
//   } );
// } );

// $(document).ready(function() {
//   $('#exampleOne').DataTable({
//       columnDefs: [{
//           orderable: false,
//           className: 'select-checkbox',
//           targets: 0
//       }],
//       select: {
//           style: 'os',
//           selector: 'td:first-child'
//       },
//       order: [[1, 'asc']],
//       language: {
//           paginate: {
//               previous: "Previous", // Change this text
//               next: "Next"          // Change this text
//           },
//           info: "Showing _START_ to _END_ of _TOTAL_ entries", // This text can be adjusted as needed
//           infoEmpty: "No entries available",
//           infoFiltered: "(filtered from _MAX_ total entries)",
//           lengthMenu: "Show _MENU_ entries",
//           search: "Search:",
//           zeroRecords: "No matching records found"
//       },
//       paging: true, 
//   });
// });
//*******************************DataTable */
$(document).ready(function () {
  var table = $('#exampleOne').DataTable({
    // Ram 09/10/2024 comment here for manual checkbox 
    // columnDefs: [{
    //     orderable: false,
    //     className: 'select-checkbox',
    //     targets: 0
    // }],
    // select: {
    //     style: 'multi',
    //     selector: 'td:first-child'
    // },
    // end here
    order: [[1, 'asc']],
    language: {
      paginate: {
        previous: "Previous",
        next: "Next"
      },
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "No entries available",
      emptyTable: '<span style="color: #e10078; font-weight: bold;">Sorry, there are no items available</span>',
      infoFiltered: "(filtered from _MAX_ total entries)",
      lengthMenu: "Show _MENU_ entries",
      search: "Search:",
      zeroRecords: "No matching records found"
    },
    paging: true,
  });
  // Ram change
  // Select All checkbox functionality
  // $('#selectAll').on('click', function() {
  //     if ($(this).prop('checked')) {
  //         table.rows().select();  
  //     } else {
  //         table.rows().deselect();
  //     }
  // });

  $('#selectAll').on('click', function () {
    // if ($(this).prop('checked')) {
    //     table.rows().select();  
    // } else {
    //     table.rows().deselect();
    // }
    $('.delete_checkbox').prop('checked', $(this).prop('checked'));
  });
  // Ram comment this function
  // Delete button functionality
  // $('#deleteSelectedRows').click(function() {
  //     var selectedRows = table.rows({ selected: true }).remove().draw();
  //     if (selectedRows.count() === 0) {
  //         alert('No rows selected!');
  //     }
  // });
});
//*******************************End DataTable */
// CRM JS

document.getElementById('onclickbtnHideShow').addEventListener('click', function () {
  var element = document.getElementById('showDivCont');
  element.classList.toggle('show');
});

//End  CRM JS



// search leads show search Filter
function hideShowDiv() {
  let div = document.getElementById("divTohide");
  let hideBtn = document.querySelector(".hidebtn");

  if (div.style.display === 'none' || div.style.opacity === '0') {
    div.style.display = 'block';
    div.style.height = div.scrollHeight + 'px'; // Ensures the height is set for the transition
    div.style.opacity = '1';
    hideBtn.textContent = "Hide Search Filter";
  } else {
    hideBtn.textContent = "Show Search Filter";
    div.style.height = '0px';
    div.style.opacity = '0';
    // Use a timeout to set display to none after the transition
    setTimeout(() => {
      div.style.display = 'none';
    }, 500); // 500ms matches the CSS transition duration
  }
}
// end search leads show search Filter js

// ******************* PaiChart *******************

var myConfig = {
  "type": "pie",
  "title": {
    "text": "All Potential Jobs - July 2024",
    "fontSize": "17px"
  },
  "series": [{
    values: [59],
    backgroundColor: "#FF5733"
  },
  {
    values: [55],
    backgroundColor: "#1f88b5"
  },
  {
    values: [30],
    backgroundColor: "#5c6bc0"
  },
  {
    values: [28],
    backgroundColor: "#e10078"
  },
  {
    values: [15],
    backgroundColor: '#42a5f5'
  }
  ]
};

zingchart.render({
  id: 'PieChart',
  data: myConfig,
  height: 326,
  width: "100%"
});

// ***************************************************************************

// DEFINE CHART LOCATIONS (IDS)
// -----------------------------
// Main chart render location
let chartId = 'lineChart';

// CHART CONFIG
// -----------------------------
let chartConfig = {
  type: 'mixed',
  title: {
    text: 'Authorised Jobs For Period',
    align: 'left',
    marginLeft: '23%',
    fontSize: '17px', // Adjust the font size as needed
  },
  legend: {
    adjustLayout: true,
    verticalAlign: 'middle',
  },
  plot: {
    animation: {
      delay: 500,
      effect: 'ANIMATION_SLIDE_TOP',
      method: 'ANIMATION_BOUNCE_EASE_OUT',
      sequence: 'ANIMATION_NO_SEQUENCE',
      speed: 975,
    },
  },
  scaleX: {
    labels: [
      'Day 1',
      'Day 2',
      'Day 3',
      'Day 4',
      'Day 5',
      'Day 6',
      'Day 7',
      'Day 8',
    ], //one label for every datapoint
  },
  scaleY: {
    label: {
      text: 'Remaining effort (hours)',
      fontSize: '14px',
    },
    guide: {
      // dashed lines
      visible: false,
    },
  },
  scaleY2: {
    minValue: 0,
    maxValue: 100,
    step: 25, // can define scale step values or default
    label: {
      text: 'Remaing and completed tasks',
      fontSize: '14px',
    },
  },
  crosshairX: {
    lineColor: '#424242',
    lineGapSize: '4px',
    lineStyle: 'dotted',
    plotLabel: {
      backgroundColor: 'white',
      bold: true,
      borderColor: '#e3e3e3',
      borderRadius: '5px',
      fontColor: '#2f2f2f',
      fontFamily: 'Lato',
      fontSize: '12px',
      padding: '15px',
      shadow: true,
      shadowAlpha: 0.2,
      shadowBlur: 5,
      shadowColor: '#a1a1a1',
      shadowDistance: 4,
      textAlign: 'left',
    },
    scaleLabel: {
      backgroundColor: '#424242',
    },
  },
  series: [
    {
      type: 'line',
      text: 'Remaining Tasks',
      values: [35, 42, 67, 89, 25, 34, 67, 85].sort().reverse(),
      lineColor: '#42a5f5',
      marker: {
        visible: 'false',
      },
      scales: 'scale-x, scale-y',
    },
    {
      type: 'line',
      text: 'Remaining Effort',
      values: [25, 32, 57, 79, 15, 24, 57, 75].sort().reverse(),
      lineColor: '#5c6bc0',
      marker: {
        type: 'square',
        backgroundColor: '#5c6bc0',
      },
      scales: 'scale-x, scale-y',
    },
    {
      type: 'line',
      text: 'Completed Tasks',
      values: [90, 80, 70, 60, 50, 40, 30, 20],
      lineColor: '#e10078',
      marker: {
        visible: false,
      },
      scales: 'scale-x, scale-y',
    },
    {
      type: 'bar',
      text: 'Completed Tasks',
      values: [5, 10, 6, 3, 6, 2, 0, 9],
      backgroundColor: '#1f88b5',
      scales: 'scale-x, scale-y-2',
    },
  ],
};

// RENDER CHART
// -----------------------------
zingchart.render({
  id: chartId,
  data: chartConfig,
  height: '325px',
  width: '100%',
});




// --------------------- /* Select option with search  */ ---------------------
let customInput = document.querySelector('.customInput')
selectedData = document.querySelector('.selectedData')
searchInput = document.querySelector('.searchInput input')
ul = document.querySelector('.options ul')
customInputContainer = document.querySelector('.customInputContainer')

window.addEventListener('click', (e) => {
  if (document.querySelector('.searchInput').contains(e.target)) {
    document.querySelector('.searchInput').classList.add('focus')
  } else {
    document.querySelector('.searchInput').classList.remove('focus')
  }
})

var countries = [
  "Custom Input",
  "Afghanistan",
  "Aland Islands",

  "Zimbabwe"
];

customInput.addEventListener('click', () => {
  customInputContainer.classList.toggle('show')
})

let countriesLength = countries.length

for (let i = 0; i < countriesLength; i++) {
  let country = countries[i]
  const li = document.createElement("li");
  const countryName = document.createTextNode(country);
  li.appendChild(countryName);
  ul.appendChild(li);
}


ul.querySelectorAll('li').forEach(li => {
  li.addEventListener('click', (e) => {
    let selectdItem = e.target.innerText
    selectedData.innerText = selectdItem

    for (const li of document.querySelectorAll("li.selected")) {
      li.classList.remove("selected");
    }
    e.target.classList.add('selected')
    customInputContainer.classList.toggle('show')
  })
});

function updateData(data) {
  let selectedCountry = data.innerText
  selectedData.innerText = selectedCountry

  for (const li of document.querySelectorAll("li.selected")) {
    li.classList.remove("selected");
  }
  data.classList.add('selected')
  customInputContainer.classList.toggle('show')
  console.log(selectedCountry);
}

searchInput.addEventListener('keyup', (e) => {
  let searchedVal = searchInput.value.toLowerCase()
  let searched_country = []

  searched_country = countries.filter(data => {
    return data.toLocaleLowerCase().startsWith(searchedVal)
  }).map(data => {
    return `<li onClick="updateData(this)">${data}</li>`
  }).join('')
  ul.innerHTML = searched_country ? searched_country : "<p style='margin-top: 1rem;'>Opps can't find any result <p style='margin-top: .2rem; font-size: .9rem;'>Try searching something else.</p></p>"
})


// ************************************************************************************************

$(window).scroll(function () {
  if ($(this).scrollTop() > 250) {
    $('.sticky-top').addClass('sticky-nav').css('top', '0px');
  } else {
    $('.sticky-top').removeClass('sticky-nav').css('top', '-100px');
  }
});

