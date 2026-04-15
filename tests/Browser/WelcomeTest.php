<?php

declare(strict_types=1);

it('has login page', function (): void {
    $page = visit('/login');

    $page->assertSee('Teacher & Admin Portal');
});
