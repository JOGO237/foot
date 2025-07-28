// Mode sombre
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;
    
    // Vérifier la préférence sauvegardée ou la préférence système
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        htmlElement.classList.add('dark');
        updateDarkModeIcon(true);
    }
    
    // Toggle du mode sombre
    darkModeToggle.addEventListener('click', function() {
        htmlElement.classList.toggle('dark');
        const isDark = htmlElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateDarkModeIcon(isDark);
    });
    
    function updateDarkModeIcon(isDark) {
        const icon = darkModeToggle.querySelector('i');
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Menu mobile
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Sélecteur de langue
    const languageToggle = document.getElementById('languageToggle');
    const languageMenu = document.getElementById('languageMenu');
    
    if (languageToggle && languageMenu) {
        languageToggle.addEventListener('click', function() {
            languageMenu.classList.toggle('hidden');
        });
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!languageToggle.contains(event.target) && !languageMenu.contains(event.target)) {
                languageMenu.classList.add('hidden');
            }
        });
    }
    
    // Recherche en temps réel
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function(e) {
            performSearch(e.target.value, e.target.dataset.searchType);
        }, 300));
    });
    
    // Filtres par catégorie
    const categoryFilters = document.querySelectorAll('.category-filter');
    categoryFilters.forEach(filter => {
        filter.addEventListener('change', function(e) {
            filterByCategory(e.target.value);
        });
    });
    
    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
    
    // Confirmation de suppression
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            e.preventDefault();
            showConfirmDialog(
                'Êtes-vous sûr de vouloir supprimer cet élément ?',
                function() {
                    window.location.href = e.target.href;
                }
            );
        }
    });
});

// Fonction de debounce pour la recherche
function debounce(func, wait) {
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

// Fonction de recherche AJAX
function performSearch(query, type) {
    if (query.length < 2) {
        resetSearchResults();
        return;
    }
    
    fetch(`ajax/search.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `query=${encodeURIComponent(query)}&type=${type}`
    })
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data, type);
    })
    .catch(error => {
        console.error('Erreur de recherche:', error);
    });
}

// Affichage des résultats de recherche
function displaySearchResults(results, type) {
    const container = document.getElementById(`${type}-results`);
    if (!container) return;
    
    if (results.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Aucun résultat trouvé</p>';
        return;
    }
    
    let html = '';
    results.forEach(item => {
        html += generateResultHTML(item, type);
    });
    
    container.innerHTML = html;
}

// Génération du HTML pour les résultats
function generateResultHTML(item, type) {
    switch(type) {
        case 'clubs':
            return `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 card-hover">
                    <div class="flex items-center space-x-4">
                        ${item.logo ? `<img src="uploads/logos/${item.logo}" alt="${item.nom}" class="w-12 h-12 object-contain">` : '<div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded"></div>'}
                        <div>
                            <h3 class="font-semibold text-lg">${item.nom}</h3>
                            <p class="text-gray-600 dark:text-gray-400">${item.departement_nom}, ${item.region_nom}</p>
                        </div>
                    </div>
                </div>
            `;
        case 'joueurs':
            return `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 card-hover">
                    <div class="flex items-center space-x-4">
                        ${item.photo ? `<img src="uploads/photos/${item.photo}" alt="${item.prenom} ${item.nom}" class="w-12 h-12 object-cover rounded-full">` : '<div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>'}
                        <div>
                            <h3 class="font-semibold text-lg">${item.prenom} ${item.nom}</h3>
                            <p class="text-gray-600 dark:text-gray-400">${item.club_nom} - ${item.poste}</p>
                        </div>
                    </div>
                </div>
            `;
        default:
            return '';
    }
}

// Reset des résultats de recherche
function resetSearchResults() {
    const containers = document.querySelectorAll('[id$="-results"]');
    containers.forEach(container => {
        container.innerHTML = '';
    });
}

// Filtrage par catégorie
function filterByCategory(categoryId) {
    const url = new URL(window.location);
    if (categoryId) {
        url.searchParams.set('categorie', categoryId);
    } else {
        url.searchParams.delete('categorie');
    }
    window.location.href = url.href;
}

// Dialog de confirmation
function showConfirmDialog(message, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Confirmation</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                <div class="flex justify-center space-x-3">
                    <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded border" onclick="this.closest('.fixed').remove()">
                        Annuler
                    </button>
                    <button class="px-4 py-2 bg-red-500 text-white hover:bg-red-600 rounded" onclick="this.closest('.fixed').remove(); (${onConfirm})()">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Notification toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} transform translate-x-full opacity-0 transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);
    
    // Suppression automatique
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Chargement dynamique des statistiques
function loadStats() {
    fetch('ajax/stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-clubs').textContent = data.clubs;
            document.getElementById('total-joueurs').textContent = data.joueurs;
            document.getElementById('total-matchs').textContent = data.matchs;
            document.getElementById('total-saisons').textContent = data.saisons;
        })
        .catch(error => {
            console.error('Erreur de chargement des statistiques:', error);
        });
}

// Actualisation automatique des scores en direct
function refreshLiveScores() {
    fetch('ajax/live_scores.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(match => {
                const matchElement = document.getElementById(`match-${match.id}`);
                if (matchElement) {
                    matchElement.querySelector('.score').textContent = `${match.score_domicile} - ${match.score_exterieur}`;
                    matchElement.querySelector('.status').textContent = match.statut;
                }
            });
        })
        .catch(error => {
            console.error('Erreur de mise à jour des scores:', error);
        });
}

// Démarrer l'actualisation automatique toutes les 30 secondes
if (document.querySelector('.live-scores')) {
    setInterval(refreshLiveScores, 30000);
}
