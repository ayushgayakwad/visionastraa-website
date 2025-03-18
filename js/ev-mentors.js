document.addEventListener('DOMContentLoaded', function() {
    const mentors = [
        {
            image: 'images/mentor1.jpg',
            name: 'Martin BurgBatcher',
            title: 'Executive Director',
            description: 'American Axle Manufacturing, Frankfurt, Germany<br>29+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/martinburgbatcher'
        },
        {
            image: 'images/mentor2.jpg',
            name: 'Kishore Ochani',
            title: 'COO',
            description: 'NRB Bearings, Detroit, MI, USA<br>30+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/kishoreochani'
        },
        {
            image: 'images/mentor3.jpg',
            name: 'Matti Vint',
            title: 'R&D Director',
            description: 'Marelli, Canton, MI, USA<br>30+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/mattivint'
        },
        {
            image: 'images/mentor4.jpg',
            name: 'Raj Puttaiah',
            title: 'Vice President',
            description: 'EV Strategy and Battery Systems, Marelli, Detroit, MI, USA<br>25+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/rajputtaiah'
        },
        {
            image: 'images/mentor5.jpg',
            name: 'Rahul Sagar',
            title: 'Head Of Driveline R&D',
            description: 'FPT Industrial, Turin, Italy<br>17+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/rahulsagar'
        },
        {
            image: 'images/mentor6.jpg',
            name: 'Subith Vasu',
            title: 'Professor',
            description: 'University of Central Florida, Florida, USA<br>21+ years in Auto Industry',
            linkedin: 'https://www.linkedin.com/in/subithvasu'
        }
    ];

    let currentIndex = 0;

    function updateCarousel() {
        const mentor = mentors[currentIndex];
        const carouselItems = document.querySelectorAll('.carousel-item');
        
        carouselItems.forEach((item, index) => {
            if (index === currentIndex) {
                item.style.display = 'block';

                const nameElement = item.querySelector('.mentor-name');
                nameElement.innerHTML = `<a href="${mentor.linkedin}" target="_blank">${mentor.name}</a>`;
            } else {
                item.style.display = 'none';
            }
        });
    }

    document.querySelector('.prev').addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + mentors.length) % mentors.length;
        updateCarousel();
    });

    document.querySelector('.next').addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % mentors.length;
        updateCarousel();
    });

    setInterval(() => {
        currentIndex = (currentIndex + 1) % mentors.length;
        updateCarousel();
    }, 3000);

    updateCarousel();
});
