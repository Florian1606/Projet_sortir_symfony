<?php
// app/AppKernel.php
use Symfony\Component\HttpKernel\Kernel as Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        );

        // ...
    }

    // ...
}