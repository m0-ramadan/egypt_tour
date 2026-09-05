function shouldLockDate(date) {
    // Lock specific days of the week
    const lockedDays = [0, 2, 4, 6]; // Sunday, Tuesday, Thursday, Saturday
    if (lockedDays.includes(date.getDay())) {
        return true;
    }

    // Lock specific dates
const lockedDates = [
    { year: 2025, month: 11, day: 20 }, // 20-12-2025
    { year: 2025, month: 11, day: 21 }, // 21-12-2025
    { year: 2025, month: 11, day: 22 }, // 22-12-2025
    { year: 2025, month: 11, day: 23 }, // 23-12-2025
    { year: 2025, month: 11, day: 24 }, // 24-12-2025
    { year: 2025, month: 11, day: 25 }, // 25-12-2025
    { year: 2025, month: 11, day: 26 }, // 26-12-2025
    { year: 2025, month: 11, day: 27 }, // 27-12-2025
    { year: 2025, month: 11, day: 28 }, // 28-12-2025
    { year: 2025, month: 11, day: 29 }, // 29-12-2025
    { year: 2025, month: 11, day: 30 }, // 30-12-2025
    { year: 2025, month: 11, day: 31 }, // 31-12-2025

    { year: 2026, month: 0, day: 1 },  // 01-01-2026
    { year: 2026, month: 0, day: 2 },  // 02-01-2026
    { year: 2026, month: 0, day: 3 },  // 03-01-2026
    { year: 2026, month: 0, day: 4 },  // 04-01-2026
    { year: 2026, month: 0, day: 5 }   // 05-01-2026
];


    // Lock range: July 1, 2027 to August 31, 2027
    const rangeStart = new Date(2027, 6, 1);  // month is 0-indexed, so 6 = July
    const rangeEnd = new Date(2027, 7, 31);   // 7 = August
    if (date >= rangeStart && date <= rangeEnd) {
        return true;
    }

    // Lock all dates after 1/1/2028, including 1/1/2028
    const lockAfterDate = new Date(2028, 0, 1); // Jan 1, 2028
    if (date >= lockAfterDate) {
        return true;
    }

    // Check if date is in lockedDates
    if (lockedDates.some(d =>
        date.getFullYear() === d.year &&
        date.getMonth() === d.month &&
        date.getDate() === d.day
    )) {
        return true;
    }

    return false;
}


const datepicker = new easepick.create({
    element: document.getElementById('datepicker'),
    css: [
        "https://www.luxorandaswan.com/lite.css"
    ],
    zIndex: 10,
    firstDay: 6,
    header: "",
    format: 'DD/MM/YYYY',
    readonly: false,
    AmpPlugin: {
        dropdown: {
            years: true,
            months: true,
            minYear: 2023,
            maxYear: 2028
        },
        darkMode: false
    },
    LockPlugin: {
        presets: false,
        minDate: new Date(),
        inseparable: true,
        filter: shouldLockDate
    },
    plugins: [
        "AmpPlugin",
        "LockPlugin"
    ]
});

const datepicker2 = new easepick.create({
    element: document.getElementById('datepicker2'),
    css: [
        "https://www.luxorandaswan.com/lite.css"
    ],
    zIndex: 10,
    firstDay: 6,
    header: "",
    format: 'DD/MM/YYYY',
    readonly: false,
    AmpPlugin: {
        dropdown: {
            years: true,
            months: true,
            minYear: 2023,
            maxYear: 2028
        },
        darkMode: false
    },
    LockPlugin: {
        presets: false,
        minDate: new Date(),
        inseparable: true,
        filter: shouldLockDate
    },
    plugins: [
        "AmpPlugin",
        "LockPlugin"
    ]
});
