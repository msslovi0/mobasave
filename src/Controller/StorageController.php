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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
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
        $state          = $entityManager->getRepository(State::class)->findBy([], ['name' => 'ASC']);

        $storage = new Storage();

        $form = $this->createFormBuilder($storage)
            ->add('name', TextType::class)
            ->add('color', ChoiceType::class, ['required' => false, 'choices' => [$translator->trans('global.color.gray') => 'gray' ,$translator->trans('global.color.red') => 'red', $translator->trans('global.color.yellow') => 'yellow', $translator->trans('global.color.green') => 'green', $translator->trans('global.color.blue') => 'blue', $translator->trans('global.color.indigo') => 'indigo', $translator->trans('global.color.purple') => 'purple', $translator->trans('global.color.pink') => 'pink']])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($storage);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('storage.saved', ['name' => $storage->getName()])
            );
            return $this->redirectToRoute('mbs_storage', ['id' => $storage->getId()]);
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('storage/storage.html.twig', [
            "databases" => $databases,
            "storageform" => $form->createView(),
            "storage" => $storage,
        ]);
    }

    #[Route('/storage/{id}', name: 'mbs_storage', methods: ['GET', 'POST'])]
    public function storage(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $storage = $entityManager->getRepository(Storage::class)->findOneBy(["id" => $id]);

        $form = $this->createFormBuilder($storage)
            ->add('name', TextType::class)
            ->add('color', ChoiceType::class, ['required' => false, 'choices' => [$translator->trans('global.color.gray') => 'gray' ,$translator->trans('global.color.red') => 'red', $translator->trans('global.color.yellow') => 'yellow', $translator->trans('global.color.green') => 'green', $translator->trans('global.color.blue') => 'blue', $translator->trans('global.color.indigo') => 'indigo', $translator->trans('global.color.purple') => 'purple', $translator->trans('global.color.pink') => 'pink']])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($storage);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('storage.saved', ['name' => $storage->getName()])
            );
            return $this->redirectToRoute('mbs_storage', ['id' => $storage->getId()]);
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
        return $this->render('storage/storage.html.twig', [
            "databases" => $databases,
            "storageform" => $form->createView(),
            "storage" => $storage,
        ]);
    }

    #[Route('/storage/', name: 'mbs_storage_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $storages = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ['name' => 'ASC']);

        $limit = $request->query->get('limit');
        $limits = $this->getParameter('limits');
        if($limit=="" || !in_array($limit, $limits)) {
            $limit = $request->getSession()->get('limit');
        } else {
            $request->getSession()->set('limit', $limit);
        }

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
    public function models(int $id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, request $request): Response
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
    public function delete(int $id, EntityManagerInterface $entityManager, TranslatorInterface $translator, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $storage = $entityManager->getRepository(Storage::class)->findOneBy(["id" => $id]);
        if(count($storage->getModels())>0) {
            $this->addFlash(
                'error',
                $translator->trans('storage.has-models', ['count' => count($storage->getModels()), 'name' => $storage->getName()])
            );
            $entityManager->flush();
            return $this->redirectToRoute('mbs_storage', ['id' => $storage->getId()]);
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

