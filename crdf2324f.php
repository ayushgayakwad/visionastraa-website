<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Recruitment Drive for 2023/24 Freshers</title>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2PV0BKLV94"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2PV0BKLV94');
    </script>
    <meta name="title" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta name="description" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://visionastraa.com/crdf2324f.html" />
    <meta property="og:title" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta property="og:description" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta property="og:image" content="https://visionastraa.com/image/VisionAstraa_logo.jpg" />
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://visionastraa.com/crdf2324f.html" />
    <meta property="twitter:title" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta property="twitter:description" content="Campus Recruitment Drive for 2023/24 Freshers" />
    <meta property="twitter:image" content="https://visionastraa.com/image/VisionAstraa_logo.jpg" />
    <style>
        body, html {
            font-family: Arial, sans-serif;
            background-color: #1c1c1c; 
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: auto;
        }
        .form-container {
            background-color: #202020;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            margin: 20px auto;
            overflow-y: auto;
        }
        .form-container h1 {
            text-align: center;
            color: #fff;
            margin-top: 0;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }
        .form-container label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="tel"],
        .form-container input[type="file"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            color: #000;
            background-color: #fff;
            border: 1px solid #fff;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container textarea {
            resize: vertical;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #fff;
            border-radius: 20px;
            color: black;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #5642a3;
            color: #fff;
        }
        .navigate-button {
            background-color: #007bff;
            margin-top: 10px;
            width: 100%;
            padding: 10px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }
        .navigate-button:hover {
            background-color: #0069d9;
        }
        .message {
            margin-top: 20px;
            font-size: 16px;
            text-align: center;
        }
        .contact-admissions {
            text-align: center;
            margin-top: 20px;
            color: #fff;
        }
        .contact-admissions a {
            text-decoration: none;
            color: #007bff;
            font-size: 18px;
        }
        .contact-admissions a:hover {
            text-decoration: underline;
        }
        .logo-container {
            text-align: center;
        }
        .logo {
            max-width: 100px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo-container">
            <img src="image/transpatent_vsa.png" alt="Logo" class="logo">
        </div>
        <h1>Campus Recruitment Drive for 2023/24 Freshers</h1>

        <form id="applicationForm" onsubmit="submitForm(event);" enctype="multipart/form-data">
            <label for="first_name">First name</label>
            <input type="text" id="first_name" name="first_name" pattern="[A-Za-z\s]+" title="Please enter only letters." required>

            <label for="last_name">Last name</label>
            <input type="text" id="last_name" name="last_name" pattern="[A-Za-z\s]+" title="Please enter only letters." required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number." required>

            <label for="location">Your Location (City/Town)</label>
            <input type="text" id="location" name="location" required>

            <label for="college">College</label>
            <input type="text" id="college" name="college" required>

            <label for="degree">Degree (B.E/B.Tech/M.Tech)</label>
            <input type="text" id="degree" name="degree" required>

            <label for="specialization">Specialization (Branch)</label>
            <input type="text" id="specialization" name="specialization" required>

            <label for="graduation">Year of Graduation</label>
            <input type="text" id="graduation" name="graduation" pattern="\d{4}" title="Please enter a valid year." required>

            <button type="submit">Continue</button>
        </form>

        <div id="message" class="message"></div>
    </div>

    <script>
        function submitForm(event) {
            event.preventDefault();

            const form = document.getElementById('applicationForm');
            const formData = new FormData(form);

            fetch('php/submit_crdf2324f.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                if (data.success) {
                    messageDiv.textContent = "Your application has been submitted successfully!";
                    messageDiv.style.color = "green";
                    form.reset();
                    const token = 'access_granted'; 
                    const expirationTime = Date.now() + (10 * 60 * 1000);
                    localStorage.setItem('token', token);
                    localStorage.setItem('expires_at', expirationTime);

                    window.location.href = 'crdf2324f2.php';
                } else {
                    messageDiv.textContent = "Error: " + data.error;
                    messageDiv.style.color = "red";
                }
            })
            .catch(error => {
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = "There was an error submitting the form.";
                messageDiv.style.color = "red";
            });
        }
    </script>
</body>
</html>
