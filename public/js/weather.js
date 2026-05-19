// Global State
const state = {
    currentUnit: 'C', // 'C' or 'F'
    weatherData: null,
    recentSearches: []
};

// Cache Elements
const el = {
    body: document.body,
    clockTime: document.getElementById('clock-time'),
    unitToggle: document.getElementById('unit-toggle'),
    unitC: document.getElementById('unit-c'),
    unitF: document.getElementById('unit-f'),
    searchInput: document.getElementById('search-input'),
    btnSearch: document.getElementById('btn-search'),
    btnGps: document.getElementById('btn-gps'),
    gpsIcon: document.getElementById('gps-icon'),
    loadingOverlay: document.getElementById('loading-overlay'),
    errorCard: document.getElementById('error-card'),
    errorMsg: document.getElementById('error-msg'),
    weatherDashboard: document.getElementById('weather-dashboard'),
    
    // Dashboard elements
    sourceBadge: document.getElementById('source-badge'),
    cityName: document.getElementById('city-name'),
    countryName: document.getElementById('country-name'),
    weatherTime: document.getElementById('weather-time'),
    weatherIcon: document.getElementById('weather-icon'),
    currentTemp: document.getElementById('current-temp'),
    conditionText: document.getElementById('condition-text'),
    feelsLikeTemp: document.getElementById('feels-like-temp'),
    
    // Metrics
    humidity: document.getElementById('stat-humidity'),
    wind: document.getElementById('stat-wind'),
    uv: document.getElementById('stat-uv'),
    cloud: document.getElementById('stat-cloud'),
    pressure: document.getElementById('stat-pressure'),
    visibility: document.getElementById('stat-visibility'),
    
    // Containers
    forecastContainer: document.getElementById('forecast-container'),
    historyContainer: document.getElementById('history-container'),
    clearHistory: document.getElementById('clear-history')
};

// Live Clock Tick
function startClock() {
    function tick() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        el.clockTime.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
    }
    tick();
    setInterval(tick, 1000);
}

// Initialize Lucide Icons
function initIcons() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// Mapping Condition Texts to dynamic theme classes
function updateTheme(condition) {
    const cleanCondition = condition.toLowerCase();
    
    // Reset theme classes
    el.body.classList.remove('theme-sunny', 'theme-clear', 'theme-cloudy', 'theme-rainy', 'theme-stormy', 'theme-snowy');
    
    if (cleanCondition.includes('sunny')) {
        el.body.classList.add('theme-sunny');
    } else if (cleanCondition.includes('clear')) {
        el.body.classList.add('theme-clear');
    } else if (cleanCondition.includes('storm') || cleanCondition.includes('thunder')) {
        el.body.classList.add('theme-stormy');
    } else if (cleanCondition.includes('snow') || cleanCondition.includes('blizzard') || cleanCondition.includes('frost') || cleanCondition.includes('sleet')) {
        el.body.classList.add('theme-snowy');
    } else if (cleanCondition.includes('rain') || cleanCondition.includes('drizzle') || cleanCondition.includes('shower')) {
        el.body.classList.add('theme-rainy');
    } else if (cleanCondition.includes('cloud') || cleanCondition.includes('overcast') || cleanCondition.includes('mist') || cleanCondition.includes('fog')) {
        el.body.classList.add('theme-cloudy');
    } else {
        // Fallback depending on day/night
        if (state.weatherData && state.weatherData.is_day === 0) {
            el.body.classList.add('theme-clear');
        } else {
            el.body.classList.add('theme-sunny');
        }
    }
}

// Fetch weather details
async function fetchWeather(city) {
    showLoading(true);
    hideError();
    
    try {
        const response = await fetch(`/api/weather?city=${encodeURIComponent(city)}`);
        const data = await response.json();
        
        if (response.ok && data.success) {
            state.weatherData = data;
            renderWeather();
            addToHistory(data.city);
        } else {
            showError(data.message || `We couldn't retrieve weather details for "${city}".`);
        }
    } catch (error) {
        showError('A network error occurred. Please check your connection and try again.');
    } finally {
        showLoading(false);
    }
}

// Fetch weather by GPS coordinates
async function fetchWeatherCoords(lat, lon) {
    showLoading(true);
    hideError();
    
    try {
        const response = await fetch(`/api/weather/coords?lat=${lat}&lon=${lon}`);
        const data = await response.json();
        
        if (response.ok && data.success) {
            state.weatherData = data;
            renderWeather();
            addToHistory(data.city);
        } else {
            showError(data.message || "Failed to retrieve weather for your current location.");
        }
    } catch (error) {
        showError('A network error occurred while resolving coordinates.');
    } finally {
        showLoading(false);
    }
}

