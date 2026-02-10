<?php

return [
    App\Providers\AppServiceProvider::class,
    Infrastructure\Tenant\Providers\TenancyServiceProvider::class,
    Infrastructure\Auth\Providers\AuthServiceProvider::class,
];
