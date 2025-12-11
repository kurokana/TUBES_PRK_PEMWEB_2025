/**
 * Custom JavaScript untuk SiPaMaLi
 * Kelompok 22
 * 
 * File ini reserved untuk custom JavaScript jika diperlukan
 * Saat ini logic ada di inline script di HTML
 */

// API Configuration
const API_CONFIG = {
    BASE_URL: 'api.php',
    TIMEOUT: 30000,
    MAX_RETRIES: 3
};

// Utility Functions
const Utils = {
    /**
     * Format date to Indonesian format
     */
    formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    },

    /**
     * Truncate text to specified length
     */
    truncate(text, length = 100) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    },

    /**
     * Debounce function calls
     */
    debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_CONFIG, Utils };
}
