
let largeur = window.innerWidth;
let hauteur = window.innerHeight;
const LARGEUR_SMARTPHONE = 767;
let urlLogin = Routing.generate('app_login');
console.log("largeur : " + largeur);
console.log("hauteur : " + hauteur);
let user = document.getElementById('user');
console.log("id : " + user )
// Si l'écran du navigateur est plus petit que 767px (taille smartphone)
if (largeur < LARGEUR_SMARTPHONE) {
    // On regarde si l'user est connectée sinon on redirige vers la page login
    document.addEventListener('DOMContentLoaded', function() {
        let isAuthenticated = $('.js-user-rating').data('isAuthenticated');

        if (!isAuthenticated) {
            window.location.href = urlLogin; 
        }

    });
    
}