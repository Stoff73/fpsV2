<?php
if ($_POST) {
  // Sanitize input data
  $name = htmlspecialchars(trim($_POST['name']));
  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $message = htmlspecialchars(trim($_POST['message']));

  // Validate required fields
  if (empty($name) || empty($email) || empty($message)) {
    $error_message = "Please fill in all required fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Please enter a valid email address.";
  } else {
    // Email settings (send to yourself)
    $to = "c.jones@csjones.co";
    $subject = "New Workflow Signup - " . $name;

    $email_body = "You have received a new signup from your Workflow landing page.\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n\n";
    $email_body .= "What worries them about integrating AI:\n";
    $email_body .= $message . "\n\n";
    $email_body .= "---\n";
    $email_body .= "Submitted on: " . date('F j, Y \a\t g:i A') . "\n";

    $headers = "From: noreply@csjones.co\n";
    $headers .= "Reply-To: " . $email;

    // Send email
    $mail_sent = mail($to, $subject, $email_body, $headers);

    // ✅ SEND TO GOOGLE SHEET VIA APPS SCRIPT
    $script_url = 'https://script.google.com/macros/s/AKfycbyTlz-LTjKnbV7byjL0pxMKb_LeDuQcPZymK5YzUnzvoxo8UkSM7gLjH3sLfvRjynGv/exec';

    $postData = http_build_query([
      'name' => $name,
      'email' => $email,
      'message' => $message
    ]);

    $context = stream_context_create([
      'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => $postData
      ]
    ]);

    $response = file_get_contents($script_url, false, $context);

    if ($response !== false) {
      $result = json_decode($response, true);
      if (isset($result['result']) && $result['result'] === 'success') {
        $success_message = "Form submitted successfully!";
        $show_modal = true; // Flag to show modal
      } else {
        $error_message = "Something went wrong with the spreadsheet submission.";
      }
    } else {
      $error_message = "Could not connect to Google Apps Script.";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Workflow – Immerse in the AI Era</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html {
      scroll-behavior: smooth;
      font-family: 'Manrope', sans-serif;
    }

    body {
      overflow-x: hidden;
    }

    .parallax-section {
      transform: translateZ(0);
      will-change: transform;
    }

    .fade-in-up {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.9s ease-out, transform 0.9s ease-out;
    }

    .fade-in-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }

    .no-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .bg-noise {
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.1'/%3E%3C/svg%3E");
    }

    .glow-input:focus {
      box-shadow: 0 0 0 2px #a78bfa, 0 0 10px 2px #7c3aed;
      border-color: #a78bfa;
    }

    .glow-btn {
      box-shadow: 0 0 15px 1px rgba(139, 92, 246, 0.22);
    }

    .glow-btn:hover {
      box-shadow: 0 0 26px 5px #a78bfa;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: white;
      margin: 15% auto;
      padding: 30px;
      border-radius: 10px;
      width: 400px;
      max-width: 90%;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .success-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 20px;
      background-color: #28a745;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .checkmark {
      color: white;
      font-size: 30px;
      font-weight: bold;
    }

    .modal h2 {
      color: #333;
      margin: 0 0 10px;
      font-size: 24px;
    }

    .modal p {
      color: #666;
      margin: 0 0 25px;
      font-size: 16px;
      line-height: 1.5;
    }

    .close-btn {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .close-btn:hover {
      background-color: #218838;
    }

    .modal-header {
      position: relative;
    }

    .close-x {
      position: absolute;
      right: -10px;
      top: -10px;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .close-x:hover {
      color: #333;
    }
  </style>
</head>

<body class="bg-black text-white min-h-screen flex flex-col no-scrollbar">

  <!-- Animated Background -->
  <div aria-hidden="true" class="fixed inset-0 z-0 opacity-50 pointer-events-none">
    <div class="absolute inset-0 bg-noise"></div>
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[120vw] h-[70vh] bg-gradient-radial from-cyan-500/20 via-fuchsia-400/10 to-transparent opacity-70 blur-3xl"></div>
    <div class="absolute top-1/3 left-1/4 w-[30vw] h-[40vh] bg-gradient-to-br from-violet-600/20 to-transparent rounded-full blur-2xl"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[40vw] h-[30vh] bg-gradient-to-tr from-cyan-500/20 to-transparent rounded-full blur-2xl"></div>
  </div>

  <!-- Navigation -->
  <nav class="fixed top-0 inset-x-0 flex items-center justify-between px-6 md:px-12 py-6 z-50 backdrop-blur-lg bg-black/20 border-b border-white/5">
    <div class="flex items-center space-x-3">
      <div class="w-9 h-9 bg-gradient-to-tr from-cyan-400 via-fuchsia-500 to-violet-500 rounded-full flex items-center justify-center shadow-lg">
        <span class="font-bold text-lg tracking-wider">W</span>
      </div>
      <span class="font-extralight text-xl tracking-widest uppercase">Workflow</span>
    </div>
    <div class="flex items-center space-x-6">
      <div class="hidden md:flex space-x-8">
        <a href="#about" class="text-sm font-light text-white/80 hover:text-white transition">About</a>
        <a href="#features" class="text-sm font-light text-white/80 hover:text-white transition">Features</a>
        <a href="#early-access" class="text-sm font-light text-white/80 hover:text-white transition">Early Access</a>
        <a href="/projects/coding_projects_gallery.html" class="text-sm font-light text-white/80 hover:text-white transition">Projects</a>
        <a href="/pilots/" class="text-sm font-light text-white/80 hover:text-white transition">Pilot Demo</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="relative overflow-hidden min-h-screen flex flex-col justify-center items-center px-4 pt-20 pb-16">
    <div class="relative z-10 max-w-6xl mx-auto text-center">
      <h1 class="font-extralight tracking-widest text-[2.5rem] sm:text-6xl md:text-7xl lg:text-8xl uppercase bg-gradient-to-br from-cyan-300 via-fuchsia-400 to-violet-500 bg-clip-text text-transparent mb-8 fade-in-up">
        Empower Your Business with AI
      </h1>
      <p class="text-lg md:text-xl font-light tracking-wide text-cyan-100/90 max-w-2xl mx-auto mb-12 fade-in-up" style="transition-delay:0.2s">
        Seamlessly integrate AI into your workflow. Step-by-step guidance, interactive learning, and real ROI tracking—no tech
        expertise required.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <a href="#early-access" class="inline-block bg-gradient-to-r from-fuchsia-500 to-cyan-400 text-white text-base font-normal tracking-wide px-10 py-4 rounded-full shadow-lg hover:shadow-fuchsia-500/20 hover:scale-[1.03] transition-all duration-300 ease-in-out fade-in-up" style="transition-delay:0.35s">
          Start Your AI Journey
        </a>
      </div>
    </div>
    <div class="flex justify-center mt-24 fade-in-up absolute bottom-8" style="transition-delay:0.5s">
      <a href="#intro" class="animate-bounce p-2">
        <svg class="w-8 h-8 text-cyan-300/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </a>
    </div>
  </header>

  <!-- Storytelling Section 1: Introduction -->
  <section id="intro" class="relative py-32 flex items-center min-h-[60vh] px-6 parallax-section">
    <div class="max-w-3xl mx-auto fade-in-up">
      <h2 class="text-4xl sm:text-5xl font-light tracking-tight mb-8 bg-gradient-to-r from-fuchsia-400 to-cyan-300 bg-clip-text text-transparent">
        The World Is Changing
      </h2>
      <p class="text-cyan-100/90 text-xl font-light leading-relaxed">
        Artificial Intelligence isn't just a headline—it's a revolution unfolding in real time. <span class="text-fuchsia-400">Workflow</span> is your partner guiding you through this transformation, shaping your tomorrow.
      </p>
    </div>
  </section>

  <!-- Storytelling Section 2: Discovery -->
  <section class="relative py-32 flex items-center min-h-[60vh] px-6 bg-gradient-to-b from-black via-violet-950/30 to-black parallax-section">
    <div class="max-w-3xl mx-auto fade-in-up">
      <h2 class="text-4xl sm:text-5xl font-light tracking-tight mb-8 bg-gradient-to-r from-cyan-300 to-fuchsia-400 bg-clip-text text-transparent">
        Discover, Don't Just Watch
      </h2>
      <p class="text-cyan-100/90 text-xl font-light leading-relaxed">
        Start today, see where it could take you and your business. The only limit is your imagination.
      </p>
    </div>
  </section>

  <!-- Storytelling Section 3: Participate -->
  <section class="relative py-32 flex items-center min-h-[60vh] px-6 parallax-section">
    <div class="max-w-3xl mx-auto fade-in-up">
      <h2 class="text-4xl sm:text-5xl font-light tracking-tight mb-8 bg-gradient-to-r from-fuchsia-400 to-cyan-300 bg-clip-text text-transparent">
        Be Part of the Pulse
      </h2>
      <p class="text-cyan-100/90 text-xl font-light leading-relaxed">
        Ask questions, vote on topics, and interact with like minded business owners. Workflow isn't just a window—it's an invitation.<br class="hidden sm:inline" />
        <span class="text-fuchsia-400">Will you watch, or will you shape the future?</span>
      </p>
    </div>
  </section>

  <section id="about" class="py-24 bg-black">
    <div class="container mx-auto px-6">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-light tracking-tight mb-4">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Meaningful Integrations</span>
          for modern companies
        </h2>
        <p class="text-gray-300 text-xl max-w-2xl mx-auto font-extralight">
          Everything you need to automate, manage, and scale your business operations with unprecedented efficiency.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-indigo-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-indigo-500/20 transition-all">
            <svg class="h-6 w-6 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Step by step integration</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            We will show you how to go from documenting your workflows, automating what you need and up to AI Agent integration for complex tasks. Including what tools to use and how to use them. Going at your speed as far as you need.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-purple-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-purple-500/20 transition-all">
            <svg class="h-6 w-6 text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Intelligent Automation</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            Streamline workflows with AI-powered automation that learns and adapts to your team's unique processes.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-blue-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-all">
            <svg class="h-6 w-6 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Advanced Analytics</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            Gain actionable insights with customizable dashboards that visualize your most important metrics. Tracking the impact of your integration on your business outcomes.
          </p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-green-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-green-500/20 transition-all">
            <svg class="h-6 w-6 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Enterprise Security</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            Protect sensitive data with bank-level encryption and compliance frameworks that meet global standards.
          </p>
        </div>

        <!-- Feature 5 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-pink-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-pink-500/20 transition-all">
            <svg class="h-6 w-6 text-pink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Team Onboarding</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            AI should support your team and staff not replace them. We want you and the rest of your company to feel good about implementing AI. Using these tools to make your staff more productive, working on more meaningful jobs. We will train everyone, along the way in what they need to be able to utilise the new integrations as well as possible.
          </p>
        </div>

        <!-- Feature 6 -->
        <div class="bg-gradient-to-br from-gray-900 to-black p-8 rounded-xl border border-gray-800 hover:border-indigo-500/30 transition-all group">
          <div class="bg-amber-500/10 rounded-lg w-12 h-12 flex items-center justify-center mb-6 group-hover:bg-amber-500/20 transition-all">
            <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-light mb-3">Scalable Infrastructure</h3>
          <p class="text-gray-400 font-extralight leading-relaxed">
            Grow with confidence, all our solutions can be scaled according to your needs, from basic automation to enterprise solutions..
          </p>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="mt-16 text-center">
        <button class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-light rounded-md px-8 py-3 hover:opacity-90 transition-all">
          <a href="#early-access">
            Be an early adopter
          </a>
        </button>
      </div>
    </div>
  </section>

  <!-- Features: Why Aura? -->
  <section id="features" class="bg-gradient-to-b from-black to-violet-950/50 py-32">
    <div class="max-w-6xl mx-auto px-6">
      <div class="mb-20 text-center fade-in-up">
        <h3 class="text-3xl sm:text-4xl font-light bg-gradient-to-br from-cyan-300 via-fuchsia-400 to-violet-500 bg-clip-text text-transparent mb-4 uppercase tracking-widest">Why Workflow?</h3>
        <p class="text-cyan-100/90 text-xl font-light max-w-2xl mx-auto">Our tailor made solutions, specific for your business and outcomes, enable your business to seamlessly integrate AI into your workflow, without interruption, jargon or the need for technical knowledge.</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-16 md:gap-10">
        <div class="flex flex-col items-center fade-in-up group" style="transition-delay:0.05s">
          <div class="w-20 h-20 bg-gradient-to-br from-fuchsia-400 to-cyan-400 rounded-full flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h4 class="font-light uppercase tracking-widest text-xl mb-4 text-white">Real world problems</h4>
          <p class="text-cyan-100/80 text-center font-light text-base leading-relaxed">We make sure your business (and you) will get a material benefit before anything is implemented. There are no long term commitments, if something does not work, take it out</p>
        </div>
        <div class="flex flex-col items-center fade-in-up group" style="transition-delay:0.15s">
          <div class="w-20 h-20 bg-gradient-to-br from-cyan-400 to-fuchsia-400 rounded-full flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 12l2 2 4-4" />
            </svg>
          </div>
          <h4 class="font-light uppercase tracking-widest text-xl mb-4 text-white">How can we help</h4>
          <p class="text-cyan-100/80 text-center font-light text-base leading-relaxed">Automate boring admin tasks, invoicing, customer follow up and chasing, lead generation, marketing. AI client appointment booking, help desk, and FAQ, to name a few. the only limitation is your imagination.</p>
        </div>
        <div class="flex flex-col items-center fade-in-up group" style="transition-delay:0.25s">
          <div class="w-20 h-20 bg-gradient-to-br from-violet-500 to-cyan-400 rounded-full flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
              <path d="M16 3.13a4 4 0 010 7.75"></path>
            </svg>
          </div>
          <h4 class="font-light uppercase tracking-widest text-xl mb-4 text-white">Interactive Community</h4>
          <p class="text-cyan-100/80 text-center font-light text-base leading-relaxed">Shape the conversation. Connect, discuss, and imagine together with business owners who have gone through the process, going through the process or just starting to think about it.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section id="early-access" class="py-24 px-6 bg-gradient-to-br from-black via-violet-950/20 to-black">
    <div class="max-w-4xl mx-auto text-center fade-in-up">
      <h3 class="text-3xl sm:text-4xl font-light mb-8 bg-gradient-to-r from-cyan-300 to-fuchsia-400 bg-clip-text text-transparent">
        Ready to join the revolution?
      </h3>
      <p class="text-xl font-light text-cyan-100/90 mb-10 max-w-2xl mx-auto">
        First 15 clients get a free assessment and basic automation integration.
      </p>
    </div>

    <!-- Form section -->
    <section class="relative flex items-center justify-center px-4">
      <div
        class="w-full max-w-2xl bg-indigo-900/30 border border-indigo-800/60 rounded-2xl shadow-2xl p-10 backdrop-blur-xl flex flex-col items-center">
        <h2 class="text-3xl font-bold text-white mb-2 text-center">Sign Up</h2>
        <p class="text-gray-400 mb-8 text-center">No obligation, no spam, no sales calls. You will get an email with a useful list of todos to get started.</p>
        <form id="contactForm" method="POST" class="w-full flex flex-col space-y-5">
          <div class="flex flex-col md:flex-row gap-5">
            <input type="text" name="name" placeholder="Your Name" required
              class="glow-input flex-1 px-4 py-3 rounded-lg border border-gray-700 bg-indigo-950/40 text-gray-100 focus:outline-none focus:border-purple-500 transition" />
            <input type="email" name="email" placeholder="Email" required
              class="glow-input flex-1 px-4 py-3 rounded-lg border border-gray-700 bg-indigo-950/40 text-gray-100 focus:outline-none focus:border-purple-500 transition" />
          </div>
          <textarea name="message" placeholder="What worries you the most about integrating AI?" rows="4" required
            class="glow-input px-4 py-3 rounded-lg border border-gray-700 bg-indigo-950/40 text-gray-100 focus:outline-none focus:border-purple-500 transition"></textarea>
          <button type="submit"
            class="glow-btn mt-2 py-3 w-full rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold text-lg tracking-wide hover:from-purple-700 hover:to-indigo-700 transition">
            Sign Up
          </button>
        </form>
        <div class="mt-7 text-gray-400 text-sm text-center">Or email us at <a class="text-purple-400 hover:underline"
            href="mailto:chris@workflow.co">chris@workflow.co</a></div>
      </div>
    </section>
  </section>

  <!-- Success Modal -->
  <div id="successModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-gray-900 border border-purple-500 rounded-lg p-8 max-w-md w-full mx-4">
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
          <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">Success!</h3>
        <p class="text-gray-300 mb-6">Thank you for signing up! We'll be in touch soon.</p>
        <button onclick="document.getElementById('successModal').style.display='none'"
          class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold hover:from-purple-700 hover:to-indigo-700 transition">
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-black/80 border-t border-white/5 py-12 px-6">
    <div class="max-w-6xl mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-center mb-10">
        <div class="flex items-center space-x-3 mb-6 md:mb-0">
          <div class="w-8 h-8 bg-gradient-to-tr from-cyan-400 via-fuchsia-500 to-violet-500 rounded-full flex items-center justify-center shadow-lg">
            <span class="font-bold text-sm tracking-wider">W</span>
          </div>
          <span class="font-extralight text-lg tracking-widest uppercase">Workflow</span>
        </div>
        <div class="flex space-x-8">
          <a href="#" class="text-cyan-100/70 hover:text-cyan-100 text-sm font-light transition-colors">Privacy</a>
          <a href="#" class="text-cyan-100/70 hover:text-cyan-100 text-sm font-light transition-colors">Terms</a>
          <a href="#" class="text-cyan-100/70 hover:text-cyan-100 text-sm font-light transition-colors">Contact</a>
          <a href="#" class="text-cyan-100/70 hover:text-cyan-100 text-sm font-light transition-colors">About</a>
        </div>
      </div>
      <div class="text-cyan-100/50 text-sm font-light text-center md:text-left border-t border-white/5 pt-6">
        &copy; 2026 Workflow — All Rights Reserved. Shaping the future of AI storytelling for everyone.
      </div>
    </div>
  </footer>

  <script>
    /* =============================================================
   Site-wide JavaScript
   – Ajax form submit + inline success / error banner
   – Smooth-scroll links
   – Fade-in-on-scroll
   – Parallax background sections
   ============================================================= */
    (function() {

      /* -----------------------------------------------------------
         Run the rest after the DOM is ready
         ----------------------------------------------------------- */
      function ready(fn) {
        if (document.readyState !== 'loading') {
          fn();
        } else {
          document.addEventListener('DOMContentLoaded', fn);
        }
      }

      ready(function() {

        /* ---------------------------------------------------------
           1. Ajax submit with inline feedback
           --------------------------------------------------------- */
        var form = document.getElementById('contactForm'); /* ← change if your form has another ID */
        if (form) {

          /* prevent any built-in “onsubmit” from reloading */
          form.addEventListener('submit', function(e) {
            e.preventDefault();
          });

          /* main handler */
          form.addEventListener('submit', ajaxSubmit, false);

          function ajaxSubmit(evt) {
            /* stop every kind of fallback reload */
            evt.preventDefault();
            evt.stopImmediatePropagation();

            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
              submitBtn.disabled = true;
            }

            var fd = new FormData(form);
            var actionUrl = form.getAttribute('action') || window.location.href;

            fetch(actionUrl, {
                method: 'POST',
                body: fd,
                headers: {
                  'Accept': 'application/json'
                }
              })
              .then(function(res) {
                if (!res.ok) {
                  throw new Error('HTTP ' + res.status);
                }
                return res.text(); /* you can switch to res.json() */
              })
              .then(function() {
                /* SUCCESS path */
                showBanner(
                  'green',
                  '<strong>Success!</strong> Thank you for signing up. We’ll be in touch soon.'
                );
                form.reset();
              })
              .catch(function() {
                /* ERROR path */
                showBanner(
                  'red',
                  'Sorry, something went wrong. Please try again.'
                );
              })
              .then(function() {
                /* always */
                if (submitBtn) {
                  submitBtn.disabled = false;
                }
              });
          }

          /* helper: insert / replace banner */
          function showBanner(color, innerHtml) {
            var old = form.querySelector('.inline-banner');
            if (old) {
              old.parentNode.removeChild(old);
            }

            var banner = document.createElement('div');
            banner.className = 'inline-banner my-4 rounded-lg bg-' + color +
              '-100 text-' + color + '-800 p-4 text-center';
            banner.innerHTML = innerHtml;
            banner.style.opacity = '0';
            form.insertBefore(banner, form.firstChild);

            /* quick fade-in */
            setTimeout(function() {
              banner.style.transition = 'opacity .3s';
              banner.style.opacity = '1';
            }, 17);
          }
        }

        /* ---------------------------------------------------------
           2. Smooth scrolling for internal anchor links
           --------------------------------------------------------- */
        var anchors = document.querySelectorAll('a[href^="#"]');
        for (var i = 0; i < anchors.length; i++) {
          anchors[i].addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            var targetElement = document.querySelector(targetId);
            if (!targetElement) {
              return;
            }
            e.preventDefault();
            window.scrollTo({
              top: targetElement.offsetTop - 80,
              /* adjust for fixed nav */
              behavior: 'smooth'
            });
          }, false);
        }

        /* ---------------------------------------------------------
           3. Fade-in animation on scroll
           --------------------------------------------------------- */
        var fadeEls = document.querySelectorAll('.fade-in-up');
        var observer = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
            }
          });
        }, {
          threshold: 0.1,
          rootMargin: '0px 0px -100px 0px'
        });
        fadeEls.forEach(function(el) {
          observer.observe(el);
        });

        /* ---------------------------------------------------------
           4. Parallax background sections
           --------------------------------------------------------- */
        var parallaxSections = document.querySelectorAll('.parallax-section');

        function updateParallax() {
          for (var i = 0; i < parallaxSections.length; i++) {
            var section = parallaxSections[i];
            var scrollPos = window.pageYOffset;
            var sectionTop = section.offsetTop;
            var sectionHeight = section.offsetHeight;

            if (scrollPos + window.innerHeight > sectionTop &&
              scrollPos < sectionTop + sectionHeight) {
              var distance = (scrollPos - sectionTop) * 0.05; /* 5 % speed */
              section.style.transform = 'translateY(' + distance + 'px)';
            }
          }
        }

        window.addEventListener('scroll', updateParallax, {
          passive: true
        });
        window.addEventListener('resize', updateParallax);
        updateParallax(); /* initial run */
      });
    })();
  </script>



</body>

</html>