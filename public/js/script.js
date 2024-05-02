// Définition de la carte de bienvenue
const welcomeCard = document.getElementById('welcome-card');

// Animation de la carte de bienvenue
welcomeCard.style.opacity = '0';
welcomeCard.style.transform = 'translateY(-50px)';
welcomeCard.style.transition = 'opacity 1s, transform 1s';

// Délai avant d'appliquer l'animation
setTimeout(() => {
    welcomeCard.style.opacity = '1';
    welcomeCard.style.transform = 'translateY(0)';
}, 500);

