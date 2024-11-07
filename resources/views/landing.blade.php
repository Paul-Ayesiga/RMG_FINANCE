<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMG Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @keyframes neon-pulse {
            0%, 100% { text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #fff, 0 0 20px #00ffff, 0 0 35px #00ffff, 0 0 40px #00ffff, 0 0 50px #00ffff, 0 0 75px #00ffff; }
            50% { text-shadow: 0 0 2px #fff, 0 0 5px #fff, 0 0 7px #fff, 0 0 10px #00ffff, 0 0 17px #00ffff, 0 0 20px #00ffff, 0 0 25px #00ffff, 0 0 37px #00ffff; }
        }

        .neon-text {
            animation: neon-pulse 1.5s infinite alternate;
        }

        .bg-animated {
            background: linear-gradient(-45deg, #000000, #1a1a1a, #000033, #003366);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .nav-link {
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #00ffff;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::before {
            transform: translateX(0);
        }

        .nav-link.active {
            color: #2563eb;
            font-weight: bold;
        }

        .nav-link.active::before {
            transform: translateX(0);
        }

        .mobile-menu {
            display: none;
        }

        /* Added animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .scale-in {
            transform: scale(0.95);
            opacity: 0;
            transition: transform 0.5s ease-out, opacity 0.5s ease-out;
        }

        .scale-in.visible {
            transform: scale(1);
            opacity: 1;
        }

        .slide-in-left {
            transform: translateX(-100px);
            opacity: 0;
            transition: transform 0.6s ease-out, opacity 0.6s ease-out;
        }

        .slide-in-left.visible {
            transform: translateX(0);
            opacity: 1;
        }

        .slide-in-right {
            transform: translateX(100px);
            opacity: 0;
            transition: transform 0.6s ease-out, opacity 0.6s ease-out;
        }

        .slide-in-right.visible {
            transform: translateX(0);
            opacity: 1;
        }

        @media (max-width: 768px) {
            .desktop-menu {
                display: none;
            }

            .mobile-menu {
                display: block;
                position: fixed;
                top: 0;
                right: -100%;
                width: 70%;
                height: 100vh;
                background: white;
                padding: 80px 20px 20px;
                transition: right 0.3s ease;
                z-index: 40;
            }

            .mobile-menu.active {
                right: 0;
            }

            .mobile-menu .nav-link {
                display: block;
                padding: 15px 0;
                font-size: 1.1rem;
            }

            .menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 30;
            }

            .menu-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="#" class="text-2xl font-bold text-blue-600 animate__animated animate__fadeIn">RMG FINANCE</a>
                
                <!-- Desktop Menu -->
                <div class="desktop-menu space-x-8">
                    <a href="#home" class="nav-link text-gray-700 hover:text-blue-600 animate__animated animate__fadeInDown">Home</a>
                    <a href="#services" class="nav-link text-gray-700 hover:text-blue-600 animate__animated animate__fadeInDown" style="animation-delay: 0.1s">Services</a>
                    <a href="#about" class="nav-link text-gray-700 hover:text-blue-600 animate__animated animate__fadeInDown" style="animation-delay: 0.2s">About</a>
                    <a href="#products" class="nav-link text-gray-700 hover:text-blue-600 animate__animated animate__fadeInDown" style="animation-delay: 0.3s">Products</a>
                    <a href="#contact" class="nav-link text-gray-700 hover:text-blue-600 animate__animated animate__fadeInDown" style="animation-delay: 0.4s">Contact</a>
                    <a href="/login" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 animate__animated animate__fadeInDown" style="animation-delay: 0.5s">Login</a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <div class="text-2xl font-bold text-blue-600 text-center py-4">RMG FINANCE</div>
            <a href="#home" class="nav-link text-gray-700">Home</a>
            <a href="#services" class="nav-link text-gray-700">Services</a>
            <a href="#about" class="nav-link text-gray-700">About</a>
            <a href="#products" class="nav-link text-gray-700">Products</a>
            <a href="#contact" class="nav-link text-gray-700">Contact</a>
            <a href="/login" class="block mt-4 bg-blue-600 text-white px-6 py-2 rounded-full text-center">Login</a>
        </div>
        <div class="menu-overlay"></div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-24 pb-12 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 text-white slide-in-left">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6">Empowering Communities Through Microfinance</h1>
                    <p class="text-xl mb-8">RMG Finance provides accessible financial solutions to help small businesses and individuals achieve their dreams.</p>
                    <a href="#contact" class="bg-white text-blue-600 px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition animate__animated animate__pulse animate__infinite">Get Started</a>
                </div>
                <div class="md:w-1/2 mt-8 md:mt-0 slide-in-right">
                    <img src="https://placehold.co/600x400" alt="Microfinance" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 fade-in">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <i class="fas fa-hand-holding-usd text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-4">Microloans</h3>
                    <p>Small business loans with flexible terms and competitive interest rates.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <i class="fas fa-piggy-bank text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-4">Savings Accounts</h3>
                    <p>Secure savings accounts with attractive interest rates and easy access.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <i class="fas fa-chalkboard-teacher text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-4">Financial Education</h3>
                    <p>Free financial literacy programs and business development training.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0 slide-in-left">
                    <img src="https://placehold.co/600x400" alt="About Us" class="rounded-lg shadow-xl">
                </div>
                <div class="md:w-1/2 md:pl-12 slide-in-right">
                    <h2 class="text-3xl font-bold mb-6">About RMG Finance</h2>
                    <p class="mb-4">RMG Finance is dedicated to promoting financial inclusion and economic empowerment in underserved communities. With over 10 years of experience, we've helped thousands of individuals and small businesses achieve their financial goals.</p>
                    <p>Our mission is to provide accessible financial services while fostering sustainable economic growth in the communities we serve.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 fade-in">Our Products</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <h3 class="text-xl font-bold mb-4">Business Loans</h3>
                    <ul class="list-disc list-inside mb-4">
                        <li>Up to $10,000</li>
                        <li>Flexible repayment terms</li>
                        <li>Low interest rates</li>
                        <li>Quick approval process</li>
                    </ul>
                    <a href="#contact" class="text-blue-600 font-bold hover:underline">Learn More →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <h3 class="text-xl font-bold mb-4">Group Loans</h3>
                    <ul class="list-disc list-inside mb-4">
                        <li>Community-based lending</li>
                        <li>Shared responsibility</li>
                        <li>Group support system</li>
                        <li>Weekly repayments</li>
                    </ul>
                    <a href="#contact" class="text-blue-600 font-bold hover:underline">Learn More →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg scale-in">
                    <h3 class="text-xl font-bold mb-4">Savings Products</h3>
                    <ul class="list-disc list-inside mb-4">
                        <li>High-yield savings accounts</li>
                        <li>Fixed deposits</li>
                        <li>Goal-based savings</li>
                        <li>Mobile banking access</li>
                    </ul>
                    <a href="#contact" class="text-blue-600 font-bold hover:underline">Learn More →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 fade-in">Contact Us</h2>
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8 scale-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xl font-bold mb-4">Get in Touch</h3>
                            <p class="mb-4">Have questions? We're here to help!</p>
                            <div class="space-y-4">
                                <p><i class="fas fa-map-marker-alt text-blue-600 mr-2"></i> 123 Finance Street, City</p>
                                <p><i class="fas fa-phone text-blue-600 mr-2"></i> (123) 456-7890</p>
                                <p><i class="fas fa-envelope text-blue-600 mr-2"></i> info@rmgfinance.com</p>
                            </div>
                        </div>
                        <div>
                            <form>
                                <div class="mb-4">
                                    <input type="text" placeholder="Your Name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-600">
                                </div>
                                <div class="mb-4">
                                    <input type="email" placeholder="Your Email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-600">
                                </div>
                                <div class="mb-4">
                                    <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-600"></textarea>
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition animate__animated animate__pulse animate__infinite">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="fade-in">
                    <h4 class="text-xl font-bold mb-4">RMG Finance</h4>
                    <p>Empowering communities through accessible financial services.</p>
                </div>
                <div class="fade-in">
                    <h4 class="text-xl font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="hover:text-blue-400">Home</a></li>
                        <li><a href="#services" class="hover:text-blue-400">Services</a></li>
                        <li><a href="#about" class="hover:text-blue-400">About</a></li>
                        <li><a href="#contact" class="hover:text-blue-400">Contact</a></li>
                    </ul>
                </div>
                <div class="fade-in">
                    <h4 class="text-xl font-bold mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li>Microloans</li>
                        <li>Business Loans</li>
                        <li>Savings Accounts</li>
                        <li>Financial Education</li>
                    </ul>
                </div>
                <div class="fade-in">
                    <h4 class="text-xl font-bold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-blue-400"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-blue-400"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="hover:text-blue-400"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p>&copy; 2024 RMG Finance. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuOverlay = document.querySelector('.menu-overlay');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });

        menuOverlay.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
        });

        // Smooth scrolling and active section highlighting
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.nav-link');

        function setActiveLink() {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - sectionHeight/3)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    mobileMenu.classList.remove('active');
                    menuOverlay.classList.remove('active');
                }
            });
        });

        // Update active link on scroll
        window.addEventListener('scroll', setActiveLink);
        window.addEventListener('load', setActiveLink);

        // Intersection Observer for animations
        const animatedElements = document.querySelectorAll('.fade-in, .scale-in, .slide-in-left, .slide-in-right');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        animatedElements.forEach(element => {
            observer.observe(element);
        });
    </script>
</body>
</html>
