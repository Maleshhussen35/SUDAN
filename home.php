<?php include 'header.php'; ?>

<main class="home-page">
    <style>
        /* Hero Section - Enhanced with Parallax */
        .hero {
            height: 100vh;
            min-height: 800px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 0 2rem;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(88, 28, 135, 0.85) 100%),
                        url('assets/images/hero-bg.jpg') center/cover no-repeat;
            z-index: -1;
            transform: translateZ(-1px) scale(1.1);
        }
        
        .hero-content {
            max-width: 1440px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            z-index: 2;
        }
        
        .hero-text {
            max-width: 600px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 1s ease-out 0.3s forwards;
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            background: linear-gradient(45deg, #7C3AED, #A855F7, #EC4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.05em;
        }
        
        .hero-subtitle {
            color: var(--text-light);
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .hero-cta {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            background: linear-gradient(45deg, #7C3AED, #A855F7);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            margin-top: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            font-weight: 600;
        }
        
        .hero-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .hero-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }
        
        .hero-cta:hover::before {
            left: 100%;
        }
        
        /* Featured Gallery Section - Opposite Layout */
        .featured-gallery {
            position: relative;
            padding: 8rem 2rem;
            background: var(--primary-dark);
            clip-path: polygon(0 5%, 100% 0, 100% 95%, 0 100%);
            margin-top: -5%;
            z-index: 1;
        }
        
        .gallery-container {
            max-width: 1440px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease-out 0.4s forwards;
        }
        
        .section-title {
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            color: var(--text);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #7C3AED, #A855F7);
            border-radius: 3px;
        }
        
        .section-subtitle {
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            aspect-ratio: 4/5;
            transform: scale(0.95);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            animation: galleryItemAppear 0.8s ease-out forwards;
            animation-delay: calc(var(--item-index) * 0.1s + 0.6s);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 50%);
            z-index: 1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1);
            z-index: 2;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-item:hover::before {
            opacity: 1;
        }
        
        .item-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 2rem;
            z-index: 2;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.5s ease;
        }
        
        .gallery-item:hover .item-info {
            transform: translateY(0);
            opacity: 1;
        }
        
        .item-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .item-meta {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes galleryItemAppear {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(0.95);
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .hero {
                min-height: 600px;
                text-align: center;
            }
            
            .hero-text {
                max-width: 100%;
            }
            
            .featured-gallery {
                clip-path: polygon(0 3%, 100% 0, 100% 97%, 0 100%);
                padding: 6rem 1.5rem;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Crafting Unforgettable Parties</h1>
                <p class="hero-subtitle">
                    Step into the ultimate celebration experience with our expert event planning. We create moments of joy, excitement, and memories that last forever.
                </p>
                <a href="events.php" class="hero-cta">
                    Explore Our Events
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor" width="20">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

  

<?php include 'footer.php'; ?>