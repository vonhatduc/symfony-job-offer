<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Identity\Domain\Entity\User;

final class SecurityController extends AbstractController
{
    /**
     * This controller is empty because login is handled by the LexikJWT firewall.
     * Documentation is handled by the Credentials DTO in src/UI/Api/V1/Resource/Auth/
     */
}
