<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_calendar_are_public(): void
    {
        $this->get('/')->assertOk();
        $this->get('/eventos')->assertOk()->assertSee('Agenda');
    }

    public function test_legacy_page_urls_continue_to_work(): void
    {
        $pages = [
            'a_cicuta.html',
            'about.html',
            'andaluz.html',
            'artevivencias.html',
            'banda_descendentes.html',
            'banda_sn.html',
            'clube_de_escrita.html',
            'clube_do_livro.html',
            'flauteando.html',
            'nuav.html',
            'passocompasso.html',
            'rabisco.html',
            'thank_you.html',
        ];

        foreach ($pages as $path) {
            $this->get('/'.$path)->assertOk();
        }
    }

    public function test_admin_calendar_requires_authentication(): void
    {
        $this->get('/admin/eventos')->assertRedirect('/login');
    }
}
