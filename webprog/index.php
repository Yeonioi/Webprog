<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendLink - Connect with Neighbors for Local Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white min-h-screen">
    <!-- Hero Section -->
    <header class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-purple-50 z-0"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative z-10">
            <div class="text-center md:text-left md:flex md:items-center md:justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 tracking-tight">
                        <span class="text-blue-600">Friend</span><span class="text-purple-600">Link</span>
                        <br/>
                        <span class="text-gray-700">Connect with neighbors for local services</span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 max-w-3xl">
                        Build meaningful connections in your community. Offer your skills, find trusted help, and support local talent with services ranging from education to home care.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row justify-center md:justify-start gap-4">
                        <a href="home.php" class="px-8 py-3 rounded-full text-white bg-blue-600 hover:bg-blue-700 font-medium shadow-md transition-colors duration-200 text-center">
                            Get Started
                        </a>
                        <a href="search.php" class="px-8 py-3 rounded-full text-blue-600 bg-white border border-blue-200 hover:border-blue-300 font-medium shadow-sm flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Browse Services
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute -top-6 -right-6 w-40 h-40 bg-purple-100 rounded-full opacity-60"></div>
                        <div class="absolute -bottom-10 -left-10 w-56 h-56 bg-blue-100 rounded-full opacity-60"></div>
                        <div class="relative bg-white rounded-2xl shadow-xl overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1521791055366-0d553872125f?w=600" alt="Neighbors connecting" class="w-full h-64 object-cover">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 rounded-full overflow-hidden">
                                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100" alt="Profile" class="h-full w-full object-cover">
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Michael Rodriguez</h3>
                                        <div class="flex items-center mt-1">
                                            <span class="text-yellow-400">★★★★★</span>
                                            <span class="ml-2 text-sm text-gray-600">27 reviews</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h4 class="font-medium text-gray-900">Lawn Care & Landscaping</h4>
                                    <p class="mt-1 text-sm text-gray-600">Professional lawn mowing, edging, and landscaping services.</p>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-blue-600 font-medium">$25/hour</span>
                                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full text-sm font-medium">Available Now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">How FriendLink Works</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">A simple way to connect with skilled neighbors in your community</p>
            </div>
            <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-sm mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">Find & Offer Services</h3>
                    <p class="mt-4 text-gray-600">Browse services or create your own. Add custom categories to match your unique skills.</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-sm mb-6">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">Build Community</h3>
                    <p class="mt-4 text-gray-600">Connect directly with neighbors, build trust, and create lasting relationships.</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-sm mb-6">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">Share & Earn</h3>
                    <p class="mt-4 text-gray-600">Set your own rates, work on your schedule, and support the local economy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-br from-blue-50 to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-12 md:p-12 md:flex md:items-center md:justify-between">
                    <div class="md:w-2/3">
                        <h2 class="text-3xl font-bold text-white">Ready to connect with your neighbors?</h2>
                        <p class="mt-4 text-blue-100 max-w-2xl">Join FriendLink today and start building meaningful connections in your community.</p>
                    </div>
                    <div class="mt-8 md:mt-0">
                        <a href="home.php" class="block px-8 py-3 bg-white text-blue-600 rounded-full font-medium shadow-md hover:bg-blue-50 transition-colors duration-200 text-center">
                            Join FriendLink
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>