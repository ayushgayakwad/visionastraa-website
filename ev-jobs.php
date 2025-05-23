<?php
$servername = "localhost";
$username = "u707137586_EV_Jobs";
$password = "Sjj*/u9~xH9";
$dbname = "u707137586_EV_Jobs";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT role, company, location, url FROM job_listings";
$result = $conn->query($sql);

$jobs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Jobs - VisionAstraa EV Academy</title>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2PV0BKLV94"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2PV0BKLV94');
    </script>
    <link rel="icon" href="images/favicon-16x16.png" type="image/png">
    <link rel="stylesheet" href="css/ev-careers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="logo-container">
        <a href="the-best-ev-course-in-india.html"><img src="images/1000052219-removebg-preview.png" alt="EV Academy Logo" class="logo"></a>
      </div>

      <!-- Hamburger Icon -->
      <div class="hamburger" onclick="toggleMenu()" id="hamburger">
          <span></span>
          <span></span>
          <span></span>
      </div>
        
      <ul class="nav-links">
        <li><a href="the-best-ev-course-in-india.html">EV Academy</a></li>
        <li><a href="ev-course.html">EV Course</a></li>
        <li><a href="ev-projects.html">EV Projects</a></li>
        <!-- <li><a href="ev-mentors.html">EV Mentors</a></li> -->
        <li><a href="ev-jobs.html">EV Jobs</a></li>
        <!-- <li><a href="ev-careers.html">EV Careers</a></li> -->
        <li><a href="ev-contact.html">Contact Us</a></li>
        <li><a href="best-ev-training-institute.html">About Us</a></li>
        <li><a href="ev-application.html" class="apply">Apply Now</a></li>
      </ul>
  </nav>

  <script>
      function toggleMenu() {
          const navLinks = document.querySelector(".nav-links");
          navLinks.classList.toggle("show");
      }
  </script>
</header>
    <section class="join-team">
        <h2>EV Jobs</h2>
        <p>Discover exciting career opportunities in the rapidly growing EV industry. Be a part of the revolution driving sustainable mobility and shaping the future of transportation.</p>
        <button class="join-button" onclick="location.href='#open-positions-section'">View open positions</button>
    </section>

<section id="open-positions-section" class="careers-container">
    <div class="header-container">
        <h2>Open Positions</h2>
        <div class="search-bar">
            <label for="search-bar">
                <i class="fas fa-search"></i>
            </label>
            <input type="text" id="search-bar" placeholder="Type positions...">
        </div>               
    </div>

    <br>

    <div class="job-list">
        <?php foreach ($jobs as $job): ?>
            <div class="job-item">
                <div class="job-content">
                    <span class="job-type" style="margin-bottom: 4px;">Full-Time / Location: <?= htmlspecialchars($job['location']) ?></span>
                    <h3 style="margin-bottom: 4px;"> <?= htmlspecialchars($job['role']) ?> </h3>
                    <p style="margin-bottom: 4px;"> Company: <?= htmlspecialchars($job['company']) ?> </p>
                    <a href="<?= htmlspecialchars($job['url']) ?>" class="job-description-link" target="_blank">Job Description</a>
                </div>
                <a href="<?= htmlspecialchars($job['url']) ?>" style="text-decoration: none;" target="_blank"><button class="apply-btn">Apply now</button></a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="newsletter-container">
  <h3>Subscribe to our newsletter</h3>
  <div id="newsletterResponse" style="margin-top: 16px;"></div>
  <div class="newsletter-input">
      <span class="email-icon">✉️</span>
      <input type="email" id="newsletterEmail" placeholder="Enter your email" required>
      <button type="submit" id="subscribeButton">Subscribe</button>
  </div>
</div>
    
