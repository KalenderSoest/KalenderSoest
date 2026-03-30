<?php

namespace App\Service\Install;

use App\Entity\DfxNfxUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final class InstallerBootstrapService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array{success: bool, user_id: ?int, message: string}
     */
    public function ensureAnonymousWebUser(): array
    {
        $existingUser = $this->em->getRepository(DfxNfxUser::class)->find(99);
        if ($existingUser instanceof DfxNfxUser) {
            return [
                'success' => true,
                'user_id' => $existingUser->getId(),
                'message' => 'Anonymer Webuser ist bereits vorhanden.',
            ];
        }

        $user = new DfxNfxUser();
        $user->setId(99);
        $user->setUsername('webuser');
        $user->setEmail('nobody@nirgendwo.de');
        $user->setNameLang('nobody');
        $user->setPassword('noPassword');
        $user->setRoles(['IS_AUTHENTICATED_ANONYMOUSLY']);

        $metadata = $this->em->getClassMetaData($user::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->em->persist($user);
        $this->em->flush();

        return [
            'success' => $user->getId() === 99,
            'user_id' => $user->getId(),
            'message' => $user->getId() === 99
                ? 'Anonymer Webuser wurde angelegt.'
                : 'Beim Anlegen des anonymen Webusers ist ein Fehler aufgetreten.',
        ];
    }
}
