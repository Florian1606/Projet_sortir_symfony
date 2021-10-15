import router, { setRoutingData } from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import routerConfig from './fos_js_routes.json';
setRoutingData(routerConfig);
 
export default router;