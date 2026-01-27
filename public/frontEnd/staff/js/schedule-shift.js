let draggedShift = null;

document.addEventListener('dragstart', function (e) {
    if (e.target.classList.contains('shift')) {
        draggedShift = e.target;
        e.dataTransfer.effectAllowed = 'move';

        setTimeout(() => {
            draggedShift.classList.add('dragging');
        }, 0);
    }
});

document.addEventListener('dragend', function () {
    if (draggedShift) {
        draggedShift.classList.remove('dragging');
        draggedShift = null;
    }
});

/* DROP ZONES */
document.querySelectorAll('.dropzone').forEach(zone => {

    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    zone.addEventListener('dragleave', function () {
        this.classList.remove('drag-over');
    });

    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('drag-over');

        if (!draggedShift) return;

        /* Move shift in UI */
        this.appendChild(draggedShift);

        /* Extract data */
        const shiftId = draggedShift.dataset.shiftId;
        const newDate = this.dataset.date;
        const newStaffId = this.dataset.staffId;

        console.log({
            shift_id: shiftId,
            staff_id: newStaffId,
            date: newDate
        });

        /* SAVE TO SERVER */
        saveShiftMove(shiftId, newStaffId, newDate);
    });
});

/* AJAX SAVE */
function saveShiftMove(shiftId, staffId, date) {
    fetch('/shifts/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            shift_id: shiftId,
            staff_id: staffId,
            date: date
        })
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) {
            alert('Failed to move shift');
        }
    })
    .catch(() => {
        alert('Server error');
    });
}
