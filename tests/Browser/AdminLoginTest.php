<?php

it('renders the admin login page for guests', function (): void {
    visit('/admin/login')
        ->assertPathIs('/admin/login')
        ->assertPresent('form')
        ->assertNoJavaScriptErrors();
});