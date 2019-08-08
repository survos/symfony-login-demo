<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Form\User\ChangeEmailType;
use App\Form\User\ChangePasswordType;
use MsgPhp\User\Command\ChangeUserCredential;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/profile", name="profile")
 */
final class ProfileController
{
    /**
     * @ParamConverter("user", converter="msgphp.current_user")
     */
    public function __invoke(
        User $user,
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        Environment $twig,
        MessageBusInterface $bus
    ): Response {
        $usernameForm = $formFactory->create(ChangeEmailType::class);
        $usernameForm->handleRequest($request);

        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $bus->dispatch(new ChangeUserCredential($user->getId(), $usernameForm->getData()));
            $flashBag->add('success', 'You\'re username is changed.');

            return new RedirectResponse('/profile');
        }

        $passwordForm = $formFactory->create(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $bus->dispatch(new ChangeUserCredential($user->getId(), $passwordForm->getData()));
            $flashBag->add('success', 'You\'re password is changed.');

            return new RedirectResponse('/profile');
        }

        return new Response($twig->render('user/profile.html.twig', [
            'username_form' => $usernameForm->createView(),
            'password_form' => $passwordForm->createView(),
        ]));
    }
}
