<?php

declare(strict_types=1);

namespace Infrastructure\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Domain\Shared\Ports\UuidGeneratorInterface;
use Domain\Shared\Ports\PasswordHasherInterface;
use Infrastructure\Shared\Adapters\LaravelUuidGenerator;
use Infrastructure\Shared\Adapters\LaravelPasswordHasher;

class SharedServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->app->bind(UuidGeneratorInterface::class, LaravelUuidGenerator::class);
    $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
  }
}