import React from 'react';
import './Carousel.css'; // Adjust the path as needed

function Carousel() {
  return (
    <div id="carouselExampleIndicators" className="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
      <div className="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" className="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div className="carousel-inner">
        <div className="carousel-item active">
          <img src="img1.jpg" className="d-block w-100" alt="Image 1" />
          <div className="carousel-caption">
            <div className="main-text">
              EMPOWER YOUR FUTURE <br /> WITH EV INDUSTRY
            </div>
            <div className="sub-text">
              Join our academy to master the technology of tomorrow
            </div>
          </div>
        </div>
        <div className="carousel-item">
          <img src="img2.jpg" className="d-block w-100" alt="Image 2" />
          <div className="carousel-caption">
            <div className="main-text">
              EMPOWER YOUR FUTURE <br /> WITH EV INDUSTRY
            </div>
            <div className="sub-text">
              Join our academy to master the technology of tomorrow
            </div>
          </div>
        </div>
        <div className="carousel-item">
          <img src="img3.jpg" className="d-block w-100" alt="Image 3" />
          <div className="carousel-caption">
            <div className="main-text">
              EMPOWER YOUR FUTURE <br /> WITH EV INDUSTRY
            </div>
            <div className="sub-text">
              Join our academy to master the technology of tomorrow
            </div>
          </div>
        </div>
      </div>
      <button className="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
        <span className="carousel-control-prev-icon" aria-hidden="true"></span>
        <span className="visually-hidden">Previous</span>
      </button>
      <button className="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
        <span className="carousel-control-next-icon" aria-hidden="true"></span>
        <span className="visually-hidden">Next</span>
      </button>
    </div>
  );
}

export default Carousel;
