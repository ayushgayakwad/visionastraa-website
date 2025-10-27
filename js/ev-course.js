document.addEventListener("DOMContentLoaded", function () {
    const popup = document.getElementById("popup");
    const popupTitle = document.getElementById("popup-title");
    const popupContentList = document.getElementById("popup-content-list");
    const closeBtn = document.querySelector(".close-btn");
    const learnMoreButtons = document.querySelectorAll(".learn-more-btn");


    // Module Descriptions
    const moduleDetails = {
        1: {
            title: "Module 1: EV & Automotive Basics",
            content: [
                "Overview of Electric Vehicles",
                "EV Outlook globally and in India",
                "Basics of EV Engineering",
                "Types of EVs and its Architecture",
                "Basics of EV Powertrain",
                "Powertrain evolution and current main trends",
                "Main elements of powertrain",
                "Key elements of powertrain design - Motor, inverter, gearbox, e-axle"
            ]
        },
        2: {
            title: "Module 2: Automotive System Engineering and Tools",
            content: [
                "Introduction",
                "Current State of the Art",
                "Model Based System Engineering and Its Impact",
                "Tools: IBM DOORS, Siemens Polarion",
                "System Requirement Specification (SRS) Management and Tools"
            ]
        },
        3: {
            title: "Module 3: EV Powertrain System Modeling and Simulation using MATLAB Simulink",
            content: [
                "Vehicle Dynamics",
                "Modeling Vehicle Acceleration using Matlab",
                "Acceleration performance parameters",
                "Modelling Electric Vehicle Range",
                "Drive Cycle",
                "1D/2D Simulation basics",
                "System modeling for motor, gearbox and inverter",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        4: {
            title: "Module 4: Energy Storage Devices",
            content: [
                "Introduction to Vehicle battery technologies",
                "Battery Parameters",
                "Battery Chemistry and Cell Types",
                "Battery Design and Engineering",
                "Battery Modelling",
                "Battery Charging",
                "Battery Pack Development Process",
                "Electrical Design of Battery Pack",
                "Mechanical Design of Battery Pack",
                "Thermal Design of Battery Pack",
                "Module, individual Cells and its configuration",
                "Introduction to Fuel Cells",
                "Fuel Cells - A Real Option?",
                "Hydrogen Fuel Cells - Basic Principles",
                "Super Capacitor based energy storage and its analysis",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        5: {
            title: "Module 5: Battery Management System (BMS) Design",
            content: [
                "Battery Management System Requirement",
                "Battery State of Charge and State of Health Estimation, Cell Balancing",
                "SoC Estimating Algorithms",
                "Functional Safety",
                "Modelling and Simulation using MATLAB",
                "Design of battery BMS",
                "Hardware Implementation",
                "Manufacturing Processes",
                "Practical Applications and Case Studies",
                "Laboratory and Hands-on Sessions",
                "Industry Compliance and Standards",
                "Future Trends and Innovations",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        "6-A": {
            title: "Module 6A: Power Electronics(EEE/ECE Only)",
            content: [
                "Power Electronic Basics",
                "Power switches IGBT, MOSFET, SiC MOSFET and their application",
                "Mixed-Signal Circuit Design",
                "EMI/EMC Design Principles",
                "Shielding, grounding, and filtering techniques for EMI/EMC compliance.",
                "Switch-Mode dc–dc Converter",
                "Switch-Mode dc–ac Inverters",
                "Switch-Mode ac–dc Converters",
                "Practical Aspects of Power Converter Design",
                "Power Converters Control",
                "Bi Directional Converters",
                "Hands on with Pspice / LTSpice / PSIM",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        "6-B": {
            title: "Module 6B: Embedded Systems For EV Applications(EEE/ECE Only)",
            content: [
                "Introduction to Embedded Systems in Automotive Applications",
                "Firmware Design Principles",
                "Embedded C Programming",
                "RTOS",
                "Automotive Communication Protocols CAN, SPI, I2C, UART",
                "Embedded Systems for Vehicle Diagnostics and Maintenance",
                "Automotive Infotainment and Human-Machine Interface (HMI)",
                "Embedded Systems for Vehicle Safety and Driver Assistance",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        "6-C": {
            title: "Module 6C: E-Axile Design(Only ME)",
            content: [
                "E-Axle Basics",
                "Gear Design basics",
                "Housing Design Basics",
                "Bearing Design",
                "Seal Design",
                "Shaft Design",
                "Fluid Mechanics",
                "Fastener Design",
                "3D design of the e-Axle using CATIA/NX",
                "Durability Calculation",
                "Gear box design",
                "FMEA",
                "Reliability and Confidence",
                "Inverter Integration",
                "NVH",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        "6-D": {
            title: "Module 6D: Powertrain Design Considerations(Only for ME)",
            content: [
                "Aerodynamic Considerations",
                "Consideration of Rolling Resistance",
                "Transmission Efficiency",
                "Consideration of Vehicle Mass",
                "Electric Vehicle Chassis and Body Design",
                "General Issues in Design",
                "Performance Analysis using MATLAB Simulations",
                "Group assignment and Project",
                "Project Review and group discussion"
            ]
        },
        7: {
            title: "Module 7: Electric Machines and their Controllers",
            content: [
                "E-motor design- basics",
                "Different E-Motor Topologies",
                "Current trends and front runners",
                "Brushed DC Motor",
                "PMSM Motor- Main stream",
                "BLDC Motor",
                "Induction Motor",
                "SRM and other special Motors (Linear, Axial, Hub)",
                "Motor Control Basics",
                "Motor Control Advanced",
                "Performance Analysis of Different Motors using MATLAB Simulink",
                "Thermal Modeling Basics",
                "Thermal Modeling Advanced",
                "Motor Cooling, Efficiency, Size and Mass",
                "CAE & CFD Basics",
                "Test motor on a test bench",
                "Group assignment and Project",
                "Project Review and group discussion"
                        ]
        },
        8: {
            title: "Module 8: Testing, Validation & Certification",
            content: [
                "Description of test set up",
                "Framing of test cases and test plan",
                "Checking for the losses and efficiency of motor for different operating conditions",
                "Cooling circuit and thermal checking",
                "No load & run tests",
                "Checking of harmonics and power factor",
                "Testing motor for environmental and mechanical severities as per regulatory standards",
                "Functional testing",
                "Testing for environmental and mechanical severities as per SAE /ISO standards",
                "Electromagnetic interference & compatibility (EMI/EMC)",
                "Why EMI/EMC in an automobile",
                "Regulatory Requirements"
            ]
        }
        
    };

    // Open the popup with content
    learnMoreButtons.forEach(button => {  // Updated from knowMoreButtons
        button.addEventListener("click", function () {
    
            let moduleId = this.getAttribute("data-module");

            // Check if the module ID exists in the moduleDetails object
            if (moduleDetails[moduleId]) {
                popupTitle.innerText = moduleDetails[moduleId].title;
                
                // Clear previous content and add bullet points
                popupContentList.innerHTML = "";
                moduleDetails[moduleId].content.forEach(item => {
                    let li = document.createElement("li");
                    li.textContent = item;
                    popupContentList.appendChild(li);
                });

                popup.style.display = "flex"; // Show the pop-up
            }
        });
    });

    // Close the popup when the close button is clicked
    closeBtn.addEventListener("click", function () {
        popup.style.display = "none"; // Hide the pop-up
    });

    // Close the popup when clicking outside the modal content
    popup.addEventListener("click", function (e) {
        if (e.target === popup) {
            popup.style.display = "none"; // Hide the pop-up
        }
    });
});

document.querySelectorAll('.know-more').forEach(button => {
    button.addEventListener('click', function () {
        const extraFeatures = this.previousElementSibling;
        extraFeatures.classList.toggle('hidden');
        this.textContent = extraFeatures.classList.contains('hidden') ? "Know More" : "Show Less";
    });
});

learnMoreButtons.forEach(button =>
{
    button.addEventListener("click", function () {
        let moduleId = this.getAttribute("data-module");
        console.log("Clicked Module:", moduleId); // Debugging
        
        if (moduleDetails[moduleId]) {
            popupTitle.innerText = moduleDetails[moduleId].title;

            popupContentList.innerHTML = "";
            moduleDetails[moduleId].content.forEach(item => {
                let li = document.createElement("li");
                li.textContent = item;
                popupContentList.appendChild(li);
            });

            popup.style.display = "flex";
        } else {
            console.error("Module not found:", moduleId);
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".btn[href='#course-content']").addEventListener("click", function (event) {
        event.preventDefault();
        document.querySelector("#course-content").scrollIntoView({ behavior: "smooth" });
    });

    document.querySelector(".btn[href='#program-fee']").addEventListener("click", function (event) {
        event.preventDefault();
        document.querySelector("#program-fee").scrollIntoView({ behavior: "smooth" });
    });
});
