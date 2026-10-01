<?php

namespace Tests\Unit;

use App\Support\InstitutionalEmail;
use Tests\TestCase;

class InstitutionalEmailTest extends TestCase
{
    public function test_studentski_email_se_formira_od_imena_prezimena_i_indeksa(): void
    {
        $this->assertSame(
            'stojan.stojanovic.108-22@ftnkm.rs',
            InstitutionalEmail::forStudent('Stojan', 'Stojanović', '108/22'),
        );
    }
}
