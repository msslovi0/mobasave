<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Storage;
use App\Entity\Model;
use App\Entity\State;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/storage/add/', name: 'mbs_storage_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);

        $storage = new Storage();

        $form = $this->createFormBuilder($storage)
            ->add('name', TextType::class)
            ->add('country', ChoiceType::class, ['required' => false, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('fill', RangeType::class, ['required' => false, 'attr' => ["min" => 0, "max" => 100, "step" => 5]])
            ->add('slot', ChoiceType::class, ['required' => false, 'choices' => [
                $translator->trans('default') => 'default',
                $translator->trans('narrow') => 'narrow',
                $translator->trans('wide') => 'wide',
            ]])
            ->add('color', ChoiceType::class, ['required' => false, 'choices' => [
                $translator->trans('red') => 'red',
                $translator->trans('orange') => 'orange',
                $translator->trans('amber') => 'amber',
                $translator->trans('yellow') => 'yellow',
                $translator->trans('lime') => 'lime',
                $translator->trans('green') => 'green',
                $translator->trans('emerald') => 'emerald',
                $translator->trans('teal') => 'teal',
                $translator->trans('cyan') => 'cyan',
                $translator->trans('sky') => 'sky',
                $translator->trans('blue') => 'blue',
                $translator->trans('indigo') => 'indigo',
                $translator->trans('violet') => 'violet',
                $translator->trans('purple') => 'purple',
                $translator->trans('fuchsia') => 'fuchsia',
                $translator->trans('pink') => 'pink',
                $translator->trans('rose') => 'rose',
                $translator->trans('slate') => 'slate',
                $translator->trans('gray') => 'gray',
                $translator->trans('zinc') => 'zinc',
                $translator->trans('neutral') => 'neutral',
                $translator->trans('stone') => 'stone',
            ]])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $storage->setUser($user);
            $storage->setUuid(Uuid::v4());
            $entityManager->persist($storage);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('storage.saved', ['name' => $storage->getName()])
            );
            return $this->redirectToRoute('mbs_storage', ['id' => str_replace("0x","",$storage->getUuid()->toHex())]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('storage/storage.html.twig', [
            "databases" => $databases,
            "storageform" => $form->createView(),
            "storage" => $storage,
            "disabled" => false
        ]);
    }

    #[Route('/storage/{id}', name: 'mbs_storage', methods: ['GET', 'POST'])]
    public function storage(mixed $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, AuthorizationCheckerInterface $authChecker): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        if(strlen($id)==32) {
            $storage = $entityManager->getRepository(Storage::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $storage = $entityManager->getRepository(Storage::class)->findOneBy(["id" => $id]);
        } else {
            $storage = false;
        }

        if(!$storage) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        if(is_object($user) and is_object($storage->getUser()) and $storage->getUser()->getId()!=$user->getId()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/forbidden.html.twig', ["databases" => $databases], response: $response);
        }

        $country        = $entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);

        if(true === $authChecker->isGranted('ROLE_ADMIN') || $storage->getUser()==$user) {
            $disabled = false;
        } else {
            $disabled = true;
        }

        $form = $this->createFormBuilder($storage)
            ->add('name', TextType::class, ['disabled' => $disabled])
            ->add('country', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => $country, 'choice_label' => 'name', 'choice_attr' => function ($choice) {return ['data-id' => $choice->getId()];}])
            ->add('fill', RangeType::class, ['required' => false, 'disabled' => $disabled, 'attr' => ["min" => 0, "max" => 100, "step" => 5]])
            ->add('slot', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => [
                $translator->trans('default') => 'default',
                $translator->trans('narrow') => 'narrow',
                $translator->trans('wide') => 'wide',
            ]])
            ->add('color', ChoiceType::class, ['required' => false, 'disabled' => $disabled, 'choices' => [
                $translator->trans('red') => 'red',
                $translator->trans('orange') => 'orange',
                $translator->trans('amber') => 'amber',
                $translator->trans('yellow') => 'yellow',
                $translator->trans('lime') => 'lime',
                $translator->trans('green') => 'green',
                $translator->trans('emerald') => 'emerald',
                $translator->trans('teal') => 'teal',
                $translator->trans('cyan') => 'cyan',
                $translator->trans('sky') => 'sky',
                $translator->trans('blue') => 'blue',
                $translator->trans('indigo') => 'indigo',
                $translator->trans('violet') => 'violet',
                $translator->trans('purple') => 'purple',
                $translator->trans('fuchsia') => 'fuchsia',
                $translator->trans('pink') => 'pink',
                $translator->trans('rose') => 'rose',
                $translator->trans('slate') => 'slate',
                $translator->trans('gray') => 'gray',
                $translator->trans('zinc') => 'zinc',
                $translator->trans('neutral') => 'neutral',
                $translator->trans('stone') => 'stone',
            ]])
            ->add('save', SubmitType::class, ['disabled' => $disabled, 'label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $disabled===false) {
            $entityManager->persist($storage);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('storage.saved', ['name' => $storage->getName()])
            );
            return $this->redirectToRoute('mbs_storage', ['id' => str_replace("0x","",$storage->getUuid()->toHex())]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('model.resubmit', ['name' => $model->getName()])
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $models = $entityManager->getRepository(Model::class)->findBy(["modeldatabase" => $databases, "storage" => $storage]);
        return $this->render('storage/storage.html.twig', [
            "models" => $models,
            "databases" => $databases,
            "storageform" => $form->createView(),
            "storage" => $storage,
            "disabled" => $disabled,
        ]);
    }

    #[Route('/storage/', name: 'mbs_storage_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $sortcolumn = $request->query->get('sortcolumn');
        $sortorder = $request->query->get('sortorder');
        $limits = $this->getParameter('limits');
        $sortcolumns = $this->getParameter('storage.sortcolumns');
        if($sortcolumn!="" && in_array($sortcolumn, $sortcolumns)) {
            $request->getSession()->set('sortcolumn', $sortcolumn);
        } else {
            $request->getSession()->set('sortcolumn', $this->getParameter('storage.sortcolumn'));
        }
        if($sortorder!="" && in_array($sortorder, ['asc', 'desc'])) {
            $request->getSession()->set('sortorder', $sortorder);
        } else {
            $request->getSession()->set('sortorder', $this->getParameter('storage.sortorder'));
        }
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }
        $storages = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), [$request->getSession()->get('sortcolumn') => $request->getSession()->get('sortorder')]);


        $pagination = $paginator->paginate(
            $storages,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('storage/list.html.twig', [
            "databases" => $databases,
            "storages" => $pagination
        ]);
    }

    #[Route('/storage/{id}/models', name: 'mbs_storage_models', methods: ['GET', 'POST'])]
    public function models(mixed $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        $limit = $request->query->get('limit');
        $limits = $this->getParameter('limits');
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

        $storage = $entityManager->getRepository(Storage::class)->find($id);
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        $models = $entityManager->getRepository(Model::Class)->findBy(["storage" => $storage, "modeldatabase" => $databases], ['purchased' => 'DESC']);
        $pagination = $paginator->paginate(
            $models,
            $request->query->getInt('page', 1), /* page number */
            $limit /* limit per page */
        );

        return $this->render('collection/list.html.twig', [
            "databases" => $databases,
            "models" => $pagination
        ]);
    }

    #[Route('/storage/delete/{id}', name: 'mbs_storage_delete', methods: ['GET'])]
    public function delete(mixed $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        if(strlen($id)==32) {
            $storage = $entityManager->getRepository(Storage::class)->findOneBy(["uuid" => hex2bin($id)]);
        } elseif(is_numeric($id)) {
            $storage = $entityManager->getRepository(Storage::class)->findOneBy(["id" => $id]);
        } else {
            $storage = false;
        }

        if(count($storage->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('storage.has-models', ['count' => count($storage->getModels()), 'name' => $storage->getName()])
            );
            $entityManager->flush();
            return $this->redirectToRoute('mbs_storage', ['id' => str_replace("0x","",$storage->getUuid()->toHex())]);
        }
        if(!$storage) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
            return $this->render('status/notfound.html.twig', ["databases" => $databases], response: $response);
        }
        $entityManager->remove($storage);
        $this->addFlash(
            'success',
            $translator->trans('storage.deleted', ['name' => $storage->getName()])
        );
        $entityManager->flush();
        return $this->redirectToRoute('mbs_storage_list');

    }

}

