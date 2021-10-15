const router = require('../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js');
const routerConfig = require('../../../web/js/fos_js_routes.json');
router.setRoutingData(routerConfig);
 
module.exports = router;