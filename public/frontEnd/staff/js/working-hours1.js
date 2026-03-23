const selectedDates = {};
const alternateDates = [];
let total_working_week_1 = "0.0";
let total_working_week_2 = "0.0";
let workingPreferences = {
    max_per_day: 8,
    max_per_week: 40,
    postcode: "",
};
document.addEventListener("DOMContentLoaded", function () {
    const patternSelect = document.getElementById("schedule_pattern");
    const resetWorkingHrsBtn = document.getElementById("resetWorkingHrsBtn");
    const applyMondayToWeekBtn = document.getElementById("applyMondayToWeek");

    const tabs = {
        standard: document.getElementById("tab-standard"),
        alternate: document.getElementById("tab-alternate"),
        specific: document.getElementById("tab-specific"),
    };

    function toggleTabs(value) {
        Object.values(tabs).forEach((tab) => (tab.style.display = "none"));
        tabs[value].style.display = "block";

        document.getElementById("editing_week").style.display =
            value === "alternate" ? "block" : "none";
    }

    // Initial state
    toggleTabs(patternSelect.value);

    // On dropdown change
    patternSelect.addEventListener("change", function () {
        toggleTabs(this.value);
        total_working_week_1 = "0.0";
        total_working_week_2 = "0.0";

        $("#total_working_week_1").val(total_working_week_1);
        $("#total_working_week_2").val(total_working_week_2);
        $("#workHoursPerWeekText").text("0.0 hrs/week");
    });
    resetWorkingHrsBtn.addEventListener("click", function () {
        reset_all();
    });
    applyMondayToWeekBtn.addEventListener("click", function () {
        applyMondayToWeek();
    });

    // function reset_all() {
    //     let selectedTabs = $("#tab-" + patternSelect.value);

    //     console.log("RESET " + patternSelect.value);

    //     selectedTabs.find(".dayRow").each(function () {
    //         let hrs = $(this).find(".hours").attr("data-hrsdiffval");
    //         $(this).find(".dayToggle").attr("checked", false);
    //         $(this).removeClass("active");
    //         $(this).find(".workingFields").hide();
    //         $(this).find(".notWorking").show();

    //         console.log(hrs);
    //     });
    //     $("#workHoursPerWeekText").text("0.0 hrs/week");

    // }
    function reset_all() {
        // STANDARD TAB
        $("#workingHoursFormError").addClass("d-none").html("");
        $("#tab-standard")
            .find(".dayRow")
            .each(function () {
                $(this).find(".dayToggle").prop("checked", false);
                $(this).removeClass("active");
                $(this).find(".workingFields").hide();
                $(this).find(".notWorking").show();
            });
        // STANDARD TAB
        $("#tab-specific")
            .find(".dayCard")
            .each(function () {
                $(this).removeClass("active");
            });
        // ALTERNATE TAB
        $("#tab-alternate")
            .find(".dayRow")
            .each(function () {
                $(this).find(".dayToggle").prop("checked", false);
                $(this).removeClass("active");
                $(this).find(".workingFields").hide();
                $(this).find(".notWorking").show();
            });
        $("#total_working_week_2").val("0.0");
        $("#total_working_week_1").val("0.0");
        // SPECIFIC TAB (different structure)
        $("#hoursList").empty(); // specific hours list clear
        $(".selectedCount").text("0 dates selected");

        $("#workHoursPerWeekText").text("0.0 hrs/week");
    }

    function applyMondayToWeek() {
        let selectedTabs = $("#tab-" + patternSelect.value);

        if (patternSelect.value == "standard") {
            selectedTabs.find(".dayRow").each(function () {
                if ($(this).hasClass("weekend")) return;

                $(this).addClass("active");
                $(this)
                    .find(".dayToggle")
                    .prop("checked", true)
                    .trigger("change");
                $(this).find(".workingFields").show();
                $(this).find(".notWorking").hide();
                $(this).find(".startTime").val("09:00");
                $(this).find(".endTime").val("17:00");
                let startDate = new Date(`1970-01-01T09:00`);
                let endDate = new Date(`1970-01-01T17:00`);

                let diff = (endDate - startDate) / 1000 / 60 / 60;
                $(this)
                    .find(".hours")
                    .attr("data-hrsdiffval", diff.toFixed(1))
                    .text(diff.toFixed(1) + " hrs");
            });
            $("#workHoursPerWeekText").text("0.0 hrs/week");
            calculateTotalHours();
        }
    }
});

