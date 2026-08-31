<?php

test('the telescope dashboard is not registered when disabled', function () {
    expect(config('telescope.enabled'))->toBeFalsy();

    $this->get('/telescope')->assertNotFound();
});

test('the telescope dashboard stays locked down for unauthorized users even when enabled', function () {
    putenv('TELESCOPE_ENABLED=true');
    $_ENV['TELESCOPE_ENABLED'] = 'true';
    $_SERVER['TELESCOPE_ENABLED'] = 'true';

    try {
        $this->refreshApplication();

        $this->get('/telescope')->assertForbidden();
    } finally {
        putenv('TELESCOPE_ENABLED=false');
        $_ENV['TELESCOPE_ENABLED'] = 'false';
        $_SERVER['TELESCOPE_ENABLED'] = 'false';
    }
});
