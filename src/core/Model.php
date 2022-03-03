<?php

namespace TestApp\Core;

use DateTime;

class Model
{
  private int $id;
  private DateTime $createdAt;

  public function getId(): int
  {
    return $this->id;
  }

  public function setId(int $value)
  {
    $this->id = $value;
    return $this;
  }

  public function getCreatedAt(): DateTime
  {
    return $this->createdAt;
  }

  public function setCreatedAt(DateTime $value)
  {
    $this->createdAt = $value;
    return $this;
  }
}
