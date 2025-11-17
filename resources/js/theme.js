/**
 * Theme Toggle Functionality
 * Handles dark/light mode switching with localStorage persistence
 */

// Initialize theme on page load (before DOM is ready to prevent flash)
(function() {
    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Determine initial theme
    let theme = savedTheme || (prefersDark ? 'dark' : 'light');
    
    // Apply theme to document immediately (before DOM is ready)
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Store initial theme
    if (!savedTheme) {
        localStorage.setItem('theme', theme);
    }
})();

// Theme toggle function (can be called from Alpine.js or other contexts)
window.toggleTheme = function() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
    
    // Dispatch custom event for Alpine.js reactivity
    window.dispatchEvent(new CustomEvent('theme-changed', {
        detail: { theme: isDark ? 'light' : 'dark' }
    }));
    
    return !isDark; // Return new theme state (true = dark, false = light)
};

// Get current theme
window.getTheme = function() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
};

// Listen for theme changes to update Alpine.js components
if (typeof window !== 'undefined' && window.addEventListener) {
    window.addEventListener('theme-changed', function(event) {
        // This event can be listened to by Alpine.js components
        // if needed for reactive updates
    });
}
