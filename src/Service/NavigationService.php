<?php
namespace App\Service;

use App\Entity\UserDropdown;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use App\Entity\Database;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NavigationService
{
    public function __construct(private Environment $twig, private EntityManagerInterface $entityManager, private Security $security, private TranslatorInterface $translator, private UrlGeneratorInterface $urlGenerator) {
    }

    public function mainNav(): string
    {
        $user = $this->security->getUser();
        $databases = $this->entityManager->getRepository(Database::class)->findBy(["user" => $user]);

        $qb = $this->entityManager->createQueryBuilder();
        $navigation = $qb->select('ud')
            ->from(UserDropdown::class, 'ud')
            ->where($qb->expr()->isNotNull("ud.position"))
            ->addOrderBy('ud.position', 'ASC')
            ->getQuery()
            ->getResult();
        foreach($navigation as $nav) {
            $mainnav[] = ['slug' => $nav->getName(), 'name' => $this->translator->trans("model.field.".$nav->getName()), "path" => $this->urlGenerator->generate('mbs_'.$nav->getName().'_list', [], UrlGeneratorInterface::ABSOLUTE_URL)];
        }

        $htmlContents = $this->twig->render('nav.html.twig', [
            'databases' => $databases,
            'mainnav' => $mainnav,
        ]);

        return $htmlContents;
    }
}
