<?php

exit(0); // MUST BE REWORKED

use PHPUnit\Framework\TestCase;
require_once 'src/include/Utilities.php';

class UtilitiesTest extends TestCase 
{
    public function test_mb_strcmp()
    {
        $this->assertEquals(-1, mb_strcmp("abcdéf", "abcdéfGHIJ"));
        $this->assertEquals(-1, mb_strcmp("abcdéf", "abcdég"));
        $this->assertEquals(0,  mb_strcmp("abcdéf", "abcdéf"));
        $this->assertEquals(+1, mb_strcmp("abcdéf", "abcdée"));
        $this->assertEquals(+1, mb_strcmp("abcdéf", "abcdé"));
    }
}