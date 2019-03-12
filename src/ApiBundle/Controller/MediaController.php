<?php

namespace ApiBundle\Controller;

use AppBundle\Enum\SuggestionStateEnum;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\Sonata\MediaBundle\Entity\Media;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\FOSRestController;

class MediaController extends FOSRestController {


    /**
     * @Route("/upload-image",methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Add Photo"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found"
     * )
     * @SWG\Parameter(
     *     in="body",
     *     name="image",
     *     description="Photo",
     *     required=true,
     *     @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="image", type="string")
     *          )
     *   )
     * @SWG\Tag(name="Media")
     * @Security(name="Bearer")
     */
    public function addImageAction(Request $request)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $image = $request->get('image');
        if (!is_null($image)) {

            $data = base64_decode($image);
            $file = $this->getParameter('files_temp_dir') . uniqid() . '.png';
            $actual = file_put_contents($file, $data);


            $media = new Media();
            $media->setBinaryContent($file);
            $media->setContext('s3');
            $media->setProviderName('sonata.media.provider.image');
            $media->setEnabled(true);
            $em->persist($media);
            $em->flush();

            $array = [];
            $provider = $this->container->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'reference');
            $urlReference = $provider->generatePublicUrl($media, $format);
            $array['reference'] = $urlReference;
            $format = $provider->getFormatName($media, 'small');
            $urlSmall = $provider->generatePublicUrl($media, $format);
            $array['small'] = $urlSmall;
            $format = $provider->getFormatName($media, 'medium');
            $urlMedium = $provider->generatePublicUrl($media, $format);
            $array['medium'] = $urlMedium;
            $format = $provider->getFormatName($media, 'big');
            $urlBig = $provider->generatePublicUrl($media, $format);
            $array['big'] = $urlBig;
            $format = $provider->getFormatName($media, 'square');
            $urlBig = $provider->generatePublicUrl($media, $format);
            $array['square'] = $urlBig;
            $array['id'] = $media->getId();

            //Borramos el archivo temporal
            unlink($file);

            $view = $this->view($array, Response::HTTP_OK);
            return $this->handleView($view);
        }

        $view = $this->view(["error" => "no hay imagen"], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @Route("/upload-media",methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Add File"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found"
     * )
     * @SWG\Parameter(
     *     in="body",
     *     name="image",
     *     description="File",
     *     required=true,
     *     @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="file", type="string")
     *          )
     *   )
     * @SWG\Tag(name="Media")
     * @Security(name="Bearer")
     */
    public function addFileAction(Request $request)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $image = $request->get('file');
        if (!is_null($image)) {

            $data = base64_decode($image);
            $file = $this->getParameter('files_temp_dir') . uniqid();
            $actual = file_put_contents($file, $data);


            $media = new Media();
            $media->setBinaryContent($file);
            $media->setContext('s3');
            $media->setProviderName('sonata.media.provider.file');
            $media->setEnabled(true);
            $em->persist($media);
            $em->flush();

            $array = [];
            $provider = $this->container->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'reference');
            $urlReference = $provider->generatePublicUrl($media, $format);
            $array['reference'] = $urlReference;
            $array['id'] = $media->getId();

            //Borramos el archivo temporal
            unlink($file);

            $view = $this->view($array, Response::HTTP_OK);
            return $this->handleView($view);
        }

        $view = $this->view(["error" => "no hay fichero"], Response::HTTP_OK);
        return $this->handleView($view);
    }


}