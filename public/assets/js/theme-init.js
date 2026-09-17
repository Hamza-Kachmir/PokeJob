(function() {
    try {
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('dark-theme', 'dark');
        }
    } catch (error) {
        return;
    }
})();
