
<footer class="site-footer">
    <style>
        :root {
            --footer-bg: #0F172A;
            --footer-text: #F8FAFC;
            --footer-accent: #7C3AED;
        }

        .site-footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            padding: 2rem 1rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: space-between;
            align-items: center;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .social-link {
            color: var(--footer-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s ease;
        }

        .social-link:hover {
            opacity: 0.8;
        }

        .social-link i {
            font-size: 1.2rem;
        }

        /* Chat Bot Styles */
        .chat-bot {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .chat-toggle {
            background: var(--footer-accent);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .chat-toggle:hover {
            transform: scale(1.1);
        }

        .chat-window {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 350px;
            position: absolute;
            bottom: 70px;
            right: 0;
            display: none;
        }

        .chat-header {
            background: var(--footer-accent);
            color: white;
            padding: 1rem;
            border-radius: 1rem 1rem 0 0;
        }

        .chat-body {
            padding: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .chat-message {
            margin: 0.5rem 0;
            padding: 0.8rem;
            border-radius: 1rem;
            background: #f1f5f9;
            color: #0F172A;
        }

        .quick-questions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .quick-question {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background: #e2e8f0;
            transition: background 0.3s ease;
        }

        .quick-question:hover {
            background: #cbd5e1;
        }
    </style>

    <div class="footer-content">
        <div class="copyright">
            © <?= date('Y') ?> CLICK -EVENTS. All rights reserved.
        </div>
        
        <div class="social-links">
            <a href="https://www.instagram.com/Mayom Emmanuel Atem" target="_blank" class="social-link">
                <i class="fab fa-instagram"></i>
                <span>@Mayom Emmanuel Atem </span>
            </a>
            <a href="https://wa.me/254757919189" target="_blank" class="social-link">
                <i class="fab fa-whatsapp"></i>
                <span>+254757919189</span>
            </a>
        </div>
    </div>

    <!-- Chat Bot System -->
    <div class="chat-bot">
        <div class="chat-toggle">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chat-window">
            <div class="chat-header">
                Event Assistant
            </div>
            <div class="chat-body" id="chat-body">
                <div class="chat-message bot-message">
                    Hello! I'm EventBot. How can I help you today?
                </div>
                <div class="quick-questions">
                    <div class="quick-question" data-question="events">
                        What events are coming up?
                    </div>
                    <div class="quick-question" data-question="booking">
                        How do I book an event?
                    </div>
                    <div class="quick-question" data-question="payment">
                        What payment methods do you accept?
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        // Chat Bot Functionality
        const chatToggle = document.querySelector('.chat-toggle');
        const chatWindow = document.querySelector('.chat-window');
        const chatBody = document.getElementById('chat-body');

        const botResponses = {
            events: "We regularly update our Events page with upcoming activities. Check out our Events section for the latest updates!",
            booking: "To book an event: 1) Choose your event 2) Fill the booking form 3) Confirm payment. Need more help?",
            payment: "We accept MPesa, credit cards, and mobile money. All transactions are secure.",
            default: "Thank you for your question! Our team will respond shortly."
        };

        chatToggle.addEventListener('click', () => {
            chatWindow.style.display = chatWindow.style.display === 'block' ? 'none' : 'block';
        });

        document.querySelectorAll('.quick-question').forEach(item => {
            item.addEventListener('click', () => {
                const questionType = item.dataset.question;
                const response = botResponses[questionType] || botResponses.default;
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chat-message bot-message';
                messageDiv.textContent = response;
                
                chatBody.appendChild(messageDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        });
    </script>
</footer>