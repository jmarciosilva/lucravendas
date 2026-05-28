<?php

declare(strict_types=1);

it('a aplicação responde com status 200 na rota raiz', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
});
