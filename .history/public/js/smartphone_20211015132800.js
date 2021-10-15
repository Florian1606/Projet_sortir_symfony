
let largeur = window.innerWidth;
let hauteur = window.innerHeight;
const router = require('../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js');
const routerConfig = require('../../../web/js/fos_js_routes.json');
router.setRoutingData(routerConfig);
 
module.exports = router;
//const Routing = require('../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js');
const LARGEUR_SMARTPHONE = 767;
let urlLogin = Routing.generate('app_login');
console.log(urlLogin);
$(GET)
// Si l'écran du navigateur est plus petit que 767px (taille smartphone)
if (largeur < LARGEUR_SMARTPHONE) {
    // On regarde si l'user est connectée sinon on redirige vers la page login
    document.addEventListener('DOMContentLoaded', function() {
        let isAuthenticated = $('.js-user-rating').data('isAuthenticated');
        console.log(isAuthenticated);
        if (!isAuthenticated) {
            window.document.location = urlLogin; 
        }

    });
    
}