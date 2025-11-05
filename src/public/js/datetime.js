function updateDateTime() {
    const now = new Date();

    const dateElement = document.getElementById('current-date');
    const timeElement = document.getElementById('current-time');

    if (dateElement) {
        const dateOptions = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'short' };
        const displayDate = now.toLocaleDateString('ja-JP', dateOptions);
        const isoDate = now.toISOString().split('T')[0];

        dateElement.textContent = displayDate;
        dateElement.setAttribute('datetime', isoDate);
    }

    if (timeElement) {
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const displayTime = `${hours}:${minutes}`;
        const isoTime = now.toISOString();

        timeElement.textContent = displayTime;
        timeElement.setAttribute('datetime', isoTime);
    }
}

updateDateTime();
setInterval(updateDateTime, 60000);