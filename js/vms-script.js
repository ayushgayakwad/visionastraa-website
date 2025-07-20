document.addEventListener("DOMContentLoaded", () => {
  initializeHeader()
  initializeMobileMenu()
  initializeAnimations()
  initializeContactForm()
  setCurrentYear()
})

function initializeHeader() {
  const header = document.getElementById("header")

  function handleScroll() {
    if (window.scrollY > 10) {
      header.classList.add("scrolled")
    } else {
      header.classList.remove("scrolled")
    }
  }

  window.addEventListener("scroll", handleScroll)

  handleScroll()
}

function initializeMobileMenu() {
  const mobileMenuBtn = document.getElementById("mobileMenuBtn")
  const mobileNav = document.getElementById("mobileNav")

  if (mobileMenuBtn && mobileNav) {
    mobileMenuBtn.addEventListener("click", () => {
      const isActive = mobileMenuBtn.classList.contains("active")

      if (isActive) {
        mobileMenuBtn.classList.remove("active")
        mobileNav.classList.remove("active")
      } else {
        mobileMenuBtn.classList.add("active")
        mobileNav.classList.add("active")
      }
    })

    const mobileLinks = mobileNav.querySelectorAll(".nav-link-mobile")
    mobileLinks.forEach((link) => {
      link.addEventListener("click", () => {
        mobileMenuBtn.classList.remove("active")
        mobileNav.classList.remove("active")
      })
    })

    document.addEventListener("click", (event) => {
      if (!mobileMenuBtn.contains(event.target) && !mobileNav.contains(event.target)) {
        mobileMenuBtn.classList.remove("active")
        mobileNav.classList.remove("active")
      }
    })
  }
}

function initializeAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  const animateElements = document.querySelectorAll(".card, .service-card, .feature-item")
  animateElements.forEach((el) => {
    el.style.opacity = "0"
    el.style.transform = "translateY(20px)"
    el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(el)
  })
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function showNotification(message, type = "info") {
  const existingNotification = document.querySelector(".notification")
  if (existingNotification) {
    existingNotification.remove()
  }

  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `

  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        ${type === "success" ? "background-color: #10b981; color: white;" : ""}
        ${type === "error" ? "background-color: #ef4444; color: white;" : ""}
        ${type === "info" ? "background-color: #3b82f6; color: white;" : ""}
    `

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.transform = "translateX(100%)"
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove()
        }
      }, 300)
    }
  }, 5000)
}

function setCurrentYear() {
  const yearElements = document.querySelectorAll("#currentYear")
  const currentYear = new Date().getFullYear()

  yearElements.forEach((element) => {
    element.textContent = currentYear
  })
}

function initializeSmoothScrolling() {
  const links = document.querySelectorAll('a[href^="#"]')

  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()

      const targetId = this.getAttribute("href").substring(1)
      const targetElement = document.getElementById(targetId)

      if (targetElement) {
        const headerHeight = document.getElementById("header").offsetHeight
        const targetPosition = targetElement.offsetTop - headerHeight - 20

        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        })
      }
    })
  })
}

document.addEventListener("DOMContentLoaded", () => {
  initializeSmoothScrolling()
})

window.addEventListener("resize", () => {
  if (window.innerWidth > 768) {
    const mobileMenuBtn = document.getElementById("mobileMenuBtn")
    const mobileNav = document.getElementById("mobileNav")

    if (mobileMenuBtn && mobileNav) {
      mobileMenuBtn.classList.remove("active")
      mobileNav.classList.remove("active")
    }
  }
})

function throttle(func, limit) {
  let inThrottle
  return function () {
    const args = arguments
    
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => (inThrottle = false), limit)
    }
  }
}

window.addEventListener(
  "scroll",
  throttle(() => {
  }, 16),
)

function addLoadingState(element) {
  element.style.opacity = "0.6"
  element.style.pointerEvents = "none"
}

function removeLoadingState(element) {
  element.style.opacity = "1"
  element.style.pointerEvents = "auto"
}

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    const mobileMenuBtn = document.getElementById("mobileMenuBtn")
    const mobileNav = document.getElementById("mobileNav")

    if (mobileMenuBtn && mobileNav && mobileNav.classList.contains("active")) {
      mobileMenuBtn.classList.remove("active")
      mobileNav.classList.remove("active")
    }
  }
})

function manageFocus() {
  const mobileNav = document.getElementById("mobileNav")
  const mobileMenuBtn = document.getElementById("mobileMenuBtn")

  if (mobileNav && mobileMenuBtn) {
    mobileMenuBtn.addEventListener("click", () => {
      if (mobileNav.classList.contains("active")) {
        const firstLink = mobileNav.querySelector(".nav-link-mobile")
        if (firstLink) {
          setTimeout(() => firstLink.focus(), 100)
        }
      }
    })
  }
}

document.addEventListener("DOMContentLoaded", manageFocus)
