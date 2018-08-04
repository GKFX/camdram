<?php

namespace Acts\CamdramBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramBundle\Entity\Performance;
use Acts\CamdramBundle\Form\Type\ShowType;
use Acts\CamdramSecurityBundle\Entity\PendingAccess;
use Acts\CamdramSecurityBundle\Entity\SocietyAccessCE;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShowController
 *
 * Controller for REST actions for shows. Inherits from AbstractRestController.
 *
 * @RouteResource("Show")
 */
class ShowController extends AbstractRestController
{
    protected $class = Show::class;

    protected $type = 'show';

    protected $type_plural = 'shows';

    protected function getRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('ActsCamdramBundle:Show');
    }

    protected function getForm($show = null, $method = 'POST')
    {
        if (is_null($show)) {
            $show = new Show();
            $show->addPerformance(new Performance());
        }

        return $this->createForm(ShowType::class, $show, ['method' => $method]);
    }

    public function cgetAction(Request $request)
    {
        if ($request->getRequestFormat() == 'rss') {
            $now = new \DateTime;
            $next_week = clone $now;
            $next_week->modify('+10 days');
            $shows = $this->getRepository()->findInDateRange($now, $next_week);

            return $this->view($shows);
        } else {
            return parent::cgetAction($request);
        }
    }

    public function getAction($identifier)
    {
        $show = $this->getRepository()->findOneBySlug($identifier);
        if (!$show) {
            $slugEntity = $this->getDoctrine()->getRepository('ActsCamdramBundle:ShowSlug')
                ->findOneBySlug($identifier);
            if (!$slugEntity) {
                throw $this->createNotFoundException('That '.$this->type.' does not exist');
            } else {
                return $this->redirectToRoute('get_show', ['identifier' => $slugEntity->getShow()->getSlug()]);
            }
        }

        $this->denyAccessUnlessGranted('VIEW', $show);

        $can_contact = $this->getDoctrine()->getRepository('ActsCamdramSecurityBundle:User')
            ->getContactableEntityOwners($show) > 0;
        $societyACEs = $this->getDoctrine()->getRepository('ActsCamdramSecurityBundle:SocietyAccessCE')
            ->findAllLinkedSocs($show);
        
        $view = $this->view($show, 200)
            ->setTemplate('show/show.html.twig')
            ->setTemplateData(['show' => $show, 'can_contact' => $can_contact, 'societyACEs' => $societyACEs]);
        ;
        return $view;
    }
    
    /**
     * Action that allows querying by id. Redirects to slug URL
     * 
     * @Rest\Get("/shows/by-id/{id}")
     */
    public function getByIdAction(Request $request, $id)
    {
        $this->checkAuthenticated();
        $show = $this->getRepository()->findOneById($id);
        
        if (!$show)
        {
            throw $this->createNotFoundException('That show id does not exist');
        }

        return $this->redirectToRoute('get_show', ['identifier' => $show->getSlug(), '_format' => $request->getRequestFormat()]);
    }

    public function postAction(Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * Called by AbstractRestController before form goes to user.
     */
    public function modifyEditForm($form, $identifier) {
        // List of societies is public knowledge, no ACL checks here.
        $em = $this->getDoctrine()->getManager();
        $show = $this->getEntity($identifier);
        $socs = $em->getRepository('ActsCamdramSecurityBundle:SocietyAccessCE')->findAllLinkedSocs($show);
        foreach ($socs as &$soc) {
            $soc = $soc->getSociety() ? $soc->getSociety()->getName() : $soc->getSocietyName();
        }
        $form->get('societies')->setData($socs);
    }

    /**
     * Called by AbstractRestController after form sent by user.
     */
    public function afterEditFormSubmitted($form, $identifier) {
        $em = $this->getDoctrine()->getManager();
        $show = $this->getEntity($identifier);

        $socRepo    = $em->getRepository('ActsCamdramBundle:Society');
        $socACERepo = $em->getRepository('ActsCamdramSecurityBundle:SocietyAccessCE');
        $newSocs = [];   // Array of [String, Society]
        $oldSocs = $socACERepo->findAllLinkedSocs($show);   // ACE array
        foreach ($form->get('societies')->getData() as $newSocName) {
            $newSocs[] = [$newSocName, $socRepo->findOneByName($newSocName)];
        }

        // Let's consider registered societies first.
        $oldRegisteredSocs = array_filter(array_map(function($ace) { return $ace->getSociety(); }, $oldSocs));
        $newRegisteredSocs = array_filter(array_map(function( $x ) { return $x[1]; }, $newSocs));
        $addedSocs = array_udiff($newRegisteredSocs, $oldRegisteredSocs, // array_udiff ignores array order.
            function($a, $b) { return $a->getId() - $b->getId(); } );
        $lostSocs  = array_udiff($oldRegisteredSocs, $newRegisteredSocs,
            function($a, $b) { return $a->getId() - $b->getId(); } );
        if ($lostSocs || $addedSocs) {
            foreach ($lostSocs as $lostSoc) {
                $ace = $socACERepo->findLiveAce($lostSoc->getId(), $show);
                $ace->setRevokedBy($this->getUser());
                $ace->setRevokedAt(new \DateTime());
            }
            foreach ($addedSocs as $addedSoc) {
                $ace = new SocietyAccessCE();
                $ace->setSociety($addedSoc);
                $ace->setType('show');
                $ace->setEntityId($show->getId());
                $ace->setGrantedBy($this->getUser());
                $ace->setCreatedAt(new \DateTime());
                $ace->setDisplayOrder(-999);
                $em->persist($ace);
            }
        }

        // Now unregistered societies.
        $oldUnregisteredSocs = array_filter(array_map(function($ace) {
                return $ace->getSociety() ? NULL : $ace->getSocietyName();
            }, $oldSocs));
        $newUnregisteredSocs = array_map(function($x) { return $x[0]; },
            array_filter($newSocs, function($x) { return $x[1] === NULL; }));
        $addedNames   = array_diff($newUnregisteredSocs, $oldUnregisteredSocs);
        $removedNames = array_diff($oldUnregisteredSocs, $newUnregisteredSocs);
        foreach ($addedNames as $addedName) {
            $ace = new SocietyAccessCE();
            $ace->setSocietyName($addedName);
            $ace->setType('show');
            $ace->setEntityId($show->getId());
            $ace->setGrantedBy($this->getUser());
            $ace->setCreatedAt(new \DateTime());
            $ace->setDisplayOrder(-999);
            $em->persist($ace);
        }
        foreach ($removedNames as $removedName) {
            $ace = $socACERepo->findLiveNameAce($removedName, $show);
            $ace->setRevokedBy($this->getUser());
            $ace->setRevokedAt(new \DateTime());
        }
        $em->flush();

        // Finally ordering.
        for ($i = 0; $i < count($newSocs); $i++) {
            if ($newSocs[$i][1]) {
                $socACERepo->findLiveAce($newSocs[$i][1]->getId(), $show)->setDisplayOrder($i);
            } else {
                $socACERepo->findLiveNameAce($newSocs[$i][0], $show)->setDisplayOrder($i);
            }
        }
    }

    public function deleteAction($identifier)
    {
        parent::removeAction($identifier);
    }

    private function getTechieAdvertForm(Show $show, $obj = null)
    {
        if (!$obj) {
            $obj = new TechieAdvert();
            $obj->setShow($show);
        }
        $form = $this->createForm(TechieAdvertType::class, $obj);
        return $form;
    }

    /**
     * Render the Admin Panel
     */
    public function adminPanelAction(Show $show)
    {
        $em = $this->getDoctrine()->getManager();
        $acl = $this->get('camdram.security.acl.provider');
        $admins = $acl->getOwners($show);
        $requested_admins = $em->getRepository('ActsCamdramSecurityBundle:User')->getRequestedShowAdmins($show);
        $pending_admins = $em->getRepository('ActsCamdramSecurityBundle:PendingAccess')->findByResource($show);
        $societies = $acl->getOwningSocs($show);

        $admins = array_merge($admins, $societies);

        if ($show->getVenue()) {
            $admins[] = $show->getVenue();
        }

        return $this->render(
            'show/admin-panel.html.twig',
            array('show' => $show,
                  'admins' => $admins,
                  'requested_admins' => $requested_admins,
                  'pending_admins' => $pending_admins)
            );
    }

    /**
     * Render the Search Result Panel. This view is used when a show is listed
     * in the search results.
     */
    public function searchResultPanelAction($slug)
    {
        $show = $this->getRepository()->findOneBySlug($slug);

        return $this->render(
            'show/search-result-panel.html.twig',
            array('show' => $show)
            );
    }

    public function editInlineAction($identifier)
    {
        $show = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('EDIT', $show);

        return $this->redirectToRoute('get_show', ['identifier' => $identifier]);
    }
    
    public function removeImageAction($identifier)
    {
        $show = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('EDIT', $show);
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($show->getImage());
        $show->setImage(null);
        $em->flush();
        
        return $this->redirectToRoute('get_show', ['identifier' => $identifier]);
    }

    public function unapproveAction($identifier)
    {
        $show = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('APPROVE', $show);

        $em = $this->getDoctrine()->getManager();
        $show->setAuthorised(false);
        $em->flush();

        return $this->redirectToRoute('get_show', ['identifier' => $identifier]);
    }

    public function getRolesAction($identifier)
    {
        $show = $this->getEntity($identifier);
        $role_repo = $this->getDoctrine()->getRepository('ActsCamdramBundle:Role');
        $roles = $role_repo->findByShow($show);

        return $this->view($roles);
    }

    public function getPeopleAction($identifier)
    {
        return $this->getRolesAction($identifier);
    }
}
