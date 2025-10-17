<?php

use function Pest\Laravel\get;

it('returns 200 from /healthz', function () {
    get('/api/healthz')->assertOk();
});
