<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroSky | Premium Weather Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons (via CDN) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- External Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/weather.css') }}">
</head>
<body class="theme-clear">

    <div class="app-container">
        
        <!-- Header -->
        <header class="glass-card">
            <div class="logo-area">
                <div class="logo-icon">
                    <i data-lucide="cloud-lightning" style="width: 24px; height: 24px; color: #ffffff;"></i>
                </div>
                <div class="logo-text">AeroSky</div>
            </div>
            
            <div class="header-controls">
                <div class="live-clock">
                    <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
                    <span id="clock-time">00:00:00 PM</span>
                </div>
                
                <!-- Unit Switcher -->
                <div class="toggle-container" id="unit-toggle">
                    <div class="toggle-slider"></div>
                    <button class="toggle-btn active" id="unit-c">°C</button>
                    <button class="toggle-btn" id="unit-f">°F</button>
                </div>
            </div>
        </header>

        <!-- Search Bar -->
        <section>
            <div class="search-section">
                <div class="search-bar-wrapper">
                    <i data-lucide="search" class="search-icon" style="width: 20px; height: 20px;"></i>
                    <input type="text" id="search-input" class="search-input" placeholder="Search city (e.g. Bangalore, London...)" autocomplete="off">
                </div>
                <button class="btn-circle" id="btn-search" title="Search weather">
                    <i data-lucide="arrow-right" style="width: 22px; height: 22px;"></i>
                </button>
                <button class="btn-circle" id="btn-gps" title="Use current location">
                    <i data-lucide="map-pin" id="gps-icon" style="width: 22px; height: 22px;"></i>
                </button>
            </div>
        </section>

        <!-- Preset Fast Search Pills -->
        <section class="preset-cities">
            <button class="preset-btn" data-city="Bangalore">Bangalore</button>
            <button class="preset-btn" data-city="Chennai">Chennai</button>
            <button class="preset-btn" data-city="Mumbai">Mumbai</button>
            <button class="preset-btn" data-city="New Delhi">Delhi</button>
            <button class="preset-btn" data-city="London">London</button>
            <button class="preset-btn" data-city="New York">New York</button>
            <button class="preset-btn" data-city="Tokyo">Tokyo</button>
        </section>

        <!-- Error Card -->
        <div class="glass-card error-card" id="error-card">
            <div class="error-icon">
                <i data-lucide="alert-circle" style="width: 32px; height: 32px;"></i>
            </div>
            <div class="error-title">City Not Found</div>
            <div class="error-msg" id="error-msg">We couldn't retrieve weather details for that location. Please check the spelling or try another city.</div>
        </div>

        <!-- Main Dashboard View -->
        <main class="dashboard-grid" id="weather-dashboard">
            
            <!-- Left Column: Current Weather Overview -->
            <article class="glass-card current-card">
                <!-- Loading overlay inside current card -->
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner"></div>
                </div>

                <span class="weather-source-badge" id="source-badge">API</span>
                
                <div class="current-header">
                    <h1 class="city-name" id="city-name">Bangalore</h1>
                    <div class="country-name" id="country-name">India</div>
                    <div class="current-datetime" id="weather-time">Tuesday, 19 May 13:49</div>
                </div>

                <div class="weather-art">
                    <img id="weather-icon" src="//cdn.weatherapi.com/weather/64x64/day/116.png" alt="Weather Condition">
                </div>

                <div class="current-temp-container">
                    <span class="current-temp" id="current-temp">
                        27
                        <span class="current-temp-degree">°</span>
                    </span>
                </div>

                <div class="current-condition" id="condition-text">Partly cloudy</div>
                
                <div class="feels-like">
                    <i data-lucide="thermometer" style="width: 15px; height: 15px;"></i>
                    <span>Feels like <strong id="feels-like-temp">29°</strong></span>
                </div>
            </article>

            <!-- Right Column: Secondary Stats and Forecasts -->
            <div class="right-column">
                
                <!-- Detailed Weather Metrics -->
                <section class="glass-card" style="padding: 1.75rem 2rem;">
                    <h2 class="section-title">
                        <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                        Weather Highlights
                    </h2>
                    
                    <div class="stats-grid">
                        
                        <!-- Humidity -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="droplets" style="width: 24px; height: 24px; color: #38bdf8;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Humidity</span>
                                <span class="stat-value" id="stat-humidity">64%</span>
                            </div>
                        </div>

                        <!-- Wind Speed -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="wind" style="width: 24px; height: 24px; color: #a855f7;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Wind Speed</span>
                                <span class="stat-value" id="stat-wind">12.5 km/h</span>
                            </div>
                        </div>

                        <!-- UV Index -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="sun" style="width: 24px; height: 24px; color: #f59e0b;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">UV Index</span>
                                <span class="stat-value" id="stat-uv">7.0</span>
                            </div>
                        </div>

                        <!-- Cloud Cover -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="cloud" style="width: 24px; height: 24px; color: #cbd5e1;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Cloud Cover</span>
                                <span class="stat-value" id="stat-cloud">40%</span>
                            </div>
                        </div>

                        <!-- Pressure -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="gauge" style="width: 24px; height: 24px; color: #10b981;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Pressure</span>
                                <span class="stat-value" id="stat-pressure">1013 hPa</span>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="glass-card stat-card">
                            <div class="stat-icon-box">
                                <i data-lucide="eye" style="width: 24px; height: 24px; color: #f43f5e;"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Visibility</span>
                                <span class="stat-value" id="stat-visibility">10 km</span>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- 5-Day Forecast -->
                <section class="glass-card forecast-card">
                    <h2 class="section-title">
                        <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
                        5-Day Forecast
                    </h2>
                    
                    <div class="forecast-list" id="forecast-container">
                        <!-- Forecast items injected here via JS -->
                    </div>
                </section>

                <!-- Recent Searches -->
                <section class="glass-card history-card">
                    <div class="history-header">
                        <div class="history-title">
                            <i data-lucide="history" style="width: 18px; height: 18px;"></i>
                            Recent Searches
                        </div>
                        <button class="btn-clear-history" id="clear-history">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            Clear
                        </button>
                    </div>
                    <div class="history-list" id="history-container">
                        <div class="history-empty">No recent searches yet.</div>
                    </div>
                </section>

            </div>
        </main>

        <!-- Footer -->
        <footer>
            <p>&copy; 2026 AeroSky Weather Dashboard. Powered by WeatherAPI. Crafted with elegance.</p>
        </footer>

    </div>

    <!-- External Script File -->
    <script src="{{ asset('js/weather.js') }}"></script>
</body>
</html>
