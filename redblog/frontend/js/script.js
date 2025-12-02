// frontend/js/script.js - VERSIÓN CORREGIDA PARA VOTOS
console.log('RedBlog cargado correctamente');

// Sistema de tema oscuro/claro
function initTheme() {
    const themeToggle = document.getElementById('themeToggle');
    
    if (!themeToggle) {
        console.log('Botón de tema no encontrado');
        return;
    }
    
    // Obtener tema guardado o usar 'light' por defecto
    let currentTheme = localStorage.getItem('theme') || 'light';
    
    // Si no hay tema guardado y el sistema prefiere oscuro
    if (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        currentTheme = 'dark';
    }
    
    // Aplicar tema guardado
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeButton(currentTheme);
    
    // Event listener para el botón de tema
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        console.log('Cambiando tema a:', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeButton(newTheme);
        
        // Mostrar notificación
        showNotification(`Modo ${newTheme === 'dark' ? 'oscuro' : 'claro'} activado`, 'success');
    });
}

// Actualizar icono del botón de tema
function updateThemeButton(theme) {
    const themeToggle = document.getElementById('themeToggle');
    if (theme === 'dark') {
        themeToggle.innerHTML = '☀️';
        themeToggle.title = 'Cambiar a modo claro';
        themeToggle.setAttribute('aria-label', 'Cambiar a modo claro');
    } else {
        themeToggle.innerHTML = '🌙';
        themeToggle.title = 'Cambiar a modo oscuro';
        themeToggle.setAttribute('aria-label', 'Cambiar a modo oscuro');
    }
}

// Sistema de votos para posts
function initPostVoteSystem() {
    const voteButtons = document.querySelectorAll('.post-card .vote-btn');
    
    voteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            // Verificar si el usuario está logueado
            const isLoggedIn = document.querySelector('.user-welcome') !== null;
            
            if(!isLoggedIn) {
                alert('Debes iniciar sesión para votar');
                window.location.href = 'pages/login.php';
                return;
            }
            
            // Deshabilitar botón temporalmente para evitar múltiples clics
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = '...';
            
            const postCard = this.closest('.post-card');
            const postId = postCard.dataset.postId;
            const voteType = this.classList.contains('upvote') ? 'up' : 'down';
            
            try {
                const response = await fetch('../api/vote-post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId,
                        vote_type: voteType
                    })
                });
                
                const result = await response.json();
                
                if(result.success) {
                    // Actualizar contador
                    const voteCount = postCard.querySelector('.vote-count');
                    voteCount.textContent = result.new_score;
                    
                    // Actualizar estilos de botones
                    const upvoteBtn = postCard.querySelector('.upvote');
                    const downvoteBtn = postCard.querySelector('.downvote');
                    
                    // Resetear todos los estilos primero
                    upvoteBtn.classList.remove('active');
                    upvoteBtn.style.color = '';
                    downvoteBtn.classList.remove('active');
                    downvoteBtn.style.color = '';
                    
                    // Aplicar estilo al botón clickeado
                    if(voteType === 'up') {
                        upvoteBtn.classList.add('active');
                        upvoteBtn.style.color = '#059669';
                    } else {
                        downvoteBtn.classList.add('active');
                        downvoteBtn.style.color = '#dc2626';
                    }
                    
                    // Mostrar notificación sutil
                    showNotification('Voto registrado ✓', 'success');
                } else {
                    showNotification(result.message, 'error');
                }
            } catch(error) {
                console.error('Error al votar:', error);
                showNotification('Error al registrar el voto', 'error');
            } finally {
                // Rehabilitar botón
                this.disabled = false;
                this.textContent = originalText;
            }
        });
    });
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444';
    } else {
        notification.style.backgroundColor = '#3b82f6';
    }
    
    // Agregar animación CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.5s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

