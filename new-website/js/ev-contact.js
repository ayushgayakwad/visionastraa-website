document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM fully loaded and parsed");
    
    // Fetch and load navbar.html
    fetch('navbar.html')
        .then(response => {
            if (!response.ok) {
                throw new Error("Navbar file not found!");
            }
            return response.text();
        })
        .then(data => {
            document.getElementById("navbar-placeholder").innerHTML = data;
            console.log("Navbar loaded");
            setupNavbar(); // Call function after navbar loads
        })
        .catch(error => console.error("Error loading navbar:", error));
});

// Function to set up the hamburger menu after navbar loads
function setupNavbar() {
    const hamburger = document.getElementById("hamburger");
    const navLinks = document.getElementById("navLinks");

    if (hamburger && navLinks) {
        console.log("Hamburger and navLinks elements found");
        hamburger.addEventListener("click", function () {
            console.log("Hamburger clicked");
            navLinks.classList.toggle("show");
        });
    } else {
        console.error("Navbar elements not found!");
    }
}