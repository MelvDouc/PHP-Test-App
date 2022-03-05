<?php

namespace TestApp\Controllers;

use TestApp\Core\Controller;
use TestApp\Core\Request;
use TestApp\Core\Response;
use TestApp\Models\User;

class AuthController extends Controller
{
  public const SIGN_IN_ROUTE = "sign-in";
  public const SIGN_UP_ROUTE = "sign-up";

  public static function signin_GET($req, Response $res)
  {
    self::redirectUserIfSignedIn($res);
    $res->render("auth/sign-in");
  }

  public static function signIn_POST(Request $req, Response $res)
  {
    $body = $req->getBody();
    $email = $body["email"];
    $password = $body["password"];

    $error = null;
    if (!$email || !$password)
      $error = "Veuillez remplir tous les champs.";
    else if (
      !($user = User::findOne(["email" => $email]))
      || !password_verify($password, $user->getPassword())
    )
      $error = "Identifiants incorrects.";
    else if (!$user->isVerified())
      $error = "Veuillez activer votre compte afin de pouvoir vous connecter.";

    if ($error) {
      $res->session->setErrorMessages([$error]);
      $res->session->setFormData(["email" => $email ?? ""]);
      return $res->redirect(self::SIGN_IN_ROUTE);
    }

    $res
      ->session
      ->setSuccessMessage("Vous êtes connecté(e).")
      ->signIn([
        "id" => $user->getId(),
        "username" => $user->getUsername(),
        "email" => $user->getEmail(),
        "created_at" => $user->getCreatedAt()
      ]);
    $res->redirect("profile-home", [
      "username" => $user->getUsername()
    ]);
  }

  public static function signUp_GET(Request $req, Response $res)
  {
    self::redirectUserIfSignedIn($res);
    $res->render("auth/sign-up");
  }

  public static function signUp_POST(Request $req, Response $res)
  {
    $body = $req->getBody();
    $username = $body["username"];
    $email = $body["email"];
    $password1 = $body["password1"];
    $password2 = $body["password2"];
    $errors = self::getSignUpErrors($username, $email, $password1, $password2);

    if ($errors) {
      $res
        ->session
        ->setErrorMessages($errors)
        ->setFormData([
          "username" => $username ?? "",
          "email" => $email ?? ""
        ]);
      return $res->redirect(self::SIGN_UP_ROUTE);
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
      return $res->redirect(self::SIGN_IN_ROUTE);
    } catch (\Exception $e) {
      $res->session->setErrorMessages([$e]);
      return $res->redirect(self::SIGN_UP_ROUTE);
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
    $verifString = $req->getParam("verifString");

    if (!$verifString)
      return $res->redirect("home");

    $user = User::findOne(["verif_string" => $verifString]);

    if (!$user || $user->isVerified())
      return $res->redirect("home");

    $user->verify();
    $res->session->setSuccessMessage("Votre compte est à présent actif.");
    $res->redirect(self::SIGN_IN_ROUTE);
  }

  private static function getSignUpErrors($username, $email, $password1, $password2): array
  {
    if (!$username || !$email || !$password1 || !$password2)
      return ["Veuillez remplir tous les champs."];

    $errors = [];
    $usernameExists = (bool) User::findOne(["username" => $username]);
    $emailExists = (bool) User::findOne(["email" => $email]);

    if ($usernameExists)
      $errors[] = "Nom d'utilisateur indisponible.";
    if ($emailExists)
      $errors[] = "Un compte à cette adresse email existe déjà.";
    if (strlen($username) < 5 || strlen($username) > 50)
      $errors[] = "Veuillez choisir un nom d'utilisateur entre 5 et 50 caractères.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      $errors[] = "Veuillez saisir une adresse email valide.";
    if ($password1 !== $password2)
      $errors[] = "Les mots de passe ne se correspondent pas.";

    return $errors;
  }

  private static function redirectUserIfSignedIn(Response $res)
  {
    if (!($user = $res->session->getUser()))
      return;

    $res->session->setErrorMessages(["Vous êtes déjà connecté."]);
    $res->redirect("profile-home", [
      "username" => $user["username"]
    ]);
    die;
  }
}
