document.addEventListener('DOMContentLoaded', function () {
    const patternSelect = document.getElementById('schedule_pattern');

    const tabs = {
        standard: document.getElementById('tab-standard'),
        alternate: document.getElementById('tab-alternate'),
        specific: document.getElementById('tab-specific')
    };

    function toggleTabs(value) {
        Object.values(tabs).forEach(tab => tab.style.display = 'none');
        tabs[value].style.display = 'block';

        document.getElementById('editing_week').style.display = (value === 'alternate') ? 'block' : 'none';

    }

    // Initial state
    toggleTabs(patternSelect.value);

    // On dropdown change
    patternSelect.addEventListener('change', function () {
        toggleTabs(this.value);
    });
});

$(document).on('change', '.dayToggle', function () {
    let row = $(this).closest('.dayRow');

    if ($(this).is(':checked')) {
        row.addClass('active');
        row.find('.workingFields').show();
        row.find('.notWorking').hide();
    } else {
        row.removeClass('active');
        row.find('.workingFields').hide();
        row.find('.notWorking').show();
    }
});

function calculateHours(row) {
    let start = row.find('.startTime').val();
    let end = row.find('.endTime').val();

    if (!start || !end) return;

    let startDate = new Date(`1970-01-01T${start}`);
    let endDate = new Date(`1970-01-01T${end}`);

    let diff = (endDate - startDate) / 1000 / 60 / 60;
    row.find('.hours').text(diff.toFixed(1) + ' hrs');
}

$(document).on('change', '.startTime, .endTime', function () {
    calculateHours($(this).closest('.dayRow'));
});

$('.dayToggle').each(function () {
    $(this).trigger('change');
});

document.addEventListener('DOMContentLoaded', () => {

    const grid = document.getElementById('calendarGrid');
    const hoursList = document.getElementById('hoursList');
    const selectedCount = document.getElementById('selectedCount');

    const selectedDates = {};

    const today = new Date();
    const totalDays = 60;

    for (let i = 0; i < totalDays; i++) {
        const date = new Date();
        date.setDate(today.getDate() + i);

        const key = date.toISOString().split('T')[0];
        const label = date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const isWeekend = date.getDay() === 0 || date.getDay() === 6;

        const card = document.createElement('div');
        card.className = 'dayCard';
        card.innerHTML = `
            <div>${label}</div>
            ${isWeekend ? `<span class="badge">Weekend</span>` : ``}
        `;

        card.addEventListener('click', () => {
            card.classList.toggle('active');

            if (card.classList.contains('active')) {
                selectedDates[key] = label;
            } else {
                delete selectedDates[key];
            }

            renderHours();
        });

        grid.appendChild(card);
    }

    function renderHours() {
        hoursList.innerHTML = '';
        const keys = Object.keys(selectedDates);
        selectedCount.innerText = `${keys.length} dates selected`;

        keys.forEach(dateKey => {
            const row = document.createElement('div');
            row.className = 'hourRow';

            row.innerHTML = `
                <div class="dateLabel">${selectedDates[dateKey]}</div>
                <input type="time" value="09:00">
                <span>to</span>
                <input type="time" value="17:00">
                <span class="hoursBadge">8.0 hrs</span>
            `;

            hoursList.appendChild(row);
        });
    }

});

function updateWeekInfo(week) {
    document.querySelector('.highlight').innerText = `Week ${week}`;
}

updateWeekInfo(2); // Week 2
