<?php

declare(strict_types=1);

it('shows the CAI landing page at the root instead of the Laravel welcome screen', function () {
    $response = $this->get('/');

    expect($response->getStatusCode())->toBe(200);

    $response->assertSee('Sistema CAI')
        ->assertSee('Centro de Atención Inclusiva')
        ->assertSee('Cada alumno, toda su historia')
        ->assertSee('Por qué existe')
        ->assertSee('Ingresar al panel')
        ->assertSee('/panel/login')
        ->assertDontSee('Welcome to Laravel');
});