$(document).on("change", ".dayToggle", function () {
    let row = $(this).closest(".dayRow");

    if ($(this).is(":checked")) {
        let maxPerDay = workingPreferences.max_per_day;

        row.addClass("active");
        row.find(".workingFields").show();
        row.find(".notWorking").hide();

        let startTime = "09:00";
        let allowedHours = Math.min(maxPerDay, 8); // max 8 hrs

        let startDate = new Date(`1970-01-01T${startTime}`);
        let endDate = new Date(
            startDate.getTime() + allowedHours * 60 * 60 * 1000,
        );

        let endTime = endDate.toTimeString().slice(0, 5);

        row.find(".startTime").val(startTime);
        row.find(".endTime").val(endTime);

        row.find(".hours")
            .attr("data-hrsdiffval", allowedHours.toFixed(1))
            .text(allowedHours.toFixed(1) + " hrs");
    } else {
        row.find(".day-error").remove();
        row.removeClass("active");
        row.find(".workingFields").hide();
        row.find(".notWorking").show();
    }
    calculateTotalHours();
});
function calculateHours(row) {
    let start = row.find(".startTime").val();
    let end = row.find(".endTime").val();

    if (!start || !end) return;

    row.find(".day-error").remove();
    $("#workingHoursFormError").addClass("d-none").html("");

    let startDate = new Date(`1970-01-01T${start}`);
    let endDate = new Date(`1970-01-01T${end}`);

    let diff = (endDate - startDate) / 1000 / 60 / 60;
    let maxPerDay = workingPreferences.max_per_day;

    // ❌ minus hours check
    if (diff < 0) {
        let errorSpan = $("<div>", {
            class: "day-error text-danger",
            text: "End time must be greater than start time",
        });

        row.append(errorSpan);

        row.find(".endTime").val("");
        row.find(".hours").attr("data-hrsdiffval", 0).text("0.0 hrs");

        return;
    }

    // ❌ max per day check
    if (diff > maxPerDay) {
        let errorSpan = $("<div>", {
            class: "day-error text-danger",
            text: `Max ${maxPerDay} hrs allowed per day`,
        });

        row.append(errorSpan);

        row.find(".endTime").val("");
        row.find(".hours").attr("data-hrsdiffval", 0).text("0.0 hrs");

        return;
    }

    // ✔ valid hours
    row.find(".hours")
        .attr("data-hrsdiffval", diff.toFixed(1))
        .text(diff.toFixed(1) + " hrs");

    alternateDates.push({
        week_type: $("#schedule_pattern_2").val(),
    });
    console.log(alternateDates);
    calculateTotalHours();
}
function calculateTotalHours() {
    let total = 0;
    let patternSelect2 = document.getElementById("schedule_pattern");
    let activeRows = ".dayRow.active";
    if (patternSelect2.value === "specific") {
        activeRows = ".hourRow";
    } else if (patternSelect2.value === "alternate") {
        if ($(".week_1").hasClass("d-none")) {
            activeRows = ".week_2 .dayRow.active";
        } else {
            activeRows = ".week_1 .dayRow.active";
        }
    }
    $(activeRows).each(function () {
        let hrs = $(this).find(".hours").attr("data-hrsdiffval");

        if (hrs) {
            total += parseFloat(hrs);
        }
    });
    let maxPerDay = workingPreferences.max_per_week;
    // Display total
    if (patternSelect2.value !== "specific" && total > maxPerDay) {
        $("#workingHoursFormError")
            .removeClass("d-none alert-success")
            .css("text-align", "left")
            .html(
                `Total hours (${total.toFixed(1)}) exceed the weekly limit (${maxPerDay.toFixed(1)} hrs).`,
            );
    } else {
        $("#workingHoursFormError").addClass("d-none").html("");
    }
    if (patternSelect2.value === "alternate") {
        let totalAlternate = "0.0";
        let weekLabel = "Week 1";
        if ($(".week_1").hasClass("d-none")) {
            $("#total_working_week_2").val(total.toFixed(1));
            totalAlternate = $("#total_working_week_2").val() || "0.0";
            current_week_counts = $(".week_2 .dayRow.active").length;
            weekLabel = "Week 2";
        } else {
            $("#total_working_week_1").val(total.toFixed(1));
            totalAlternate = $("#total_working_week_1").val();
            current_week_counts = $(".week_1 .dayRow.active").length;
            weekLabel = "Week 1";
        }
        week_1_counts = $(".week_1 .dayRow.active").length;
        week_2_counts = $(".week_2 .dayRow.active").length;

        $(".workingHoursDifferentSchedules").html(
            `<p>You are editing <strong> ${weekLabel}</strong> of the alternating schedule. These
                                                hours will repeat every other week. Switch between Week 1 and Week 2 above
                                                to set different schedules.</p>
                                            <div class="debugWeek mt-2">Debug: Week1 enabled days: ${week_1_counts} | Week2 enabled days:
                                                 ${week_2_counts} | Current enabled days: ${current_week_counts}</div>`,
        );

        $("#workHoursPerWeekText")
            .css("color", "#2563eb")
            .text(totalAlternate + " hrs/week");
    } else {
        $("#workHoursPerWeekText")
            .css("color", "#2563eb")
            .text(total.toFixed(1) + " hrs/week");
    }
}
$(document).on("change", ".startTime, .endTime", function () {
    let patternSelect2 = document.getElementById("schedule_pattern");
    let activeRows = ".dayRow";
    if (patternSelect2.value === "specific") {
        activeRows = ".hourRow";
    }

    calculateHours($(this).closest(activeRows));
    calculateTotalHours();
});