// Render data to DOM
function renderWeather() {
    if (!state.weatherData) return;
    
    const w = state.weatherData;
    
    // Badge Source
    el.sourceBadge.textContent = w.source === 'api' ? 'Live API' : 'Demo Mode';
    el.sourceBadge.style.background = w.source === 'api' ? 'rgba(16, 185, 129, 0.25)' : 'rgba(245, 158, 11, 0.25)';
    el.sourceBadge.style.borderColor = w.source === 'api' ? 'rgba(16, 185, 129, 0.4)' : 'rgba(245, 158, 11, 0.4)';
    
    // Location Info
    el.cityName.textContent = w.city;
    el.countryName.textContent = w.country;
    
    // Format datetime
    try {
        const dateObj = new Date(w.date_time);
        if (isNaN(dateObj.getTime())) {
            el.weatherTime.textContent = w.date_time;
        } else {
            const options = { weekday: 'long', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
            el.weatherTime.textContent = dateObj.toLocaleDateString('en-US', options);
        }
    } catch (e) {
        el.weatherTime.textContent = w.date_time;
    }

    // Weather Icon & Temp
    el.weatherIcon.src = w.icon;
    el.weatherIcon.alt = w.condition;
    
    // Temp value depending on unit
    const tempVal = state.currentUnit === 'C' ? Math.round(w.temperature_c) : Math.round(w.temperature_f);
    el.currentTemp.innerHTML = `${tempVal}<span class="current-temp-degree">°</span>`;
    
    // Condition text
    el.conditionText.textContent = w.condition;
    
    // Feels Like
    const feelsLikeVal = state.currentUnit === 'C' ? Math.round(w.feelslike_c) : Math.round(w.feelslike_f);
    el.feelsLikeTemp.textContent = `${feelsLikeVal}°${state.currentUnit}`;
    
    // Detailed Highlights
    el.humidity.textContent = `${w.humidity}%`;
    el.wind.textContent = `${w.wind_kph} km/h`;
    el.uv.textContent = Number(w.uv).toFixed(1);
    el.cloud.textContent = `${w.cloud}%`;
    el.pressure.textContent = `${Math.round(w.pressure_mb)} hPa`;
    el.visibility.textContent = `${w.visibility_km} km`;
    
    // Render 5-Day Forecast
    el.forecastContainer.innerHTML = '';
    w.forecast.forEach(day => {
        const maxTemp = state.currentUnit === 'C' ? Math.round(day.temp_max_c) : Math.round(day.temp_max_f);
        const minTemp = state.currentUnit === 'C' ? Math.round(day.temp_min_c) : Math.round(day.temp_min_f);
        
        // Get day name
        let dayName = 'Day';
        try {
            const dayDate = new Date(day.date);
            dayName = dayDate.toLocaleDateString('en-US', { weekday: 'short' });
        } catch(e) {}
        
        const card = document.createElement('div');
        card.className = 'forecast-day-card';
        card.innerHTML = `
            <div class="forecast-day-name">${dayName}</div>
            <div class="forecast-date">${formatForecastDate(day.date)}</div>
            <img class="forecast-icon" src="${day.icon}" alt="${day.condition}">
            <div class="forecast-condition">${day.condition}</div>
            <div class="forecast-temps">
                <span class="forecast-temp-max">${maxTemp}°</span>
                <span class="forecast-temp-min">${minTemp}°</span>
            </div>
        `;
        el.forecastContainer.appendChild(card);
    });

    // Update Body Theme
    updateTheme(w.condition);
}

// Helper: format forecast date
function formatForecastDate(dateStr) {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
    } catch(e) {
        return dateStr;
    }
}

// Show/Hide Loading Overlay
function showLoading(isLoading) {
    if (isLoading) {
        el.loadingOverlay.classList.add('active');
    } else {
        el.loadingOverlay.classList.remove('active');
    }
}

// Error Handlers
function showError(msg) {
    el.errorMsg.textContent = msg;
    el.errorCard.style.display = 'flex';
    el.weatherDashboard.style.opacity = '0.3';
    el.weatherDashboard.style.pointerEvents = 'none';
}

// Hide Error
function hideError() {
    el.errorCard.style.display = 'none';
    el.weatherDashboard.style.opacity = '1';
    el.weatherDashboard.style.pointerEvents = 'auto';
}