<footer>
  <div class="footer-container">
    <div class="about-us">
      <h3>ABOUT US</h3>
      <p>Empowering India’s talent to lead the global EV industry.</p><br>
      <p>We are committed to bridging the gap between academia and industry, providing students with the practical skills and knowledge required to excel in the EV sector. Our programs foster innovation, entrepreneurship, and a deep understanding of the EV ecosystem.</p>
    </div>
    
    <div class="footer-links">
      <div class="links">
        <h3>LINKS</h3>
        <ul>
          <li><a href="the-best-ev-course-in-india.html">EV Academy</a></li>
          <li><a href="ev-course.html">EV Course</a></li>
          <li><a href="ev-projects.html">EV Projects</a></li>
          <!-- <li><a href="ev-mentors.html">EV Mentors</a></li> -->
          <li><a href="ev-jobs.html">EV Jobs</a></li>
        </ul>
      </div>

      <div class="Company">
      <h3>COMPANY</h3>
        <ul>
          <li><a href="best-ev-training-institute.html">About Us</a></li>
          <!-- <li><a href="ev-careers.html">EV Careers</a></li> -->
        </ul>
      </div>
                     
      <div class="plans">
        <h3>CONTACT</h3>
        <ul>  
          <li><a href="ev-contact.html">Contact Us</a></li>
        </ul>
      </div>
    </div>
      
    <div class="follow-us">
      <h3>FOLLOW US</h3>
      <ul>
        <li><a href="https://www.instagram.com/va_ev_academy/">Instagram</a></li>
        <li><a href="https://in.linkedin.com/company/va-ev-academy">LinkedIn</a></li>
        <li><a href="https://www.youtube.com/@VisionAstraaEVAcademy">YouTube</a></li>
      </ul>
    </div>
  </div>
  
  <hr>
  
  <div class="footer-bottom">
    <div class="footer-logo-container">
      <img src="images/1000052219-removebg-preview.png" alt="VisionAstraa Logo">
    </div>
    <p>&copy; 2024 VisionAstraa. All rights reserved.</p>
    <button class="ev-academy-button">EV Academy</button>
  </div>
</footer>

<script>
  document.getElementById('subscribeButton').addEventListener('click', function (e) {
        e.preventDefault();
        var emailInput = document.getElementById('newsletterEmail');
        var email = emailInput.value;
        var responseElement = document.getElementById('newsletterResponse');
        
        if (email && validateEmail(email)) {
            var formData = new FormData();
            formData.append('email', email);
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/ev-subscribe-newsletter.php', true);
            
            xhr.onload = function () {
                var response = JSON.parse(this.responseText);
                responseElement.innerHTML = response.message;
                responseElement.style.color = response.success ? 'green' : 'red';
                if (response.success) {
                    emailInput.value = ''; 
                }
            };
            
            xhr.onerror = function() {
                responseElement.innerHTML = "Error submitting your email. Please try again.";
                responseElement.style.color = 'red';
            };
            
            xhr.send(formData);
        } else {
            responseElement.innerHTML = "Please enter a valid email address.";
            responseElement.style.color = 'red';
        }
    });

    // Email validation function
    function validateEmail(email) {
        var re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(String(email).toLowerCase());
    }
    
  (function() {
    const widgetConfig = {
      backgroundColor: '#25D366',
      position: 'right',
      xoffset: '25px',
      yoffset: '25px',
      logoUrl: 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg',
      whatsappNo: '918075664438',
      whatsappText: 'Hello VisionAstraa team,',
      popupTitle: 'Dear EV Aspirant, life changing opportunity is just a click away',
      buttonText: 'Start Chat'
    };
  
    let showPopup = false;
    const container = document.createElement('div');
    document.body.appendChild(container);
  
    function renderWidget() {
      container.innerHTML = `
        <div class="z_ft_widget" style="
          background-color: ${widgetConfig.backgroundColor};
          ${widgetConfig.position}: ${widgetConfig.xoffset};
          bottom: ${widgetConfig.yoffset};
          width: 50px;
          height: 50px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          position: fixed;
          cursor: pointer;
          z-index: 999;
          transition: opacity 0.3s;
        ">
          <img src="${widgetConfig.logoUrl}" alt="WhatsApp" style="width: 32px; height: 32px;">
          ${showPopup ? `
            <div class="z_ft_popup" style="
              position: absolute;
              bottom: 60px;
              width: 200px;
              background: white;
              border-radius: 8px;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              padding: 16px;
              ${widgetConfig.position}: 0;
            ">
              <h3 style="
                font-size: 16px;
                font-weight: 600;
                text-align: center;
                margin: 30px 0px;
              ">${widgetConfig.popupTitle}</h3>
              <a href="https://wa.me/${widgetConfig.whatsappNo}?text=${widgetConfig.whatsappText}" target="_blank" rel="noopener noreferrer" style="
                display: inline-block;
                background: #25D366;
                color: white;
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                transition: background-color 0.3s;
              ">${widgetConfig.buttonText}</a>
              <a href="https://visionastraa.com" 
               target="_blank" 
               rel="noopener noreferrer" 
               style="
                text-align: center;
                font-size: 12px;
                color: #666;
                text-decoration: none;
                border-top: 1px solid #eee;
                padding-top: 12px;
                margin-top: 10px;
              ">Powered by ZEPIC</a>

            </div>
          ` : ''}
        </div>
      `;
  
      container.querySelector('.z_ft_widget').addEventListener('click', (e) => {
        if (e.target.closest('.z_ft_popup-button')) return;
        showPopup = !showPopup;
        renderWidget();
      });
    }
  
    renderWidget();
  })()
</script>
<script src="js/ev-careers.js"></script>    

</body>
</html>
