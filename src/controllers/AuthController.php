<?php

namespace TestApp\Controllers;

use TestApp\Core\Controller;
use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;
use TestApp\Utils\StringUtils;

class AuthController extends Controller
{
  // Middleware
  public static function redirectUserIfSignedIn(Request $req, Response $res)
  {
    if (!($user = $res->session->getUser()))
      return;

    $res->session->setErrorMessages("Vous êtes déjà connecté.");
    $res->redirect("profile-home", [
      "username" => $user["username"]
    ]);
    exit;
  }

  public static function checkIfUserSignedIn(Request $req, Response $res)
  {
    if ($res->session->getUser())
      return;

    $res->session->setErrorMessages("Vous n'êtes pas connecté(e).");
    $res->redirect("sign-in");
    exit;
  }

  public static function signin_GET(Request $req, Response $res)
  {
    $res->render("auth/sign-in");
  }

  public static function signIn_POST(Request $req, Response $res)
  {
    $body = $req->getBody();
    $usernameOrEmail = $body["username_or_email"] ?? null;
    $password = $body["password"] ?? null;

    $error = null;
    if (!$usernameOrEmail || !$password)
      $error = "Veuillez remplir tous les champs.";
    else {
      $user = User::findOne([
        "OR" => [
          "username" => $usernameOrEmail,
          "email" => $usernameOrEmail
        ]
      ]);

      if (!$user || !password_verify($password, $user->getPassword()))
        $error = "Identifiants incorrects.";

      else {
        if (!$user->isVerified())
          $error = "Vous devez d'abord activer votre compte de pouvoir vous connecter.";
      }
    }

    if ($error) {
      $res->session->setErrorMessages([$error]);
      $res->session->setFormData(["username_or_email" => $usernameOrEmail]);
      return $res->redirect("sign-in");
    }

    $res
      ->session
      ->setSuccessMessage("Vous êtes connecté(e).")
      ->signIn([
        "id" => $user->getId(),
        "username" => $user->getUsername(),
        "email" => $user->getEmail(),
        "role" => $user->getRole(),
        "created_at" => $user->getCreatedAt()
      ]);
    $res->redirect("profile-home", [
      "username" => $user->getUsername()
    ]);
  }

  public static function signUp_GET(Request $req, Response $res)
  {
    $res->render("auth/sign-up");
  }

  public static function signUp_POST(Request $req, Response $res)
  {
    $body = $req->getBody();
    $username = $body["username"] ?? null;
    $email = $body["email"] ?? null;
    $password1 = $body["password1"] ?? null;
    $password2 = $body["password2"] ?? null;
    $errors = self::getSignUpErrors($username, $email, $password1, $password2);

    if ($errors) {
      $res
        ->session
        ->setErrorMessages($errors)
        ->setFormData([
          "username" => $username,
          "email" => $email
        ]);
      return $res->redirect("sign-up");
    }

    $user = new User();
    $user
      ->setUsername($username)
      ->setEmail($email)
      ->setPassword(password_hash($password1, PASSWORD_DEFAULT));

    try {
      $user->save();
      $user->notify();
      $res->session->setSuccessMessage("Votre compte a bien été créé. Veuillez suivre le lien qui vous a été envoyé par mail pour l'activer.");
      return $res->redirect("sign-in");
    } catch (\Exception $e) {
      $res->session->setErrorMessages([$e]);
      $res->redirect("sign-up");
    }
  }

  public static function signOut($req, Response $res)
  {
    if ($res->session->getUser()) {
      $res->session->signOut();
      $res->session->setSuccessMessage("Vous avez bien été déconnecté(e).");
    }

    return $res->redirect("home");
  }

  public static function activateAccount(Request $req, Response $res)
  {
    $verif_string = $req->getParam("verif_string");

    if (!$verif_string)
      return $res->redirect("home");

    $user = User::findOne(["verif_string" => $verif_string]);

    if (!$user || $user->isVerified())
      return $res->redirect("home");

    $user->verify();
    $res->session->setSuccessMessage("Votre compte est à présent actif. Il ne vous reste plus qu'à vous connecter.");
    $res->redirect("sign-in");
  }

  private static function getSignUpErrors($username, $email, $password1, $password2): array
  {
    if (
      !is_string($username)
      || !is_string($email)
      || !is_string($password1)
      || !is_string($password2)
    )
      return ["Veuillez remplir tous les champs."];

    $errors = [];

    if ((bool) User::findOne(["username" => $username]))
      $errors[] = "Nom d'utilisateur indisponible.";
    if ((bool) User::findOne(["email" => $email]))
      $errors[] = "Un compte à cette adresse email existe déjà.";
    if (strlen($username) < 5 || strlen($username) > 50)
      $errors[] = "Veuillez choisir un nom d'utilisateur entre 5 et 50 caractères.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      $errors[] = "Veuillez saisir une adresse email valide.";
    if ($password1 !== $password2)
      $errors[] = "Les mots de passe ne se correspondent pas.";

    return [...$errors, ...StringUtils::checkPasswordStrength($password1)];
  }
}