// Navegación móvil
function initMobileMenu() {
    const mobileMenuButton = document.createElement('button');
    mobileMenuButton.innerHTML = '☰';
    mobileMenuButton.className = 'mobile-menu-toggle';
    mobileMenuButton.style.cssText = `
        display: none;
        background: none;
        border: none;
        color: inherit;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
    `;

    const navContainer = document.querySelector('.nav-container');
    if (navContainer) {
        const navLinks = navContainer.querySelector('.nav-links');
        navContainer.insertBefore(mobileMenuButton, navLinks);
        
        function checkMobile() {
            if (window.innerWidth <= 768) {
                mobileMenuButton.style.display = 'block';
                navLinks.style.display = 'none';
                
                mobileMenuButton.onclick = function() {
                    const isVisible = navLinks.style.display === 'flex';
                    navLinks.style.display = isVisible ? 'none' : 'flex';
                    navLinks.style.flexDirection = 'column';
                    navLinks.style.position = 'absolute';
                    navLinks.style.top = '100%';
                    navLinks.style.left = '0';
                    navLinks.style.right = '0';
                    navLinks.style.background = 'var(--nav-bg)';
                    navLinks.style.padding = '1rem';
                    navLinks.style.gap = '1rem';
                };
            } else {
                mobileMenuButton.style.display = 'none';
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'row';
                navLinks.style.position = 'static';
                navLinks.style.background = 'none';
                navLinks.style.padding = '0';
            }
        }

        checkMobile();
        window.addEventListener('resize', checkMobile);
    }
}

// Inicializar todo cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando RedBlog...');
    
    // Inicializar tema
    initTheme();
    
    // Inicializar sistema de votos para posts
    initPostVoteSystem();
    
    // Inicializar menú móvil
    initMobileMenu();
    
    // Efectos hover para tarjetas
    const postCards = document.querySelectorAll('.post-card');
    postCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
    
    console.log('RedBlog inicializado correctamente');
});

// Manejar errores de carga
window.addEventListener('error', function(e) {
    console.error('Error cargando la página:', e.error);
});

// Función global para votar posts (usada en post-detail.php)
window.votePost = async function(button) {
    const isLoggedIn = document.querySelector('.user-welcome') !== null;
    
    if(!isLoggedIn) {
        alert('Debes iniciar sesión para votar');
        window.location.href = 'pages/login.php';
        return;
    }
    
    // Deshabilitar botón temporalmente
    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = '...';
    
    const postCard = button.closest('.post-card');
    const postId = postCard.dataset.postId;
    const voteType = button.dataset.vote;
    
    try {
        const response = await fetch('../api/vote-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                vote_type: voteType
            })
        });
        
        const result = await response.json();
        
        if(result.success) {
            // Actualizar contador
            const voteCount = document.getElementById('post-vote-' + postId);
            if (voteCount) {
                voteCount.textContent = result.new_score;
            } else {
                const generalVoteCount = postCard.querySelector('.vote-count');
                if (generalVoteCount) {
                    generalVoteCount.textContent = result.new_score;
                }
            }
            
            // Actualizar estilos de botones
            const upvoteBtn = postCard.querySelector('.upvote');
            const downvoteBtn = postCard.querySelector('.downvote');
            
            // Resetear estilos primero
            upvoteBtn.classList.remove('active');
            upvoteBtn.style.color = '';
            downvoteBtn.classList.remove('active');
            downvoteBtn.style.color = '';
            
            // Aplicar estilo al botón clickeado
            if(voteType === 'up') {
                upvoteBtn.classList.add('active');
                upvoteBtn.style.color = '#059669';
            } else {
                downvoteBtn.classList.add('active');
                downvoteBtn.style.color = '#dc2626';
            }
            
            // Mostrar notificación
            showNotification('Voto registrado ✓', 'success');
        } else {
            showNotification(result.message, 'error');
        }
    } catch(error) {
        console.error('Error al votar:', error);
        showNotification('Error al registrar el voto', 'error');
    } finally {
        // Rehabilitar botón
        button.disabled = false;
        button.textContent = originalText;
    }
};