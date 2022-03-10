<?php

namespace TestApp\Models;

use TestApp\Core\Model;
use TestApp\Core\Application;
use TestApp\Utils\StringUtils;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class User extends Model
{
  public const TABLE_NAME = "user";
  public static array $ROLES = [
    "ADMIN" => 0,
    "USER" => 1
  ];

  private static function generateVerifString(): string
  {
    $verif_string = StringUtils::getRandomString(128);
    if ((bool) self::findOne(["verif_string" => $verif_string]))
      return self::generateVerifString();
    return $verif_string;
  }

  protected string $username;
  protected string $email;
  protected string $password;
  protected int $role = 1;
  protected bool $is_verified = false;
  protected ?string $verif_string = null;

  public function getUsername(): string
  {
    return $this->username;
  }

  public function setUsername(string $value): User
  {
    $this->username = $value;
    return $this;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $value): User
  {
    $this->email = $value;
    return $this;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  public function setPassword(string $value): User
  {
    $this->password = $value;
    return $this;
  }

  public function getRole(): int
  {
    return $this->role;
  }

  public function setRole(int $value): User
  {
    $this->role = $value;
    return $this;
  }

  public function isVerified(): bool
  {
    return $this->is_verified;
  }

  public function setVerified(bool $value): User
  {
    $this->is_verified = $value;
    return $this;
  }

  public function getVerifString(): string
  {
    return $this->verif_string;
  }

  public function setVerifString(string|null $value): User
  {
    $this->verif_string = $value;
    return $this;
  }

  public function getProducts(): array
  {
    return Product::findAll(["*"], ["seller_id" => $this->id]);
  }

  public function save(): void
  {
    $this->verif_string = self::generateVerifString();
    $insertion = Application::$instance->getDb()->insert(self::TABLE_NAME, [
      "username" => $this->username,
      "email" => $this->email,
      "password" => $this->password,
      "verif_string" => $this->verif_string
    ]);
    if (!$insertion)
      throw new \Exception("User couldn't be saved.");
  }

  public function verify()
  {
    Application::$instance->getDb()->update(
      self::TABLE_NAME,
      [
        "verif_string" => null,
        "is_verified" => 1
      ],
      ["id" => $this->id]
    );
  }

  public function notify(): bool
  {
    $email = new PHPMailer(true);
    $adminEmail = $_ENV["ADMIN_EMAIL_ADDRESS"];
    $link = Application::$instance->getFullUrl("activate-account", [
      "verif_string" => $this->verif_string
    ]);
    $twig = new \Twig\Environment(
      new \Twig\Loader\FilesystemLoader(Application::joinPaths("views"))
    );
    $htmlBody = $twig->render("email-templates/account-activation.twig", [
      "username" => $this->username,
      "link" => $link
    ]);

    try {
      $email->SMTPDebug = SMTP::DEBUG_SERVER;
      $email->isSMTP();
      $email->Host = "smtp.gmail.com";
      $email->SMTPAuth = true;
      $email->Username = $adminEmail;
      $email->Password = $_ENV["ADMIN_EMAIL_PASSWORD"];
      $email->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $email->Port = 465;
      $email->CharSet = "utf-8";
      $email->setFrom($adminEmail, "Admin PIPOT");
      $email->addAddress($this->email, $this->username);
      $email->isHTML(true);
      $email->Subject = "Activez votre compte";
      $email->Body = $htmlBody;
      return $email->send();
    } catch (PHPMailerException $e) {
      Application::logErrors($e->getMessage());
      return false;
    }
  }
}