$(".dayToggle").each(function () {
    $(this).trigger("change");
});
document.addEventListener("DOMContentLoaded", () => {
    specificDateToggle();
});
function specificDateToggle() {
    const grid = document.getElementById("calendarGrid");
    const hoursList = document.getElementById("hoursList");
    const selectedCount = document.getElementById("selectedCount");

    const today = new Date();
    const totalDays = 60;

    for (let i = 0; i < totalDays; i++) {
        const date = new Date();
        date.setDate(today.getDate() + i);

        const key = date.toISOString().split("T")[0];
        const label = date.toLocaleDateString("en-US", {
            weekday: "short",
            month: "short",
            day: "numeric",
            year: "numeric",
        });

        const isWeekend = date.getDay() === 0 || date.getDay() === 6;

        const card = document.createElement("div");
        card.className = "dayCard";
        card.setAttribute("data-date", key);
        card.setAttribute("data-isweekend", isWeekend ? "1" : "0");
        card.innerHTML = `
            <div>${label}</div>
            ${isWeekend ? `<span class="badge">Weekend</span>` : ``}
        `;

        card.addEventListener("click", () => {
            card.classList.toggle("active");

            if (card.classList.contains("active")) {
                selectedDates[key] = label;
            } else {
                hoursList
                    .querySelector(`.hourRow[data-date="${key}"]`)
                    .remove();
                delete selectedDates[key];
            }

            renderHours();
        });

        grid.appendChild(card);
    }
}
function renderHours() {
    // hoursList.innerHTML = "";
    // console.log(selectedDates);

    const keys = Object.keys(selectedDates);
    selectedCount.innerText = `${keys.length} dates selected`;

    keys.forEach((dateKey) => {
        const exists = hoursList.querySelector(
            `.hourRow[data-date="${dateKey}"]`,
        );

        if (exists) return;
        const row = document.createElement("div");
        row.className = "hourRow";
        row.setAttribute("data-date", dateKey);
        row.setAttribute("data-specificid", "");
        row.innerHTML = `
            <div class="dateLabel">${selectedDates[dateKey]}</div>
            <input type="time" value="09:00" class="dayTime form-control startTime">
            <span>to</span>
            <input type="time" value="17:00" class="dayTime form-control endTime">
            <span class="hours" data-hrsdiffval='8'>8.0 hrs</span>
        `;

        hoursList.appendChild(row);
    });
    calculateTotalHours();
}

function updateWeekInfo(week) {
    document.querySelector(".highlight").innerText = `Week ${week}`;
}

updateWeekInfo(2); // Week 2
