document.querySelector('.join-button').addEventListener('click', function() {
    document.querySelector('#open-positions-section').scrollIntoView({ behavior: 'smooth' });
});



document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-bar");
    const jobItems = document.querySelectorAll(".job-item");

    function filterJobs() {
        const searchText = searchInput.value.toLowerCase().trim();

        jobItems.forEach(job => {
            const jobTitle = job.querySelector("h3").textContent.toLowerCase();
            if (jobTitle.includes(searchText)) {
                job.style.opacity = "0"; // Smooth transition before showing
                setTimeout(() => {
                    job.style.display = "flex";
                    job.style.opacity = "1";
                }, 200);
            } else {
                job.style.opacity = "0"; // Smooth transition before hiding
                setTimeout(() => {
                    job.style.display = "none";
                }, 200);
            }
        });
    }

    // Debounce function to improve performance (delays execution while typing)
    function debounce(func, delay) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(func, delay);
        };
    }

    // Attach event listener with debounce
    searchInput.addEventListener("keyup", debounce(filterJobs, 150));
});
