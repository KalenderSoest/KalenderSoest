<?php

namespace App\Security;

use App\Entity\DfxKonf;
use App\Entity\DfxNfxKunden;
use App\Entity\DfxNfxUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CurrentContext
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getUser(): DfxNfxUser
    {
        $user = $this->security->getUser();
        if (!$user instanceof DfxNfxUser) {
            throw new AccessDeniedHttpException('Authenticated DfxNfxUser required.');
        }

        return $user;
    }

    public function getKonf(): DfxKonf
    {
        $konf = $this->getUser()->getDatefix();
        if (!$konf instanceof DfxKonf) {
            throw new AccessDeniedHttpException('No calendar account assigned to current user.');
        }

        return $konf;
    }

    public function getKunde(): DfxNfxKunden
    {
        $kunde = $this->getUser()->getKunde();
        if (!$kunde instanceof DfxNfxKunden) {
            throw new AccessDeniedHttpException('No customer account assigned to current user.');
        }

        return $kunde;
    }
}