// Recent Searches Cache (Local Storage)
function loadHistory() {
    const saved = localStorage.getItem('aerosky_recent');
    if (saved) {
        try {
            state.recentSearches = JSON.parse(saved);
        } catch (e) {
            state.recentSearches = [];
        }
    }
    renderHistory();
}

function addToHistory(city) {
    if (!city) return;
    const cleanCity = city.trim();
    
    // Remove duplicates
    state.recentSearches = state.recentSearches.filter(c => c.toLowerCase() !== cleanCity.toLowerCase());
    
    // Add to front
    state.recentSearches.unshift(cleanCity);
    
    // Limit to 8 searches
    if (state.recentSearches.length > 8) {
        state.recentSearches.pop();
    }
    
    localStorage.setItem('aerosky_recent', JSON.stringify(state.recentSearches));
    renderHistory();
}

function removeFromHistory(city, event) {
    event.stopPropagation();
    state.recentSearches = state.recentSearches.filter(c => c.toLowerCase() !== city.toLowerCase());
    localStorage.setItem('aerosky_recent', JSON.stringify(state.recentSearches));
    renderHistory();
}

function clearHistory() {
    state.recentSearches = [];
    localStorage.removeItem('aerosky_recent');
    renderHistory();
}

function renderHistory() {
    el.historyContainer.innerHTML = '';
    
    if (state.recentSearches.length === 0) {
        el.historyContainer.innerHTML = '<div class="history-empty">No recent searches yet.</div>';
        return;
    }
    
    state.recentSearches.forEach(city => {
        const item = document.createElement('div');
        item.className = 'history-item';
        item.innerHTML = `
            <span>${city}</span>
            <span class="history-item-remove" title="Remove"><i data-lucide="x" style="width: 12px; height: 12px;"></i></span>
        `;
        
        // Search triggers on click
        item.addEventListener('click', () => {
            el.searchInput.value = city;
            fetchWeather(city);
        });
        
        // Remove button trigger
        const removeBtn = item.querySelector('.history-item-remove');
        removeBtn.addEventListener('click', (e) => removeFromHistory(city, e));
        
        el.historyContainer.appendChild(item);
    });
    
    initIcons();
}

// GPS Geolocation Handler
function handleGps() {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }
    
    el.gpsIcon.setAttribute('data-lucide', 'loader-2');
    el.gpsIcon.style.animation = 'spin 1s infinite linear';
    initIcons();

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            fetchWeatherCoords(lat, lon).then(() => {
                el.gpsIcon.setAttribute('data-lucide', 'map-pin');
                el.gpsIcon.style.animation = 'none';
                initIcons();
            });
        },
        (error) => {
            el.gpsIcon.setAttribute('data-lucide', 'map-pin');
            el.gpsIcon.style.animation = 'none';
            initIcons();
            
            let errorMsg = "Unable to retrieve your location.";
            if (error.code === error.PERMISSION_DENIED) {
                errorMsg = "Location access denied. Please enable location permissions in your browser or search for a city manually.";
            }
            alert(errorMsg);
        },
        { timeout: 8000 }
    );
}

// Setup Event Listeners
function setupEventListeners() {
    // Search button click
    el.btnSearch.addEventListener('click', () => {
        const city = el.searchInput.value.trim();
        if (city) {
            fetchWeather(city);
        }
    });

    // Auto-search on Enter key
    el.searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const city = el.searchInput.value.trim();
            if (city) {
                fetchWeather(city);
            }
        }
    });

    // Preset city pills
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const city = e.target.getAttribute('data-city');
            el.searchInput.value = city;
            fetchWeather(city);
        });
    });

    // GPS Click
    el.btnGps.addEventListener('click', handleGps);

    // Clear history click
    el.clearHistory.addEventListener('click', clearHistory);

    // Unit conversions
    el.unitC.addEventListener('click', () => {
        if (state.currentUnit !== 'C') {
            state.currentUnit = 'C';
            el.unitC.classList.add('active');
            el.unitF.classList.remove('active');
            el.unitToggle.classList.remove('fahrenheit');
            renderWeather();
        }
    });

    el.unitF.addEventListener('click', () => {
        if (state.currentUnit !== 'F') {
            state.currentUnit = 'F';
            el.unitF.classList.add('active');
            el.unitC.classList.remove('active');
            el.unitToggle.classList.add('fahrenheit');
            renderWeather();
        }
    });
}

// App Initialisation
document.addEventListener('DOMContentLoaded', () => {
    startClock();
    loadHistory();
    setupEventListeners();
    
    // Initial Load (Default City is Delhi, India)
    fetchWeather('New Delhi');
});
