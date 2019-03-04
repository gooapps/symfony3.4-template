<?php

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UtilsController extends FOSRestController {

    private function getAppVersion($so){
        $utilsService = $this->get('utils.services');
        $parameters = $utilsService->getParameters();

        $version = $parameters[$so];

        $view = $this->view(['version' => $version], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /** Returns the current version of the application on iOS.
     *
     * Descriptions.
     *
     * @Route("/get-ios-version",methods={"GET"})
     *
     * @SWG\Response(
     *     response=404,
     *     description="Not Found"
     * )
     * @SWG\Tag(name="Utils")
     *
     */
    public function getAppIosVersion(Request $request) {
        return $this->getAppVersion('ios_version');
    }

    /** Returns the current version of the application on Android.
     *
     * Descriptions.
     *
     * @Route("/get-android-version",methods={"GET"})
     *
     * @SWG\Response(
     *     response=404,
     *     description="Not Found"
     * )
     * @SWG\Tag(name="Utils")
     *
     */
    public function getAppAndroidVersion(Request $request) {
        return $this->getAppVersion('android_version');
    }

}
