<?php

namespace App\Controller;

use App\Entity\Box;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Condition;
use App\Entity\Containertype;
use App\Entity\Country;
use App\Entity\Database;
use App\Entity\Dealer;
use App\Entity\Epoch;
use App\Entity\Manufacturer;
use App\Entity\Modelset;
use App\Entity\Project;
use App\Entity\Scale;
use App\Entity\ScaleTrack;
use App\Entity\State;
use App\Entity\Status;
use App\Entity\Storage;
use App\Entity\Subcategory;
use App\Entity\Subepoch;
use App\Entity\Coupler;
use App\Entity\Decoder;
use App\Entity\Edition;
use App\Entity\Pininterface;
use App\Entity\Power;
use App\Entity\Protocol;
use App\Entity\Axle;
use App\Entity\Maker;
use App\Entity\UserDropdown;
use App\Form\NewPasswordFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class UserController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route(path: '/profile', name: 'mbs_profile')]
    public function profile(EntityManagerInterface $entityManager, TranslatorInterface $translator, request $request, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/data/profile')] string $imageDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        if($user->getImage()!="") {
            $currentImage = $user->getImage();
        }
        $locales = $this->getParameter('locales');
        $limits = $this->getParameter('limits');
        foreach($limits as $limit) {
            $limitchoice[$limit] = $limit;
        }
        foreach($locales as $locale) {
            $localechoice[$locale['name']] = $locale['code'];
        }


        $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('dark', ChoiceType::class, ['choices' => [$translator->trans('global.auto') => null ,$translator->trans('global.yes') => true, $translator->trans('global.no') => false]])
            ->add('pagination', ChoiceType::class, ['choices' => $limitchoice])
            ->add('language', ChoiceType::class, ['choices' => $localechoice])
            ->add('image', FileType::class, ['required' => false, 'data_class' => null, 'empty_data' => ''])
            ->add('save', SubmitType::class, ['label' => $translator->trans('global.save')]);

        $form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                try {
                    $imageFile->move($imageDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        $translator->trans('upload.failed', ['message' => $e->getMessage()])
                    );
                    return $this->redirectToRoute('mbs_profile');
                    // ... handle exception if something happens during file upload
                }
                $user->setImage($newFilename);
                if($this->getParameter('remote_ssh')!="") {
                    try {
                        exec('/usr/bin/scp '.$imageDirectory.'/'.$newFilename.' '.$this->getParameter('remote_ssh').'profile/'.$newFilename);
                    } catch (Exception $e) {
                    }
                }
            } elseif(isset($currentImage) && $currentImage!="") {
                $user->setImage($currentImage);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('user.saved')
            );
            return $this->redirectToRoute('mbs_profile');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $translator->trans('user.resubmit')
            );
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
        }
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('user/profile.html.twig', [
            "databases" => $databases,
            'user' => $user,
            'userform' => $form,
        ]);
    }

    #[Route('/change-password', name: 'mbs_change_password')]
    public function change(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(NewPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $oldPassword = $form->get('oldPassword')->getData();
            if(!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $response = new Response();
                $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
                $this->addFlash(
                    'error',
                    $translator->trans('login.password.error.invalid')
                );
                return $this->redirectToRoute('mbs_change_password');
            }

            $this->addFlash(
                'success',
                $translator->trans('login.password.success')
            );
            // Encode(hash) the plain password, and set it.
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $entityManager->flush();

            return $this->redirectToRoute('mbs_profile');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);
        return $this->render('security/password.html.twig', [
            "databases" => $databases,
            'resetForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/dropdowns', name: 'mbs_dropdowns')]
    public function dropdowns(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        $databases = $entityManager->getRepository(Database::class)->findBy(["user" => $user]);

        $status         = $entityManager->getRepository(Status::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $category       = $entityManager->getRepository(Category::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subcategory    = $entityManager->getRepository(Subcategory::class)->findBy(array("user" => [null, $user->getId()]), ["category" => "ASC", "name" => "ASC"]);
        $manufacturer   = $entityManager->getRepository(Manufacturer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $company        = $entityManager->getRepository(Company::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $scale          = $entityManager->getRepository(Scale::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $track          = $entityManager->getRepository(ScaleTrack::class)->findBy(array("user" => [null, $user->getId()]), ["scale" => "ASC", "name" => "ASC"]);
        $epoch          = $entityManager->getRepository(Epoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $subepoch       = $entityManager->getRepository(Subepoch::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $storage        = $entityManager->getRepository(Storage::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $project        = $entityManager->getRepository(Project::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $dealer         = $entityManager->getRepository(Dealer::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $box            = $entityManager->getRepository(Box::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $condition      = $entityManager->getRepository(Condition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $country        = $entityManager->getRepository(Country::class)->findBy([], ["name" => "ASC"]);
        $modelset       = $entityManager->getRepository(Modelset::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $containertype  = $entityManager->getRepository(Containertype::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $coupler        = $entityManager->getRepository(Coupler::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $decoder        = $entityManager->getRepository(Decoder::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $edition        = $entityManager->getRepository(Edition::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $pininterface   = $entityManager->getRepository(Pininterface::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $power          = $entityManager->getRepository(Power::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $protocol       = $entityManager->getRepository(Protocol::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $axle           = $entityManager->getRepository(Axle::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);
        $maker          = $entityManager->getRepository(Maker::class)->findBy(array("user" => [null, $user->getId()]), ["name" => "ASC"]);

        $userDropdowns  =  $entityManager->getRepository(UserDropdown::class)->findBy(array("user" => $user->getId()), ["name" => "ASC"]);

        foreach($userDropdowns as $dropdown) {
            $dropdownDefaults[$dropdown->getName()]['defaultvalue'] = $dropdown->getDefaultvalue();
            $dropdownDefaults[$dropdown->getName()]['position'] = $dropdown->getPosition();
        }

        $dropdowns = [
            "country" => ["values" => $country, "name" => $translator->trans('model.field.country'), "defaultvalue" => $dropdownDefaults['country']['defaultvalue'], "position" => $dropdownDefaults['country']['position']],
            "category" => ["values" => $category, "name" => $translator->trans('model.field.category'), "defaultvalue" => $dropdownDefaults['category']['defaultvalue'], "position" => $dropdownDefaults['category']['position']],
            "subcategory" => ["values" => $subcategory, "name" => $translator->trans('model.field.subcategory'), "defaultvalue" => $dropdownDefaults['subcategory']['defaultvalue'], "position" => $dropdownDefaults['subcategory']['position']],
            "manufacturer" => ["values" => $manufacturer, "name" => $translator->trans('model.field.manufacturer'), "defaultvalue" => $dropdownDefaults['manufacturer']['defaultvalue'], "position" => $dropdownDefaults['manufacturer']['position']],
            "company" => ["values" => $company, "name" => $translator->trans('model.field.company'), "defaultvalue" => $dropdownDefaults['company']['defaultvalue'], "position" => $dropdownDefaults['company']['position']],
            "scale" => ["values" => $scale, "name" => $translator->trans('model.field.scale'), "defaultvalue" => $dropdownDefaults['scale']['defaultvalue'], "position" => $dropdownDefaults['scale']['position']],
            "track" => ["values" => $track, "name" => $translator->trans('model.field.track'), "defaultvalue" => $dropdownDefaults['track']['defaultvalue'], "position" => $dropdownDefaults['track']['position']],
            "epoch" => ["values" => $epoch, "name" => $translator->trans('model.field.epoch'), "defaultvalue" => $dropdownDefaults['epoch']['defaultvalue'], "position" => $dropdownDefaults['epoch']['position']],
            "subepoch" => ["values" => $subepoch, "name" => $translator->trans('model.field.subepoch'), "defaultvalue" => $dropdownDefaults['subepoch']['defaultvalue'], "position" => $dropdownDefaults['subepoch']['position']],
            "storage" => ["values" => $storage, "name" => $translator->trans('model.field.storage'), "defaultvalue" => $dropdownDefaults['storage']['defaultvalue'], "position" => $dropdownDefaults['storage']['position']],
            "project" => ["values" => $project, "name" => $translator->trans('model.field.project'), "defaultvalue" => $dropdownDefaults['project']['defaultvalue'], "position" => $dropdownDefaults['project']['position']],
            "status" => ["values" => $status, "name" => $translator->trans('model.field.status'), "defaultvalue" => $dropdownDefaults['status']['defaultvalue'], "position" => $dropdownDefaults['status']['position']],
            "dealer" => ["values" => $dealer, "name" => $translator->trans('model.field.dealer'), "defaultvalue" => $dropdownDefaults['dealer']['defaultvalue'], "position" => $dropdownDefaults['dealer']['position']],
            "box" => ["values" => $box, "name" => $translator->trans('model.field.box'), "defaultvalue" => $dropdownDefaults['box']['defaultvalue'], "position" => $dropdownDefaults['box']['position']],
            "condition" => ["values" => $condition, "name" => $translator->trans('model.field.condition'), "defaultvalue" => $dropdownDefaults['condition']['defaultvalue'], "position" => $dropdownDefaults['condition']['position']],
            "modelset" => ["values" => $modelset, "name" => $translator->trans('model.field.modelset'), "defaultvalue" => $dropdownDefaults['modelset']['defaultvalue'], "position" => $dropdownDefaults['modelset']['position']],
            "edition" => ["values" => $edition, "name" => $translator->trans('model.field.edition'), "defaultvalue" => $dropdownDefaults['edition']['defaultvalue'], "position" => $dropdownDefaults['edition']['position']],
            "maker" => ["values" => $maker, "name" => $translator->trans('model.field.maker'), "defaultvalue" => $dropdownDefaults['maker']['defaultvalue'], "position" => $dropdownDefaults['maker']['position']],
            "containertype" => ["values" => $containertype, "name" => $translator->trans('model.field.containertype'), "defaultvalue" => $dropdownDefaults['containertype']['defaultvalue'], "position" => $dropdownDefaults['containertype']['position']],
            "coupler" => ["values" => $coupler, "name" => $translator->trans('model.field.coupler'), "defaultvalue" => $dropdownDefaults['coupler']['defaultvalue'], "position" => $dropdownDefaults['coupler']['position']],
            "power" => ["values" => $power, "name" => $translator->trans('model.field.power'), "defaultvalue" => $dropdownDefaults['power']['defaultvalue'], "position" => $dropdownDefaults['power']['position']],
            "axle" => ["values" => $axle, "name" => $translator->trans('model.field.axle'), "defaultvalue" => $dropdownDefaults['axle']['defaultvalue'], "position" => $dropdownDefaults['axle']['position']],
            "decoder" => ["values" => $decoder, "name" => $translator->trans('model.field.decoder'), "defaultvalue" => $dropdownDefaults['decoder']['defaultvalue'], "position" => $dropdownDefaults['decoder']['position']],
            "pininterface" => ["values" => $pininterface, "name" => $translator->trans('model.field.pininterface'), "defaultvalue" => $dropdownDefaults['pininterface']['defaultvalue'], "position" => $dropdownDefaults['pininterface']['position']],
            "protocol" => ["values" => $protocol, "name" => $translator->trans('model.field.protocol'), "defaultvalue" => $dropdownDefaults['protocol']['defaultvalue'], "position" => $dropdownDefaults['protocol']['position']],
        ];

        return $this->render('user/dropdowns.html.twig', [
            "databases" => $databases,
            "user" => $user,
            "dropdowns" => $dropdowns,
        ]);
    }
    #[Route('/dropdown/default', name: 'mbs_dropdown_default', format: 'json', methods: ['POST'])]
    public function dropdownDefault(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $name = $request->get('name');
        $defaultvalue = $request->get('defaultvalue');
        $position = $request->get('position');
        $user = $this->security->getUser();

        $userDropdown = $entityManager->getRepository(UserDropdown::class)->findOneBy(array("user" => $user->getId(), "name" => $name));
        if($defaultvalue!="") {
            $userDropdown->setDefaultvalue($defaultvalue);
        } else {
            $userDropdown->setDefaultvalue(null);
        }
        if($position!="") {
            $userDropdown->setPosition($position);
        } else {
            $userDropdown->setPosition(null);
        }
        $entityManager->persist($userDropdown);
        $entityManager->flush();

        if($defaultvalue!="") {
            $record = $entityManager->getRepository('App\\Entity\\'.$name)->findOneBy(["id" => $defaultvalue]);

        }

        $headline = $translator->trans('global.notifications.defaults', ['name' => $translator->trans('model.field.'.$name)]);
        $text[] = $translator->trans('global.notifications.new-value', ['value' => $defaultvalue=='' ? $translator->trans('global.no-selection') : $record->getName()]);
        $text[] = $translator->trans('global.notifications.new-position', ['value' => $position=='' ? $translator->trans('global.no-selection') : $position]);

        $text = implode("<br/>", $text);

        return new Response('{"success": true, "headline": "'.$headline.'", "text": "'.$text.'"}');
    }

}
