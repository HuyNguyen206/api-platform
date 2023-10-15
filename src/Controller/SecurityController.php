<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(IriConverterInterface $iriConverter, #[CurrentUser] $user = null ): Response
    {
        return new Response(null, 204, [
            'Location' => $iriConverter->getIriFromResource($user)
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout()
    {
        throw new \Exception('Never reach here');
    }
}
