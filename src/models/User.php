<?php

namespace TestApp\Models;

use DateTime;
use TestApp\Core\Model;
use TestApp\Core\Application;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class User extends Model
{
  public static string $TABLE_NAME = "user";

  public static function findOne(array $search): User|null
  {
    $dbRow = Application::getDb()->getOne(self::$TABLE_NAME, $search);
    if (!$dbRow)
      return null;
    $user = new User();
    return $user
      ->setId((int) $dbRow["id"])
      ->setUsername($dbRow["username"])
      ->setEmail($dbRow["email"])
      ->setPassword($dbRow["password"])
      ->setRole($dbRow["role"])
      ->setVerified($dbRow["is_verified"])
      ->setVerifString($dbRow["verif_string"])
      ->setCreatedAt(new DateTime($dbRow["created_at"]));
  }

  private static function generateVerifString(): string
  {
    $length = 128;
    return substr(
      str_shuffle(str_repeat("0123456789abcdfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_", $length)),
      0,
      $length
    );
  }

  private string $username;
  private string $email;
  private string $password;
  private int $role = 1;
  private bool $is_verified = false;
  private ?string $verifString = null;

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
    return $this->verifString;
  }

  public function setVerifString(string|null $value): User
  {
    $this->verifString = $value;
    return $this;
  }

  public function save(): void
  {
    $this->verifString = self::generateVerifString();
    $insertion = Application::getDb()->insert(self::$TABLE_NAME, [
      "username" => $this->username,
      "email" => $this->email,
      "password" => $this->password,
      "verif_string" => $this->verifString
    ]);
    if (!$insertion)
      throw new \Exception("User couldn't be saved.");
  }

  public function verify()
  {
    Application::getDb()->update(self::$TABLE_NAME, [
      "verif_string" => null,
      "is_verified" => 1
    ], $this->id);
  }

  public function notify(): void
  {
    $email = new PHPMailer(true);
    $dotenv = \Dotenv\Dotenv::createImmutable(Application::$ROOT_DIR);
    $dotenv->load();
    $adminEmail = $_ENV["ADMIN_EMAIL_ADDRESS"];
    $htmlBody = preg_replace_callback("/{{[^{}]+}}/", function ($matches) {
      $prop = substr($matches[0], 2, strlen($matches[0]) - 2 * 2);
      return $this->{$prop};
    }, file_get_contents(Application::$ROOT_DIR . "/email-templates/account-activation.html"));

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
      $email->send();
    } catch (PHPMailerException $e) {
      Application::vardump($e);
      return;
    }
  }
}
